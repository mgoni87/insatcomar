<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $act = isset($_GET['act']) ? (int)$_GET['act'] : 0;
    $modemId = isset($_GET['modem_id']) ? $_GET['modem_id'] : null;
    $hub = isset($_GET['hub']) ? $_GET['hub'] : null;
    $startDate = isset($_GET['start_date']) ? date('Y-m-d H:i:s', strtotime($_GET['start_date'])) : null;
    $endDate = isset($_GET['end_date']) ? date('Y-m-d H:i:s', strtotime($_GET['end_date'])) : null;

    $query = "SELECT timestamp, EsNO, CNO FROM signal_data WHERE act = :act";
    $params = [':act' => $act];
    if ($startDate && $endDate) {
        $query .= " AND timestamp BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>