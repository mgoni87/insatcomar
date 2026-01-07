<?php
require_once 'config.php';

date_default_timezone_set(APP_TIMEZONE);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
}

// Obtener parámetros de la URL
$act = isset($_GET['act']) ? $_GET['act'] : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : ''; // Formato YYYY-MM-DD

if (!$act || !$fecha) {
    die(json_encode(['error' => 'Parámetros ACT o fecha no proporcionados.']));
}

// Consulta para obtener el consumo por aplicación para una fecha y ACT específicos
$sql = "
    SELECT
        cda.nombre_aplicacion,
        cda.consumo_gb,
        cda.color
    FROM
        consumos_detallados_aplicaciones cda
    JOIN
        consumos_diarios cd ON cda.consumo_diario_id = cd.id
    WHERE
        cd.act = ? AND cd.fecha_consumo = ?
    ORDER BY
        cda.consumo_gb DESC;
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]));
}

$stmt->bind_param("ss", $act, $fecha);
$stmt->execute();
$result = $stmt->get_result();

$appData = [];
while ($row = $result->fetch_assoc()) {
    $appData[] = [
        'name' => $row['nombre_aplicacion'],
        'y' => (float)$row['consumo_gb'], // Asegurarse de que sea un float para Chart.js
        'color' => $row['color']
    ];
}

$stmt->close();
$conn->close();

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode(['apps' => $appData, 'date' => $fecha]);
?>