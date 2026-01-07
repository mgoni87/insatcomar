<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Log file
$logFile = 'view_signal_data_log.txt';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMessage("Database connection established");

    // Execute query to fetch the last 300 records ordered by timestamp DESC
    $stmt = $pdo->query("SELECT * FROM signal_data ORDER BY timestamp DESC LIMIT 300");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    logMessage("Query executed, rows: " . count($data));

    // Display results
    if (count($data) > 0) {
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><title>Signal Data</title>";
        echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
        echo "</head><body><div class='container'><h2>Signal Data Contents (Last 300 Records)</h2>";
        echo "<table class='table table-bordered'><thead><tr><th>ACT</th><th>Timestamp</th><th>EsNO</th><th>CNO</th></tr></thead><tbody>";
        foreach ($data as $row) {
            echo "<tr><td>" . htmlspecialchars($row['act']) . "</td><td>" . htmlspecialchars($row['timestamp']) . "</td><td>" . htmlspecialchars($row['EsNO']) . "</td><td>" . htmlspecialchars($row['CNO']) . "</td></tr>";
        }
        echo "</tbody></table></div></body></html>";
    } else {
        echo "No data found in signal_data table";
    }

    // Output as JSON for API compatibility
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>