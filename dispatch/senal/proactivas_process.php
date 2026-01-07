<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Create log file
$logFile = 'proactivas_log.txt';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMessage("Process request received");

    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    if (empty($startDate) || empty($endDate)) {
        throw new Exception("Invalid date range");
    }

    $startDateStr = date('Y-m-d H:i:s', strtotime($startDate));
    $endDateStr = date('Y-m-d H:i:s', strtotime($endDate));
    logMessage("Processing data from $startDateStr to $endDateStr");

    // Fetch all activations with modem_id and hub
    $stmt = $pdo->query("SELECT act, nro_doc, latitud, longitud, esno_activacion, modem_id, hub FROM activations WHERE activo = 1");
    $activations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    logMessage("Fetched " . count($activations) . " active activations");

    $results = [];
    foreach ($activations as $activation) {
        $act = $activation['act'];
        $esnoActivacion = floatval($activation['esno_activacion']);

        $signalStmt = $pdo->prepare("SELECT timestamp, EsNO, CNO FROM signal_data WHERE act = :act AND timestamp BETWEEN :start_date AND :end_date AND EsNO > 0 AND CNO > 0");
        $signalStmt->execute([':act' => $act, ':start_date' => $startDateStr, ':end_date' => $endDateStr]);
        $signalData = $signalStmt->fetchAll(PDO::FETCH_ASSOC);
        logMessage("Fetched " . count($signalData) . " signal points for act $act");

        if (empty($signalData)) {
            logMessage("No signal data for act $act, skipping");
            continue;
        }

        $esnoValues = array_column($signalData, 'EsNO');
        $esnoPromedio = array_sum($esnoValues) / count($esnoValues);
        $mean = $esnoPromedio;
        $variance = 0;
        foreach ($esnoValues as $esno) {
            $variance += pow($esno - $mean, 2);
        }
        $desviacionEstandar = sqrt($variance / count($esnoValues));
        $lowSignalCount = 0;
        foreach ($esnoValues as $esno) {
            if (($esnoActivacion - $esno) > 1.5) {
                $lowSignalCount++;
            }
        }
        $bajaSenal = ($lowSignalCount / count($esnoValues)) * 100;
        $resultado = $desviacionEstandar * $bajaSenal;

        $results[] = [
            'act' => $act,
            'nro_doc' => $activation['nro_doc'],
            'latitud' => $activation['latitud'],
            'longitud' => $activation['longitud'],
            'esno_activacion' => $esnoActivacion,
            'esno_promedio' => $esnoPromedio,
            'desviacion_estandar' => $desviacionEstandar,
            'baja_senal' => $bajaSenal,
            'resultado' => $resultado,
            'modem_id' => $activation['modem_id'],
            'hub' => $activation['hub']
        ];
        logMessage("Calculated metrics for act $act: EsNO Promedio=$esnoPromedio, Desviacion=$desviacionEstandar, Baja Señal=$bajaSenal%, Resultado=$resultado");
    }

    // Sort by resultado in descending order and get top 250
    usort($results, function($a, $b) {
        return $b['resultado'] <=> $a['resultado'];
    });
    $topResults = array_slice($results, 0, 250);
    logMessage("Sorted and selected top 250 records");

    // Prepare table HTML
    $tableHtml = '';
    foreach ($topResults as $row) {
        $tableHtml .= "<tr data-act='" . htmlspecialchars($row['act']) . "' data-modem-id='" . htmlspecialchars($row['modem_id']) . "' data-hub='" . htmlspecialchars($row['hub']) . "' data-latitud='" . htmlspecialchars($row['latitud'] ?? '') . "' data-longitud='" . htmlspecialchars($row['longitud'] ?? '') . "'>";
        $tableHtml .= "<td>" . htmlspecialchars($row['act']) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars($row['nro_doc']) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars($row['latitud'] ?? 'N/A') . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars($row['longitud'] ?? 'N/A') . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars(number_format($row['esno_activacion'], 2)) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars(number_format($row['esno_promedio'], 2)) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars(number_format($row['desviacion_estandar'], 2)) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars(number_format($row['baja_senal'], 2)) . "</td>";
        $tableHtml .= "<td>" . htmlspecialchars(number_format($row['resultado'], 2)) . "</td>";
        $tableHtml .= "<td><button class='btn btn-primary chart-btn' data-act='" . htmlspecialchars($row['act']) . "' data-modem-id='" . htmlspecialchars($row['modem_id']) . "' data-hub='" . htmlspecialchars($row['hub']) . "' data-latitud='" . htmlspecialchars($row['latitud'] ?? '') . "' data-longitud='" . htmlspecialchars($row['longitud'] ?? '') . "'>Ver Gráfico</button></td>";
        $tableHtml .= "</tr>";
    }

    header('Content-Type: application/json');
    echo json_encode(['table' => $tableHtml, 'count' => count($topResults)]);

} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    logMessage("General error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>