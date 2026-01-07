<?php
require_once 'config.php'; // Asegúrate de que esta línea esté al principio

date_default_timezone_set(APP_TIMEZONE);

// Conexión a base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Recolectar filtros enviados por POST (igual que en consulta.php)
$idFilter = isset($_POST['id']) ? trim($_POST['id']) : '';
$dniFilter = isset($_POST['dni']) ? trim($_POST['dni']) : '';
$planFilter = isset($_POST['plan']) ? trim($_POST['plan']) : '';
$clienteIdFilter = isset($_POST['cliente_id']) ? trim($_POST['cliente_id']) : '';

// Planes map (puedes copiarlo del consulta.php o hacer include)
$planesMap = [
    'Entry' => ['Entry'],
    'Standard 10' => ['FAMILY 10'],
    'Premium 40' => ['Prestador - Premium 40 - 30x6','FAMILY 40'],
    'Infinity 100' => ['Prestador - Infinity 100 - 30x6','FAMILY 100'],
    'Pro 50' => ['Prestador - Pro 50 - 30x6','Pro 50 | 20x3','PROFESSIONAL 50'],
    'Pro 80' => ['Prestador - Pro 80 - 30x6','Pro 80 | 20x3','PROFESSIONAL 80'],
    'Pro 120' => ['Prestador - Pro 120 - 30x6','Pro 120 | 20x3','PROFESSIONAL 120'],
    'Pyme 50' => ['Prestador - Pyme 50 - 30x6','Pyme 50 | 20x3','Pyme 50GB'],
    'Pyme 80' => ['Prestador - Pyme 80 - 30x6','Pyme 80 | 20x3','Pyme 80 GB'],
    'Pyme 120' => ['Prestador - Pyme 120 - 30x6','Pyme 120 | 20x3','Pyme 120GB'],
    'Business 150' => ['Business 150+'],
    'Business 220' => ['Business 220+'],
    'Business 280' => ['Business 280+'],
    'Standard' => ['Prestador - Standard - 30x6','Standard 20%x3 meses','Standard'],
    'Priorizado 80' => ['Prestador - Priorizado 80 - 30x6','Priorizado 80 20%x3 meses','Priorizado 80 Gb'],
    'Priorizado 200' => ['Prestador - Priorizado 200 - 30x6','Priorizado 200 20%x3 meses','Priorizado 200 GB'],
];

// Fechas del ciclo actual
$hoy = new DateTime();
$fecha_inicio_ciclo_actual_dt = new DateTime();
if ($hoy->format('d') <= 24) {
    $fecha_inicio_ciclo_actual_dt->modify('first day of this month')->modify('-1 month');
} else {
    $fecha_inicio_ciclo_actual_dt->modify('first day of this month');
}
$fecha_inicio_ciclo_actual_dt->setDate($fecha_inicio_ciclo_actual_dt->format('Y'), $fecha_inicio_ciclo_actual_dt->format('m'), 25);

$fecha_fin_ciclo_actual_dt = new DateTime();
if ($hoy->format('d') <= 24) {
    $fecha_fin_ciclo_actual_dt->modify('first day of this month');
} else {
    $fecha_fin_ciclo_actual_dt->modify('first day of next month');
}
$fecha_fin_ciclo_actual_dt->setDate($fecha_fin_ciclo_actual_dt->format('Y'), $fecha_fin_ciclo_actual_dt->format('m'), 24);

$fecha_inicio_mes = $fecha_inicio_ciclo_actual_dt->format('Y-m-d');
$fecha_fin_mes = $fecha_fin_ciclo_actual_dt->format('Y-m-d');

// Construir filtros
$where = [];
$params = [];
$types = '';

if ($idFilter !== '') { $where[] = 'act = ?'; $params[] = $idFilter; $types .= 's'; }
if ($clienteIdFilter !== '') { $where[] = 'cliente_id LIKE ?'; $params[] = '%' . $clienteIdFilter . '%'; $types .= 's'; }
if ($dniFilter !== '') { $where[] = 'documento LIKE ?'; $params[] = '%' . $dniFilter . '%'; $types .= 's'; }
if ($planFilter !== '' && isset($planesMap[$planFilter])) {
    $placeholders = implode(',', array_fill(0, count($planesMap[$planFilter]), '?'));
    $where[] = "plan IN ($placeholders)";
    foreach ($planesMap[$planFilter] as $p) { $params[] = $p; $types .= 's'; }
}

$where[] = 'fecha_consumo BETWEEN ? AND ?';
$params[] = $fecha_inicio_mes;
$params[] = $fecha_fin_mes;
$types .= 'ss';

$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "
SELECT
    act,
    MAX(cliente_id) as cliente_id,
    MAX(documento) as documento,
    MAX(plan) as plan,
    SUM(total_gb) as total_gb
FROM consumos_diarios
$whereSql
GROUP BY act
ORDER BY act
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparando consulta: ' . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Configurar cabeceras para CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="exportado.csv"');

// Abrir salida
$output = fopen('php://output', 'w');

// Escribir cabeceras
fputcsv($output, ['ACT', 'Cliente ID', 'DNI', 'Plan', 'Total GB (Mes actual)'], ';');

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['act'],
        $row['cliente_id'],
        $row['documento'],
        $row['plan'],
        number_format($row['total_gb'], 2, '.', '')
    ], ';');
}

fclose($output);
exit;
