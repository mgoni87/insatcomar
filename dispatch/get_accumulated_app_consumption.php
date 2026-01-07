<?php
require_once 'config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

$act = isset($_GET['act']) ? $conn->real_escape_string($_GET['act']) : '';
$startDate = isset($_GET['startDate']) ? $conn->real_escape_string($_GET['startDate']) : '';
$endDate = isset($_GET['endDate']) ? $conn->real_escape_string($_GET['endDate']) : '';

if (!$act || !$startDate || !$endDate) {
    echo json_encode(['error' => 'Parámetros ACT, startDate o endDate no proporcionados.']);
    exit;
}

// Query to get aggregated consumption by application for the given period
// Adjusted to use consumos_detallados_aplicaciones and join with consumos_diarios
$sql = "
    SELECT
        cda.nombre_aplicacion AS app_name,
        SUM(cda.consumo_gb) AS total_gb
    FROM
        consumos_detallados_aplicaciones cda
    JOIN
        consumos_diarios cd ON cda.consumo_diario_id = cd.id
    WHERE
        cd.act = '{$act}' AND cd.fecha_consumo BETWEEN '{$startDate}' AND '{$endDate}'
    GROUP BY
        cda.nombre_aplicacion
    ORDER BY
        total_gb DESC
";

$result = $conn->query($sql);

$apps_data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $apps_data[] = [
            'name' => $row['app_name'],
            'y' => (float)$row['total_gb'] // 'y' is common for chart data values
        ];
    }
} else {
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $conn->error]);
    exit;
}

$conn->close();

echo json_encode(['apps' => $apps_data]);
?>