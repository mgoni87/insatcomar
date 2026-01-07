<?php
// Permitir ejecución por CLI (cron) pasando parámetros como "url_index=1&pagina=1&fecha=2025-06-18"
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    parse_str($argv[1], $_GET);
}


//*******************************************************//
//_____________________CONFIGURACION_____________________//
//*******************************************************//

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$urlIndexStart = isset($_GET['url_index']) ? (int)$_GET['url_index'] : 0;
$pageStart = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Configuración
require_once 'config.php'; // Asegúrate de que esta línea esté al principio
$resultsPerPage = 30;

$logFile = __DIR__ . '/scraper.log';

date_default_timezone_set(APP_TIMEZONE);

//*******************************************************//
//_______________________FUNCIONES_______________________//
//*******************************************************//

function logToFile($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    //echo $message . "<br>";
}

// --- Función para enviar correo electrónico ---
function sendReportEmail($recipientEmail, $reportDate, $totalResultsFoundByUrl, $totalRecordsExtracted) {
    $subject = "Reporte Diario de Scraper Orbith - Fecha: " . $reportDate;
    $message = "Se ha completado la extracción de datos para la fecha: **" . $reportDate . "**.\n\n";

    $message .= "Resumen de Resultados Encontrados por URL Base:\n";
    if (!empty($totalResultsFoundByUrl)) {
        foreach ($totalResultsFoundByUrl as $url => $total) {
            $message .= "- URL: " . $url . ", Total de Registros en Página: " . $total . "\n";
        }
    } else {
        $message .= "- No se encontraron resultados en las URLs base.\n";
    }

    $message .= "\nTotal de Registros Extraídos y Guardados en DB para esta fecha: **" . $totalRecordsExtracted . "**.\n\n";
    $message .= "Este correo se generó automáticamente.\n";

    $headers = "From: noreply@insat.com.ar\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Puedes cambiar a text/html si quieres HTML en el correo

    if (mail($recipientEmail, $subject, $message, $headers)) {
        logToFile("Correo de reporte enviado a $recipientEmail para la fecha $reportDate.");
    } else {
        logToFile("Error al enviar el correo de reporte a $recipientEmail para la fecha $reportDate.");
    }
}

//*******************************************************//
//_________________________CX_BD_________________________//
//*******************************************************//

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear cookie jar temporal
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');

// Login
$ch = curl_init(SCRAPER_LOGIN_URL);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => LOGIN_USR, 'password' => LOGIN_PASS]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

//*******************************************************//
//____________________FECHAS_A_EXTRAER___________________//
//*******************************************************//

// Crear un objeto DateTime para el día anterior al actual
$yesterday = (new DateTime())->modify('-1 day');

// Determinar startDate
if (isset($_GET['fecha'])) {
    // Si la fecha se pasa por GET, usar esa fecha para startDate y endDate
    $startDate = new DateTime($_GET['fecha']);
    $endDate = new DateTime($_GET['fecha']);
} else {
    // Consultar la última fecha extraída de la base de datos
    $lastExtractedDate = null;
    $sql = "SELECT MAX(fecha_consumo) AS last_date FROM consumos_diarios";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['last_date']) {
            $lastExtractedDate = new DateTime($row['last_date']);
        }
    }

    if ($lastExtractedDate) {
        // Si hay una última fecha, empezar desde el día siguiente
        $startDate = $lastExtractedDate->modify('+1 day');
    } else {
        // Si no hay datos en la DB, empezar desde una fecha por defecto (ej. hace 7 días o una fecha fija)
        // Para este caso, usaremos 'yesterday' como punto de partida si no hay datos previos,
        // o si prefieres, puedes establecer una fecha muy antigua para una primera corrida completa.
        // Por ahora, lo dejaré como ayer si no hay datos.
        $startDate = clone $yesterday; // Si no hay datos previos, empieza por ayer.
    }

    // La fecha final siempre será el día anterior al actual
    $endDate = clone $yesterday;
}

// Asegurarse de que startDate no sea posterior a endDate
if ($startDate > $endDate) {
    logToFile("ℹ️ La fecha de inicio ($startDate->format('Y-m-d')) es posterior a la fecha de fin ($endDate->format('Y-m-d')). No hay datos nuevos para procesar.");
    // No hay necesidad de continuar si startDate es después de endDate
    unlink($cookieFile);
    $conn->close();
    logToFile("<h3>✅ Scraper finalizado (sin nuevas fechas)</h3>");
    exit;
}


