<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Create log file
$logFile = 'timestamp_check_log.txt';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

try {
    // Initialize database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMessage("Database connection established");

    // Query to fetch the last 10 records from signal_data, ordered by timestamp descending
    $stmt = $pdo->query("SELECT act, timestamp, EsNO, CNO FROM signal_data ORDER BY timestamp DESC LIMIT 10");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    logMessage("Fetched " . count($records) . " records from signal_data");

    // Display results in a simple HTML table
    echo "<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><title>Timestamp Check</title>";
    echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
    echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } .content { max-width: 1200px; margin: 0 auto; } .table-container { margin-top: 20px; } .total-records { margin-bottom: 10px; font-weight: bold; }</style>";
    echo "</head><body><div class='content'><h1>Timestamp Format Check</h1>";
    echo "<div class='total-records'>Total Records: " . count($records) . "</div>";
    echo "<div class='table-container'><table class='table table-bordered table-hover'>";
    echo "<thead><tr><th>ACT</th><th>Timestamp</th><th>EsNO</th><th>CNO</th></tr></thead><tbody>";

    foreach ($records as $record) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($record['act']) . "</td>";
        echo "<td>" . htmlspecialchars($record['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($record['EsNO']) . "</td>";
        echo "<td>" . htmlspecialchars($record['CNO']) . "</td>";
        echo "</tr>";
        logMessage("Record: act=" . $record['act'] . ", timestamp=" . $record['timestamp'] . ", EsNO=" . $record['EsNO'] . ", CNO=" . $record['CNO']);
    }

    echo "</tbody></table></div></div></body></html>";

} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>