<?php

// Configurar archivo de log (usar /tmp para writability)
$log_file = '/tmp/monit_colas_mini.log';
function log_message($message) {
    global $log_file;
    @file_put_contents($log_file, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

log_message("Iniciando script para obtener datos en tiempo real y enviar por correo para MonitColasMini...");

// 1. Configuración de la solicitud
$url = 'https://200.45.95.4/pbx/Phps/HTTPCon/MonitColasMini/calltr-mini.php';
$payload = 'uCT=hcrespo';
$cookies_string = 'accoRaiz=8; AsterVoIP-q6fnxc22i1TjxJN=34; langes=6; menuClose--hcrespo--crmn=24; menuClose--hcrespo--usuan=25; themedefault=12';

log_message("URL: $url");
log_message("Payload: $payload");
log_message("Cookies: $cookies_string");

// 2. Inicializar cURL
$ch = curl_init();
log_message("cURL inicializado.");

// 3. Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Cookie: ' . $cookies_string
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
log_message("Opciones de cURL configuradas. SSL/TLS deshabilitado.");

// 4. Ejecutar la solicitud
$response = curl_exec($ch);

// 5. Manejo de errores
if (curl_errno($ch)) {
    log_message("Error en cURL: " . curl_error($ch) . ", Código: " . curl_errno($ch));
    curl_close($ch);
    exit(1);
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
log_message("Solicitud cURL ejecutada. Código HTTP: $http_code");

curl_close($ch);
log_message("cURL cerrado.");

if ($http_code !== 200) {
    log_message("Error: Código HTTP $http_code. Respuesta: " . substr($response, 0, 200));
    exit(1);
}

if (empty($response) || strlen($response) < 50) {
    log_message("ADVERTENCIA: Respuesta HTTP 200, pero el cuerpo es muy corto o vacío.");
    exit(1);
}

// 6. Parsear el XML
libxml_clear_errors();
$xml = simplexml_load_string($response);

if ($xml === false) {
    $errors = '';
    foreach (libxml_get_errors() as $error) {
        $errors .= $error->message . "\n";
    }
    log_message("Error: No se pudo parsear el XML. Errores: $errors");
    exit(1);
}

// 7. Inicializar variables para los cálculos
$total_finalizadas = 0;
$total_no_atendidas = 0;
$total_totales = 0;
$queue_data = [];
$last_non_attended = '';
$max_wait = '00:00:00';
$max_wait_queue = '';

// 8. Procesar los datos de colas y encontrar la mayor espera
function timeToSeconds($time) {
    $parts = explode(':', $time);
    return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
}

foreach ($xml->Respuesta as $respuesta_block) {
    foreach ($respuesta_block->Dato as $dato) {
        $parsed_data = (string)$dato;
        if (strpos($parsed_data, ';') !== false) {
            $queues = explode(';', $parsed_data);
            foreach ($queues as $queue) {
                if (!empty($queue)) {
                    $parts = explode('|', $queue);
                    if (count($parts) === 9) {
                        list($cola, $en_espera, $espera_actual, $en_curso, $finalizadas, $no_atendidas, $espera_maxima, $espera_promedio, $_) = $parts;
                        $total = (int)$en_curso + (int)$finalizadas + (int)$no_atendidas;
                        $nivel_porcentaje = ($total > 0) ? number_format(($finalizadas / $total) * 100, 2) : '0.00';
                        $queue_data[] = [
                            'cola' => $cola,
                            'en_espera' => $en_espera,
                            'espera_actual' => $espera_actual,
                            'en_curso' => $en_curso,
                            'finalizadas' => $finalizadas,
                            'no_atendidas' => $no_atendidas,
                            'espera_maxima' => $espera_maxima,
                            'espera_promedio' => $espera_promedio,
                            'total' => $total,
                            'nivel_porcentaje' => $nivel_porcentaje
                        ];
                        $total_finalizadas += (int)$finalizadas;
                        $total_no_atendidas += (int)$no_atendidas;
                        $total_totales += $total;
                        if (timeToSeconds($espera_maxima) > timeToSeconds($max_wait)) {
                            $max_wait = $espera_maxima;
                            $max_wait_queue = $cola;
                        }
                    }
                }
            }
        } elseif (strpos($parsed_data, '|10:') !== false) {
            $last_non_attended = explode('|', $parsed_data)[1];
        }
    }
}

// Formatear Mayor Espera
$max_wait_formatted = $max_wait_queue ? "$max_wait ($max_wait_queue)" : '00:00:00 (Ninguna)';

// 9. Calcular el nivel total
$nivel_total = ($total_totales > 0) ? number_format(($total_finalizadas / $total_totales) * 100, 2) : '0.00';

// 10. Generar el HTML para el correo
$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Edwar Pons">
    <meta name="copyright" content="AsterVoIP (1998 - 2025)">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache,mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>PBX - Admin - Reporte MonitColasMini</title>
</head>
<body style="font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-size: 0.8em; background-color: #efefef; margin: 0; padding: 0; text-rendering: optimizeLegibility; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <section id="idCuerpo" style="position: relative; top: 6px; left: 0; width: 100%; min-height: 100%; padding: 0;">
        <div id="MonColF" style="font-family: Tahoma, Arial, sans-serif; font-size: 10px; min-height: 100vh; background-color: #fff;">
            <div class="wrapper" style="max-width: 1050px; margin: 0 auto;">
                <div id="oneDiv" style="float: left; flex: 1; margin-right: 5px;">
                    <div id="co">
                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid #7f8c8d; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <td colspan="11" style="text-align: left; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Llamados en Cola</td>
                                </tr>
                                <tr>
                                    <th rowspan="2" style="text-align: left; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d; width: 20%;">Cola</th>
                                    <th colspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Atendidas</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">No Atendidas</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Total</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Nivel %</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Espera Máxima</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Espera Promedio</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">EN ESPERA</th>
                                    <th rowspan="2" style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d;">Espera Actual</th>
                                </tr>
                                <tr>
                                    <th style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d; width: 10%;">En Curso</th>
                                    <th style="text-align: center; background-color: #fff; font-size: 10px; padding: 10px 0 10px 5px; border-bottom: 1px solid #7f8c8d; width: 10%;">Finalizadas</th>
                                </tr>
                            </thead>
                            <tbody id="co_tb">
HTML;

// 11. Agregar datos de colas al HTML
foreach ($queue_data as $queue) {
    $html .= <<<HTML
                                <tr>
                                    <td style="text-align: left; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['cola']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['en_curso']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['finalizadas']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['no_atendidas']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['total']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['nivel_porcentaje']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['espera_maxima']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['espera_promedio']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['en_espera']}</td>
                                    <td style="text-align: center; padding: 2px 0 2px 5px; font-size: 15px; border-bottom: 1px solid #7f8c8d;">{$queue['espera_actual']}</td>
                                </tr>
HTML;
}

$html .= <<<HTML
                            </tbody>
                        </table>
                    </div>
                    <div id="ane" style="margin-top: 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid #7f8c8d; border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td style="background-color: #fff; padding: 10px 0 10px 5px; text-align: center; width: 25%;"><h3 style="margin: 0; font-size: 13px; font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-weight: bold;">Atendidos</h3></td>
                                    <td style="background-color: #fff; padding: 10px 0 10px 5px; text-align: center; width: 25%;"><h3 style="margin: 0; font-size: 13px; font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-weight: bold;">Nivel</h3></td>
                                    <td style="background-color: #fff; padding: 10px 0 10px 5px; text-align: center; width: 50%;"><h3 style="margin: 0; font-size: 13px; font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-weight: bold;">Mayor Espera</h3></td>
                                </tr>
                                <tr>
                                    <td id="ll_at" style="vertical-align: middle; padding: 10px 0 10px 5px; font-size: 25px; text-align: center; font-weight: bold; color: rgba(71, 191, 63, 1); border-bottom: 1px solid #7f8c8d;">{$total_finalizadas}</td>
                                    <td id="prom_at" style="vertical-align: middle; padding: 10px 0 10px 5px; font-size: 25px; text-align: center; font-weight: bold; color: #000; border-bottom: 1px solid #7f8c8d;">{$nivel_total}</td>
                                    <td id="mte" style="vertical-align: middle; padding: 10px 0 10px 5px; font-size: 25px; text-align: center; font-weight: bold; color: #000; border-bottom: 1px solid #7f8c8d;">{$max_wait_formatted}</td>
                                </tr>
                                <tr>
                                    <td style="background-color: #fff; padding: 10px 0 10px 5px; text-align: center;"><h3 style="margin: 0; font-size: 12px; font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-weight: bold;">No Atendidos</h3></td>
                                    <td style="background-color: #fff; padding: 10px 0 10px 5px;" colspan="2"><h3 style="margin: 0; font-size: 12px; font-family: 'RobotoRegular', Tahoma, Arial, sans-serif; font-weight: bold;">Últimas 3 No Atendidos</h3></td>
                                </tr>
                                <tr>
                                    <td id="ll_nat" style="vertical-align: middle; padding: 15px 0 15px 5px; font-size: 25px; text-align: center; font-weight: bold; color: rgba(191, 63, 71, 1); border-bottom: 1px solid #7f8c8d;">{$total_no_atendidas}</td>
                                    <td id="dt_nat" style="vertical-align: middle; padding: 15px 5px 15px 0; font-size: 14px; text-align: right; font-weight: bold; color: rgba(191, 63, 71, 1); border-bottom: 1px solid #7f8c8d;" colspan="2">{$last_non_attended}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
HTML;

// Set timezone to GMT-3
date_default_timezone_set('America/Argentina/Buenos_Aires');

// 12. Enviar el HTML por correo electrónico
$to = 'phontel.ar@gmail.com, lalvarez@expandigital.net';
$subject = 'Métricas - ' . date('Y-m-d H:i:s');
$from = 'no-reply@insat.com.ar';
$boundary = md5(time());

// Encabezados del correo
$headers = "From: $from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
$headers .= "Cc: tferreyra@expandigital.net,dispatch-insat@expandigital.net\r\n";

// Cuerpo del correo (texto plano como respaldo)
$plain_text = "Reporte de MonitColasMini generado el " . date('Y-m-d H:i:s') . "\n\n";
$plain_text .= "Por favor, visualiza este correo en un cliente que soporte HTML para ver el reporte completo.\n";
$plain_text .= "Atendidos: $total_finalizadas\n";
$plain_text .= "No Atendidos: $total_no_atendidas\n";
$plain_text .= "Nivel: $nivel_total%\n";
$plain_text .= "Mayor Espera: $max_wait_formatted\n";
$plain_text .= "Últimas 3 No Atendidas: $last_non_attended\n";

// Construir el cuerpo del correo
$message = "--$boundary\r\n";
$message .= "Content-Type: text/plain; charset=UTF-8\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $plain_text . "\r\n";
$message .= "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=UTF-8\r\n";
$message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $html . "\r\n";
$message .= "--$boundary--\r\n";

// Enviar el correo
$mail_sent = @mail($to, $subject, $message, $headers);

if ($mail_sent) {
    log_message("Correo enviado exitosamente a: $to");
} else {
    log_message("Error: No se pudo enviar el correo a: $to. Verifica la configuración del servidor de correo (SMTP).");
    exit(1);
}

log_message("Script finalizado.");
exit(0);

?>