// Para el DatePeriod, necesitamos una fecha de fin que sea UN DÍA MÁS
// para que incluya $endDate. Creamos un CLON temporal para esta operación.
$datePeriodEndDate = clone $endDate;
$datePeriodEndDate->modify('+1 day');

// Recorremos cada día del rango
$period = new DatePeriod($startDate, new DateInterval('P1D'), $datePeriodEndDate);

//*******************************************************//
//________________________SCRAPING_______________________//
//*******************************************************//

foreach ($period as $date) {
    $fechaDesde = $date->format('d/m/Y');
    $fechaSql = $date->format('Y-m-d');

    logToFile("<h3>📅 Procesando fecha: $fechaDesde</h3>");

    $seen = [];
	$totalResultsFoundByUrl = []; // Almacenar totalResults por URL base para el email
	$totalRecordsExtractedForDate = 0; // Contador para los registros guardados en DB para esta fecha
	
    $pageCounter = 0;
    $reinicioProgramado = false;
    $totalPagesFirstURL = 0;
    $limite = 2; // Mantengo el límite para demostración, ajusta según necesidad

    foreach (SCRAPER_URLS as $urlIndex => $baseUrl) {
        if ($urlIndex < $urlIndexStart) continue; // Saltar URLs anteriores

        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        if (preg_match('/Total: (\d+)/', $pageContent, $matches)) {
            $totalResults = intval($matches[1]);
            $totalPages = ceil($totalResults / $resultsPerPage);
            $totalResultsFoundByUrl[$baseUrl] = $totalResults; // Guardar para el email
            logToFile("Total de resultados para $baseUrl: $totalResults - Total de páginas $totalPages");
        } else {
            logToFile("Error obteniendo total de resultados para $baseUrl");
            continue;
        }

        $startPage = ($urlIndex === $urlIndexStart) ? $pageStart : 1;

        for ($i = $startPage; $i <= $totalPages; $i++) {
            $pageCounter++;

            if ($pageCounter >= $limite) {
                // Preparar redirección pasando url_index y siguiente página
                $nextUrlIndex = $urlIndex;
                $nextPage = $i;
                if ($nextPage > $totalPages) {
                    $nextUrlIndex++; // pasar a la siguiente URL si se acabaron las páginas
                    $nextPage = 1;
                }

                // Necesitamos pasar la fecha original que se inició el scraping
                // o determinar cómo continuar el rango de fechas después del reinicio.
                // Para simplificar, si se reinicia, simplemente se pasa la fecha que se está procesando.
                $fechaParam = $fechaSql;
                
$redirectUrl = "scraper_reinicio_auto.php?url_index=$nextUrlIndex&pagina=$nextPage&fecha=$fechaParam";
logToFile("🔁 Reinicio automático → $redirectUrl");

if (php_sapi_name() === 'cli') {
    // Si se ejecuta por cronjob, incluir directamente
    parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $_GET);
    include 'scraper_reinicio_auto.php';
} else {
    // Si es desde navegador, redirigir
    header("Location: $redirectUrl");
    exit;
}

                exit;
            }

             $url = $baseUrl . "&page=$i";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $attempts = 0;
            do {
                $pageContent = curl_exec($ch);
                $attempts++;
                if (!$pageContent) {
                    logToFile("⚠️ Fallo cargando página $i (intento $attempts). Reintentando...");
                    //sleep(1);
                }
            } while (!$pageContent && $attempts < 3);

            if (curl_errno($ch)) {
                logToFile("❌ cURL error en página $i de $baseUrl: " . curl_error($ch));
                curl_close($ch);
                continue;
            }

            if (!$pageContent || strlen($pageContent) < 500) {
                logToFile("⚠️ Página $i vacía o incompleta");
                curl_close($ch);
                continue;
            }

            curl_close($ch);

            if (preg_match_all('/<tr[^>]*>.*?<td[^>]*>(\d+)<\/td>.*?<\/tr>/s', $pageContent, $rows)) {
                $ids = array_unique($rows[1]);
                logToFile("🧲 Se encontraron " . count($ids) . " IDs en la página $i");

                foreach ($ids as $id) {
                    if (isset($seen[$id])) continue;
                    $seen[$id] = true;

                    $detailUrl = "https://portal.orbith.com/admin/admin/index.php?operation=consumidorDetail&id=$id";
                    logToFile("URL detalle $detailUrl");
                    $ch = curl_init($detailUrl);
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $detailContent = curl_exec($ch);
                    curl_close($ch);

                    $docNumber = null;
                    if (preg_match('/<td>\s*<strong>N&deg; Doc.<\/strong>\s*<\/td>\s*<td>([^<]*)<\/td>/', $detailContent, $docMatch)) {
                        $docNumber = trim($docMatch[1]);
                    }

                    $currentPlan = null;
                    preg_match_all('/<tr>(.*?)<\/tr>/s', $detailContent, $trRows);

                    foreach ($trRows[1] as $row) {
                        if (preg_match('/<td>\s*<span class="glyphicon glyphicon-[^>]*>\s*<\/td>\s*<td>([^<]*)<\/td>/', $row, $planMatch)) {
                            $currentPlan = trim($planMatch[1]);
                        }

                        if (preg_match('/<td class="td-equipo">.*?<p class="act" data-value="([^"]*)">ACT:/s', $row, $actMatch)) {
                            $act = trim($actMatch[1]);

                            if ($currentPlan !== null && $act !== '') {
                                $consumoUrl = "https://portal.orbith.com/admin/admin/index.php?operation=consumidorConsumoPorCategoria&activacionId=$act&fechaDesde=$fechaDesde&fechaHasta=$fechaDesde";

                                $ch = curl_init($consumoUrl);
                                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $consumoContent = curl_exec($ch);
                                curl_close($ch);

                                $consumoData = json_decode($consumoContent, true);
                                $totalConsumo = $consumoData['total_gb'] ?? 0;

                                logToFile("Guardando: ID $id, DOC $docNumber, ACT $act, PLAN $currentPlan, Fecha $fechaSql, GB $totalConsumo");

                                $stmt = $conn->prepare("
                                    INSERT INTO consumos_diarios (cliente_id, documento, plan, act, fecha_consumo, total_gb)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE
                                        total_gb = VALUES(total_gb),
                                        documento = VALUES(documento),
                                        plan = VALUES(plan)
                                ");
                                $stmt->bind_param("issssd", $id, $docNumber, $currentPlan, $act, $fechaSql, $totalConsumo);

                                if (!$stmt->execute()) {
                                    logToFile("❌ Error al guardar consumo para ACT $act, fecha $fechaSql: " . $stmt->error . "");
                                } else {
                                    // Incrementar el contador solo si la inserción/actualización fue exitosa
                                    $totalRecordsExtractedForDate++;
                                    $consumoDiarioId = $stmt->insert_id;
                                    logToFile("✅ Guardado exitoso para ACT $act, fecha $fechaSql (ID: $consumoDiarioId)");

                                    if (isset($consumoData['rows']) && is_array($consumoData['rows'])) {
                                        $stmtDetail = $conn->prepare("
                                            INSERT INTO consumos_detallados_aplicaciones (consumo_diario_id, nombre_aplicacion, consumo_gb, color)
                                            VALUES (?, ?, ?, ?)
                                            ON DUPLICATE KEY UPDATE
                                                consumo_gb = VALUES(consumo_gb),
                                                color = VALUES(color)
                                        ");
                                        foreach ($consumoData['rows'] as $app) {
                                            $appName = $app['name'] ?? 'Unknown';
                                            $appConsumption = $app['y'] ?? 0;
                                            $appColor = $app['color'] ?? '#000000';

                                            $stmtDetail->bind_param("isds", $consumoDiarioId, $appName, $appConsumption, $appColor);
                                            if (!$stmtDetail->execute()) {
                                                logToFile("❌ Error al guardar detalle para $appName (ACT $act, fecha $fechaSql): " . $stmtDetail->error . "");
                                            } else {
                                                logToFile("   ✅ Detalle de $appName guardado exitosamente.");
                                            }
                                        }
                                        $stmtDetail->close();
                                    }
                                }
                                $stmt->close();
                            }
                        }
                    }
                }
            } else {
                logToFile("⚠️ No se encontraron IDs en la página $i");
            }

            //sleep(1);
        }
    }

    // --- ENVIAR CORREO DE REPORTE AL FINAL DE CADA FECHA PROCESADA ---
    $recipient = 'dispatch-insat@expandigital.net'; // <--- CAMBIA ESTO POR EL CORREO DEL DESTINATARIO
    sendReportEmail($recipient, $fechaSql, $totalResultsFoundByUrl, $totalRecordsExtractedForDate);
    // -----------------------------------------------------------------

}

// Limpiar cookie temporal
unlink($cookieFile);

// Cerrar conexión DB
$conn->close();

logToFile("<h3>✅ Scraper finalizado</h3>");
?>