<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Log file
$logFile = 'update_esno_log.txt';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

// Update EsNO for a given act
function updateEsno($pdo, $act, $esno_activacion) {
    try {
        $stmt = $pdo->prepare("
            UPDATE activations 
            SET esno_activacion = :esno_activacion
            WHERE act = :act
        ");
        
        $stmt->execute([
            ':act' => $act,
            ':esno_activacion' => $esno_activacion
        ]);

        $affectedRows = $stmt->rowCount();
        if ($affectedRows > 0) {
            logMessage("Successfully updated esno_activacion to $esno_activacion for act $act");
            return "Successfully updated esno_activacion to $esno_activacion for act $act";
        } else {
            logMessage("No record found for act $act");
            return "No record found for act $act";
        }
    } catch (PDOException $e) {
        logMessage("Error updating esno_activacion for act $act: " . $e->getMessage());
        return "Error updating esno_activacion for act $act: " . $e->getMessage();
    }
}

// Main execution
try {
    // Initialize database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if running from command line or web
    if (php_sapi_name() === 'cli') {
        // Command line input
        if ($argc !== 3) {
            echo "Usage: php update_esno.php <act> <esno_activacion>\n";
            exit(1);
        }

        $act = $argv[1];
        $esno_activacion = floatval($argv[2]);

        if (!is_numeric($act) || !is_numeric($esno_activacion)) {
            echo "Error: act must be an integer and esno_activacion must be a number\n";
            logMessage("Invalid input: act=$act, esno_activacion=$esno_activacion");
            exit(1);
        }

        $result = updateEsno($pdo, $act, $esno_activacion);
        echo "$result\n";
    } else {
        // Web form input
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = filter_input(INPUT_POST, 'act', FILTER_VALIDATE_INT);
            $esno_activacion = filter_input(INPUT_POST, 'esno_activacion', FILTER_VALIDATE_FLOAT);

            if ($act === false || $esno_activacion === false) {
                echo "Error: Invalid input. act must be an integer and esno_activacion must be a number.\n";
                logMessage("Invalid web input: act=" . $_POST['act'] . ", esno_activacion=" . $_POST['esno_activacion']);
            } else {
                $result = updateEsno($pdo, $act, $esno_activacion);
                echo "$result\n";
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Update EsNO Activacion</title>
        </head>
        <body>
            <h2>Update EsNO Activacion</h2>
            <form method="post">
                <label for="act">Act (Service ID):</label>
                <input type="number" id="act" name="act" required><br><br>
                <label for="esno_activacion">EsNO Activacion:</label>
                <input type="number" step="0.01" id="esno_activacion" name="esno_activacion" required><br><br>
                <input type="submit" value="Update">
            </form>
        </body>
        </html>
        <?php
    }
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

?>