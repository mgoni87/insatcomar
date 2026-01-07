<?php

require_once 'config.php'; // Asegúrate de que esta línea esté al principio

date_default_timezone_set(APP_TIMEZONE);

// Conexión a base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Parámetros para filtros
$idFilter = isset($_GET['id']) ? trim($_GET['id']) : '';
$dniFilter = isset($_GET['dni']) ? trim($_GET['dni']) : '';
$planFilter = isset($_GET['plan']) ? trim($_GET['plan']) : '';
$clienteIdFilter = isset($_GET['cliente_id']) ? trim($_GET['cliente_id']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage = 10;

// Parámetros para ordenamiento
$allowedSortColumns = [
    'act' => 'act',
    'cliente_id' => 'cliente_id', // Alias from SELECT
    'dni' => 'documento',       // Alias from SELECT
    'plan' => 'plan',           // Alias from SELECT
    'total_gb' => 'total_gb'    // Alias from SELECT
];
$defaultSortByColumnKey = 'total_gb';
$defaultSortOrder = 'desc';

$sortByParam = isset($_GET['sort_by']) && isset($allowedSortColumns[$_GET['sort_by']]) ? $_GET['sort_by'] : $defaultSortByColumnKey;
$sortOrderParam = isset($_GET['sort_order']) && in_array(strtolower($_GET['sort_order']), ['asc', 'desc']) ? strtolower($_GET['sort_order']) : $defaultSortOrder;

$sqlSortColumn = $allowedSortColumns[$sortByParam]; // Actual column/alias for SQL
$sqlSortOrder = $sortOrderParam;                   // asc or desc for SQL

// Planes y variantes para filtro
$planesMap = PLANES_MAP;

$hoy = new DateTime();
$fecha_inicio_ciclo_actual_dt = new DateTime();
if ($hoy->format('d') <= 25) {
    $fecha_inicio_ciclo_actual_dt->modify('first day of this month');
    $fecha_inicio_ciclo_actual_dt->modify('-1 month');
} else {
    $fecha_inicio_ciclo_actual_dt->modify('first day of this month');
}
$fecha_inicio_ciclo_actual_dt->setDate($fecha_inicio_ciclo_actual_dt->format('Y'), $fecha_inicio_ciclo_actual_dt->format('m'), 25);

$fecha_fin_ciclo_actual_dt = new DateTime();
if ($hoy->format('d') <= 25) {
    $fecha_fin_ciclo_actual_dt->modify('first day of this month');
} else {
    $fecha_fin_ciclo_actual_dt->modify('first day of next month');
}
$fecha_fin_ciclo_actual_dt->setDate($fecha_fin_ciclo_actual_dt->format('Y'), $fecha_fin_ciclo_actual_dt->format('m'), 24);

$fecha_inicio_mes = $fecha_inicio_ciclo_actual_dt->format('Y-m-d');
$fecha_fin_mes = $fecha_fin_ciclo_actual_dt->format('Y-m-d');

// --- PREPARACIÓN DE CONDICIONES WHERE Y PARÁMETROS ---
$where = [];
$params = [];
$types = '';

if ($idFilter !== '') { $where[] = 'cd.act = ?'; $params[] = $idFilter; $types .= 's'; }
if ($clienteIdFilter !== '') { $where[] = 'cd.cliente_id LIKE ?'; $params[] = '%' . $clienteIdFilter . '%'; $types .= 's'; }
if ($dniFilter !== '') { $where[] = 'cd.documento LIKE ?'; $params[] = '%' . $dniFilter . '%'; $types .= 's'; }

$where[] = 'cd.fecha_consumo BETWEEN ? AND ?';
$params[] = $fecha_inicio_mes;
$params[] = $fecha_fin_mes;
$types .= 'ss';

// --- Consulta para contar el total de filas y conexiones para paginación y proyecciones globales ---
$totalSql = "
SELECT
    COUNT(DISTINCT cd.act) as total_conexiones,
    SUM(cd.total_gb) as consumo_total
FROM consumos_diarios cd
JOIN (
    SELECT act, MAX(fecha_consumo) as max_fecha_consumo
    FROM consumos_diarios GROUP BY act
) AS latest_entry ON cd.act = latest_entry.act
JOIN consumos_diarios cd_latest_plan ON latest_entry.act = cd_latest_plan.act AND latest_entry.max_fecha_consumo = cd_latest_plan.fecha_consumo
";

$tempWhereTotal = $where; 
$tempTypesTotal = $types; 
$tempParamsTotal = $params; 

if ($planFilter !== '' && isset($planesMap[$planFilter])) {
    $placeholders = implode(',', array_fill(0, count($planesMap[$planFilter]), '?'));
    $tempWhereTotal[] = "cd_latest_plan.plan IN ($placeholders)"; 
    foreach ($planesMap[$planFilter] as $p) { $tempParamsTotal[] = $p; $tempTypesTotal .= 's'; }
}

$finalWhereTotalSql = 'WHERE ' . implode(' AND ', $tempWhereTotal);
$totalSql .= " $finalWhereTotalSql";

$stmtTotal = $conn->prepare($totalSql);
if ($stmtTotal === false) { die('Error preparing total statement: ' . $conn->error . ' SQL: ' . $totalSql); }
$stmtTotal->bind_param($tempTypesTotal, ...$tempParamsTotal);
$stmtTotal->execute();
$stmtTotal->bind_result($totalConexiones, $consumoTotal);
$stmtTotal->fetch();
$stmtTotal->close();

$consumoPromedio = $totalConexiones > 0 ? ($consumoTotal ?? 0) / $totalConexiones : 0;

$fecha_inicio_ciclo_global_dt = $fecha_inicio_ciclo_actual_dt;
$fecha_fin_ciclo_global_dt = $fecha_fin_ciclo_actual_dt;
$fechaAyer_dt = (new DateTime())->modify('-1 day');
if ($fechaAyer_dt > $fecha_fin_ciclo_global_dt) { $fechaAyer_dt = clone $fecha_fin_ciclo_global_dt; }
$intervalo_transcurrido_global = $fecha_inicio_ciclo_global_dt->diff($fechaAyer_dt);
$diasTranscurridosGlobal = $intervalo_transcurrido_global->days + 1;
if ($diasTranscurridosGlobal <= 0) $diasTranscurridosGlobal = 1;
$intervalo_total_global = $fecha_inicio_ciclo_global_dt->diff($fecha_fin_ciclo_global_dt);
$diasTotalesGlobal = $intervalo_total_global->days + 1;
if ($diasTotalesGlobal <=0) $diasTotalesGlobal = 1;
$proyeccionGlobal = $diasTranscurridosGlobal > 0 ? (($consumoTotal ?? 0) / $diasTranscurridosGlobal) * $diasTotalesGlobal : 0;
$proyeccionPromedioGlobal = ($totalConexiones > 0) ? ($proyeccionGlobal / $totalConexiones) : 0;

// --- Consulta para el total de filas para paginación ---
$countSql = "SELECT COUNT(DISTINCT cd.act)
FROM consumos_diarios cd
JOIN (
    SELECT act, MAX(fecha_consumo) as max_fecha_consumo
    FROM consumos_diarios GROUP BY act
) AS latest_entry ON cd.act = latest_entry.act
JOIN consumos_diarios cd_latest_plan ON latest_entry.act = cd_latest_plan.act AND latest_entry.max_fecha_consumo = cd_latest_plan.fecha_consumo
$finalWhereTotalSql";

$stmtCount = $conn->prepare($countSql);
if ($stmtCount === false) { die('Error preparing count statement: ' . $conn->error . ' SQL: ' . $countSql); }
$stmtCount->bind_param($tempTypesTotal, ...$tempParamsTotal); 
$stmtCount->execute();
$stmtCount->bind_result($totalRows);
$stmtCount->fetch();
$stmtCount->close();

$totalPages = $totalRows > 0 ? ceil($totalRows / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// --- Consulta PRINCIPAL de la tabla ---
$sql = "
SELECT
    cd.act, MAX(cd.cliente_id) as cliente_id, MAX(cd.documento) as documento,
    cd_latest_plan.plan as plan, SUM(cd.total_gb) as total_gb
FROM consumos_diarios cd
JOIN (
    SELECT act, MAX(fecha_consumo) as max_fecha_consumo
    FROM consumos_diarios GROUP BY act
) AS latest_entry ON cd.act = latest_entry.act
JOIN consumos_diarios cd_latest_plan ON latest_entry.act = cd_latest_plan.act AND latest_entry.max_fecha_consumo = cd_latest_plan.fecha_consumo
";
$tempWhereTable = $where; $tempTypesTable = $types; $tempParamsTable = $params;
if ($planFilter !== '' && isset($planesMap[$planFilter])) {
    $placeholders = implode(',', array_fill(0, count($planesMap[$planFilter]), '?'));
    $tempWhereTable[] = "cd_latest_plan.plan IN ($placeholders)"; 
    foreach ($planesMap[$planFilter] as $p) { $tempParamsTable[] = $p; $tempTypesTable .= 's'; }
}
$finalWhereTableSql = 'WHERE ' . implode(' AND ', $tempWhereTable);
$sql .= " $finalWhereTableSql GROUP BY cd.act, cd_latest_plan.plan ORDER BY $sqlSortColumn $sqlSortOrder LIMIT ? OFFSET ?";
$finalParamsTable = $tempParamsTable; $currentTypesTable = $tempTypesTable; 
$finalParamsTable[] = $perPage; $currentTypesTable .= 'i';        
$finalParamsTable[] = $offset; $currentTypesTable .= 'i';        

$stmt = $conn->prepare($sql);
if ($stmt === false) { die('Error preparing main statement: ' . $conn->error . ' SQL: ' . $sql); }
$stmt->bind_param($currentTypesTable, ...$finalParamsTable);
$stmt->execute();
$result = $stmt->get_result();
$rowsForTable = [];
if ($result) { while ($row = $result->fetch_assoc()) { $rowsForTable[] = $row; } }

function getSortLink($columnKeyForLink, $columnDisplayName, $currentSortByKey, $currentSortOrder) {
    $icon = '';
    if ($columnKeyForLink == $currentSortByKey) {
        $newSortOrder = ($currentSortOrder == 'asc') ? 'desc' : 'asc';
        $icon = ($currentSortOrder == 'asc') ? ' &uarr;' : ' &darr;'; 
    } else { $newSortOrder = 'asc'; }
    $linkParams = array_merge($_GET, ['sort_by' => $columnKeyForLink, 'sort_order' => $newSortOrder]);
    return '<a href="?' . http_build_query($linkParams) . '">' . htmlspecialchars($columnDisplayName) . $icon . '</a>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Consulta de Consumos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-fixed-width { min-width: 120px; }
        /* Dark mode styles */
        body.dark-mode {
            background-color: #212529 !important; /* Bootstrap dark bg */
            color: #f8f9fa; /* General text color for the body in dark mode */
        }
        body.dark-mode .table {
            --bs-table-bg: #2c3034;             /* A slightly darker background */
            --bs-table-striped-bg: #3a3f44;     /* Adjustment for striped rows */
            --bs-table-color: #ffffff;          /* Pure white text for all table text */
            --bs-table-border-color: #495057;
            --bs-table-hover-bg: #454b52;
        }
        body.dark-mode .table tbody tr td {
            color: #ffffff !important; /* Force text color to white */
        }
        body.dark-mode .table-striped > tbody > tr {
            color: #ffffff !important; /* Ensure striped rows also have white text */
        }
        body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) {
            color: #ffffff !important;
        }
        body.dark-mode .table-striped > tbody > tr:nth-of-type(even) {
            color: #ffffff !important;
        }
        body.dark-mode .table-dark {
            --bs-table-bg: #1a1d20;
            --bs-table-color: #fff;
            --bs-table-border-color: #32383e;
        }
        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background-color: #2b3035;
            color: #fff;
            border-color: #495057;
        }
        body.dark-mode .form-control::placeholder { color: #adb5bd; }
        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background-color: #2b3035; color: #fff;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        body.dark-mode .alert-info {
            background-color:rgb(21, 62, 124); /* This is Bootstrap's primary blue */
            border-color:rgb(19, 55, 109);
            color: #fff;
        }
        body.dark-mode .page-link {
            background-color: #343a40; border-color: #495057; color: #0d6efd;
        }
        body.dark-mode .page-link:hover { background-color: #495057; color: #0a58ca; }
        body.dark-mode .page-item.disabled .page-link {
            color: #6c757d; background-color: #212529; border-color: #495057;
        }
        body.dark-mode .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        body.dark-mode .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
        body.dark-mode .btn-success { background-color: #198754; border-color: #198754; }
        body.dark-mode .btn-success:hover { background-color: #157347; border-color: #146c43; }
        body.dark-mode .btn-info {
            background-color: rgb(0 0 0 / 22%); /* Un cyan más oscuro para el fondo */
            border-color: #0a8396;
            color: #000000; /* Texto blanco para mejor contraste */
        }
        body.dark-mode .btn-info:hover {
            background-color: #086f7f; /* Un poco más oscuro para el efecto hover */
            border-color: #086f7f;
            color: #ffffff;
        }
        body.dark-mode .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
        body.dark-mode .btn-secondary:hover { background-color: #5c636a; border-color: #565e64; }
        body.dark-mode h1, body.dark-mode h2 { color: #f8f9fa; }
        body.dark-mode .table a { color: #69b5ff; }
        body.dark-mode .table a:hover { color: #8bc4ff; }
        #theme-toggle { color: white; }
        body.dark-mode #theme-toggle { color: white; }
    </style>
</head>
<body class="bg-light">
    <button id="theme-toggle" class="btn btn-secondary position-fixed top-0 start-0 m-3" style="z-index: 1050;" title="Toggle theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars-fill" viewBox="0 0 16 16" style="display: none;">
            <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 12.31 3.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097z"/>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16">
            <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
        </svg>
    </button>

<div class="container my-5">
    <h1 class="mb-4 text-center">Consulta de Consumos Mensuales</h1>
    <form method="get" action="consulta.php" class="row g-3 mb-4 align-items-center">
        <div class="col-md-2"><input type="text" name="id" value="<?= htmlspecialchars($idFilter) ?>" class="form-control" placeholder="Buscar por ACT" /></div>
        <div class="col-md-2"><input type="text" name="cliente_id" value="<?= htmlspecialchars($clienteIdFilter) ?>" class="form-control" placeholder="Buscar por Cliente ID" /></div>
        <div class="col-md-3"><input type="text" name="dni" value="<?= htmlspecialchars($dniFilter) ?>" class="form-control" placeholder="Buscar por DNI" /></div>
        <div class="col-md-3">
            <select name="plan" class="form-select">
                <option value="">Todos los planes</option>
                <?php foreach ($planesMap as $planName => $variants): ?>
                    <option value="<?= htmlspecialchars($planName) ?>" <?= $planFilter === $planName ? 'selected' : '' ?>><?= htmlspecialchars($planName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-primary btn-fixed-width">Filtrar</button></div>
    </form>
    <div class="mb-4">
        <form method="post" action="export_csv.php" class="d-inline">
            <?php
            $exportParams = $_GET;
            foreach ($exportParams as $key => $value):
                if (is_array($value)) {
                    foreach ($value as $subValue) { echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($subValue) . '">'; }
                } else { echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">'; }
            endforeach;
            ?>
            <button type="submit" class="btn btn-success btn-fixed-width">Exportar CSV</button>
        </form>
        <a href="scraper.php" target="_blank" class="btn btn-info btn-fixed-width">Ejecutar Scraper</a>
    </div>
    <div class="mb-4">
        <div class="alert alert-info">
            <strong>Total conexiones:</strong> <?= $totalConexiones ?> |
            <strong>Consumo total:</strong> <?= number_format($consumoTotal ?? 0, 2) ?> GB |
            <strong>Consumo promedio:</strong> <?= number_format($consumoPromedio, 2) ?> GB/conexión |
            <strong>Proyección del promedio:</strong> <?= number_format($proyeccionPromedioGlobal, 2) ?> GB
            <hr style="height:0px; visibility:hidden; margin: 0px" /><br><em>Los valores corresponden al ciclo actual</em><br/>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="text-center"><?= getSortLink('act', 'ACT', $sortByParam, $sortOrderParam) ?></th>
                    <th class="text-center"><?= getSortLink('cliente_id', 'Cliente ID', $sortByParam, $sortOrderParam) ?></th>
                    <th class="text-center"><?= getSortLink('dni', 'DNI', $sortByParam, $sortOrderParam) ?></th>
                    <th class="text-center"><?= getSortLink('plan', 'Plan', $sortByParam, $sortOrderParam) ?></th>
                    <th class="text-center"><?= getSortLink('total_gb', 'Total GB', $sortByParam, $sortOrderParam) ?></th>
                    <th class="text-center">Proyección GB</th>
                    <th class="text-center">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rowsForTable)): ?>
                    <tr><td colspan="7" class="text-center">No se encontraron resultados</td></tr>
                <?php else: ?>
                    <?php foreach ($rowsForTable as $row):
                        $individualProyeccion = 0;
                        if ($diasTranscurridosGlobal > 0) { $individualProyeccion = ($row['total_gb'] / $diasTranscurridosGlobal) * $diasTotalesGlobal; }
                    ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($row['act']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['cliente_id']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['documento']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['plan']) ?></td>
                        <td class="text-center"><?= number_format($row['total_gb'], 2) ?></td>
                        <td class="text-center"><?= number_format($individualProyeccion, 2) ?></td>
                        <td class="text-center"><a href="detalle.php?act=<?= urlencode($row['act']) ?>" class="btn btn-sm btn-info">Ver detalle</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav aria-label="Paginación">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a></li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            <?php endif; ?>
            <li class="page-item disabled"><span class="page-link">Página <?= $page ?> de <?= $totalPages ?></span></li>
            <?php if ($page < $totalPages && $totalPages > 1): ?>
                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Siguiente</a></li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
// --- LÓGICA DE DATOS PARA EL GRÁFICO DE PROYECCIÓN ---
$chart_fecha_inicio_ciclo_ampliado = clone $fecha_inicio_ciclo_actual_dt;
$chart_fecha_inicio_ciclo_ampliado->modify('-1 month');
$chart_fecha_fin_grafico = (new DateTime())->modify('-1 day');
$fechas_para_grafico = [];
$currentDateIterator = clone $chart_fecha_inicio_ciclo_ampliado;
while ($currentDateIterator <= $chart_fecha_fin_grafico) {
    $fechas_para_grafico[] = $currentDateIterator->format('Y-m-d');
    $currentDateIterator->modify('+1 day');
}
$actsToQueryForGraph = [];
if (!empty($rowsForTable)) { $actsToQueryForGraph = array_column($rowsForTable, 'act'); }
elseif (!empty($idFilter)) { $actsToQueryForGraph = [$idFilter]; }

$consumoDiarioData = []; $actsToChart = []; 
if (!empty($actsToQueryForGraph)) {
    $placeholdersForActs = implode(',', array_fill(0, count($actsToQueryForGraph), '?'));
    $historicoSql = "SELECT act, fecha_consumo, SUM(total_gb) as consumo_diario FROM consumos_diarios WHERE act IN ($placeholdersForActs) AND fecha_consumo BETWEEN ? AND ? GROUP BY act, fecha_consumo ORDER BY act, fecha_consumo";
    $paramsHistorico = array_merge($actsToQueryForGraph, [$chart_fecha_inicio_ciclo_ampliado->format('Y-m-d'), $chart_fecha_fin_grafico->format('Y-m-d')]);
    $typesHistorico = str_repeat('s', count($actsToQueryForGraph)) . 'ss';
    $stmtHistorico = $conn->prepare($historicoSql);
    if ($stmtHistorico === false) { die('Error preparando la sentencia histórica: ' . $conn->error); }
    $stmtHistorico->bind_param($typesHistorico, ...$paramsHistorico);
    $stmtHistorico->execute();
    $historicoResult = $stmtHistorico->get_result();
    if ($historicoResult) {
        while ($row = $historicoResult->fetch_assoc()) {
            $act = $row['act']; $fecha = $row['fecha_consumo']; $consumoDia = (float) $row['consumo_diario'];
            if (!isset($consumoDiarioData[$act])) { $consumoDiarioData[$act] = []; }
            $consumoDiarioData[$act][$fecha] = $consumoDia;
        }
    }
    $stmtHistorico->close();
    $actsToChart = array_keys($consumoDiarioData);
    $proyeccionesParaOrdenar = [];
    foreach ($rowsForTable as $row) {
        if (in_array($row['act'], $actsToChart)) {
            $act = $row['act']; $totalGbActual = $row['total_gb']; 
            $individualProyeccionParaOrdenar = 0;
            if ($diasTranscurridosGlobal > 0) { $individualProyeccionParaOrdenar = ($totalGbActual / $diasTranscurridosGlobal) * $diasTotalesGlobal; }
            $proyeccionesParaOrdenar[$act] = $individualProyeccionParaOrdenar;
        }
    }
    usort($actsToChart, function($actA, $actB) use ($proyeccionesParaOrdenar) {
        $projA = $proyeccionesParaOrdenar[$actA] ?? 0; $projB = $proyeccionesParaOrdenar[$actB] ?? 0;
        if ($projA == $projB) { return 0; } return ($projA < $projB) ? 1 : -1; 
    });
} 
$proyeccionesPorAct = [];
foreach ($actsToChart as $act) {
    $acumulado = 0; $proyeccionesPorAct[$act] = []; $currentCycleStartDate = null; 
    foreach ($fechas_para_grafico as $fechaCicloStr) {
        $fechaCiclo_dt = new DateTime($fechaCicloStr);
        $dia_mes_fechaCiclo = (int) $fechaCiclo_dt->format('d'); $mes_fechaCiclo = (int) $fechaCiclo_dt->format('m'); $ano_fechaCiclo = (int) $fechaCiclo_dt->format('Y');
        $temp_cycle_start = new DateTime();
        if ($dia_mes_fechaCiclo <= 24) { $temp_cycle_start->setDate($ano_fechaCiclo, $mes_fechaCiclo, 25); $temp_cycle_start->modify('-1 month'); } 
        else { $temp_cycle_start->setDate($ano_fechaCiclo, $mes_fechaCiclo, 25); }
        $thisCycleStartDate = $temp_cycle_start->format('Y-m-d');
        if ($currentCycleStartDate === null || $thisCycleStartDate !== $currentCycleStartDate) { $acumulado = 0; $currentCycleStartDate = $thisCycleStartDate; }
        $consumoHoy = $consumoDiarioData[$act][$fechaCicloStr] ?? 0; $acumulado += $consumoHoy;
        $currentCycleStart_dt = new DateTime($currentCycleStartDate);
        $intervalo_transcurrido_ciclo = $currentCycleStart_dt->diff($fechaCiclo_dt); $diasTranscurridosCalculo = $intervalo_transcurrido_ciclo->days + 1;
        $temp_cycle_end = clone $currentCycleStart_dt; $temp_cycle_end->modify('+1 month'); $temp_cycle_end->setDate($temp_cycle_end->format('Y'), $temp_cycle_end->format('m'), 24);
        $intervalo_total_este_ciclo = $currentCycleStart_dt->diff($temp_cycle_end); $diasTotalesEsteCiclo = $intervalo_total_este_ciclo->days + 1;
        $proyeccionDiaria = $diasTranscurridosCalculo > 0 ? ($acumulado / $diasTranscurridosCalculo) * $diasTotalesEsteCiclo : 0;
        if ($fechaCiclo_dt <= $chart_fecha_fin_grafico) { $proyeccionesPorAct[$act][$fechaCicloStr] = round($proyeccionDiaria, 2); } 
        else { $lastKnownProjection = end($proyeccionesPorAct[$act]); $proyeccionesPorAct[$act][$fechaCicloStr] = ($lastKnownProjection !== false) ? $lastKnownProjection : null; }
    }
}
?>
<div class="my-5 mx-auto" style="width: 85%; height: 800px; position: relative;">
    <h2 class="mb-4 text-center">Proyección Mensual por Cliente</h2>
    <canvas id="proyeccionChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare Chart.js data (must be defined before theme switcher script tries to use it)
const labels = <?= json_encode($fechas_para_grafico) ?>;
const datasets = [];
const fixedColors = ['#E6194B', '#3CB44B', '#4363D8', '#F58231', '#911EB4', '#42D4F4', '#F032E6', '#BFEF45', '#9A6324', '#800000'];
let colorIndex = 0;

<?php foreach ($proyeccionesPorAct as $act => $proyecciones): ?>
const proyecciones_<?= preg_replace('/[^a-zA-Z0-9_]/', '_', $act) ?> = <?= json_encode($proyecciones) ?>;
datasets.push({
    label: '<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>',
    data: labels.map(date => proyecciones_<?= preg_replace('/[^a-zA-Z0-9_]/', '_', $act) ?>[date] ?? null),
    borderColor: fixedColors[colorIndex % fixedColors.length],
    fill: false,
    tension: 0.2
});
colorIndex++;
<?php endforeach; ?>

// Global variable to hold the chart instance
let proyeccionChartInstance = null;
const chartCtx = document.getElementById('proyeccionChart') ? document.getElementById('proyeccionChart').getContext('2d') : null;

// Define el plugin de fondo
const chartBackgroundPlugin = {
    id: 'chartBackground',
    beforeDraw: (chart, args, options) => {
        const { ctx, chartArea: { left, top, right, bottom, width, height } } = chart;
        ctx.save();
        ctx.fillStyle = options.backgroundColor; // Usa el color definido en las opciones del plugin
        ctx.fillRect(left, top, width, height);
        ctx.restore();
    }
};

if (chartCtx) {
    proyeccionChartInstance = new Chart(chartCtx, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Evolución de Proyección Mensual'},
                // Opciones para nuestro plugin de fondo - Keep this static!
                chartBackground: {
                    backgroundColor: 'rgb(46, 43, 43)' // Permanent dark background
                }
            },
            interaction: { mode: 'nearest', intersect: true },
            scales: {
                y: {
                    title: { display: true, text: 'GB Proyectados'},
                    ticks: {
                        // Initial color, will be updated by updateChartThemeStyle
                        color: 'black'
                    },
                    grid: {
                        // This is the initial setup for the custom grid line colors.
                        // It will be re-assigned in updateChartThemeStyle to maintain its functionality.
                        color: (context) => {
                            if (context.tick.value === 150 || context.tick.value === 250) {
                                return 'rgb(250, 146, 146)'; // Red for 100 and 150
                            }
                            if (context.tick.value === 0) {
                                // Default to a color that works for the initial theme.
                                // This will be dynamically updated by `updateChartThemeStyle`.
                                return 'rgba(255, 255, 255, 0.5)';
                            }
                            // Default grid color for other lines. Will be updated by `updateChartThemeStyle`.
                            return 'rgba(255, 255, 255, 0.1)';
                        }
                    }
                },
                x: {
                    title: { display: true, text: 'Fecha'},
                    ticks: {
                        // Initial color, will be updated by updateChartThemeStyle
                        color: 'white'
                    },
                    grid: {
                        // Initial color, will be updated by updateChartThemeStyle
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        },
        // Registra el plugin aquí
        plugins: [chartBackgroundPlugin]
    });
}

// Theme toggle and chart update logic (MUST be after chart instantiation)
const themeToggleButton = document.getElementById('theme-toggle');
const pageBody = document.body;
const sunIconSvg = themeToggleButton ? themeToggleButton.querySelector('.bi-sun-fill') : null;
const moonIconSvg = themeToggleButton ? themeToggleButton.querySelector('.bi-moon-stars-fill') : null;

function updateChartThemeStyle(theme) {
    if (!proyeccionChartInstance) return;
    const isDark = theme === 'dark';
    const textColor = isDark ? '#f8f9fa' : '#333'; // Text color for axis titles and ticks
    const generalGridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'; // General grid line color based on theme
    const titleColor = isDark ? '#f8f9fa' : '#333'; // Main chart title color based on theme

    // Main Chart Title - This WILL change
    proyeccionChartInstance.options.plugins.title.color = titleColor;

    // Legend text color - This WILL change
    proyeccionChartInstance.options.plugins.legend.labels.color = textColor;

    // Y-axis
    if (proyeccionChartInstance.options.scales.y) {
        proyeccionChartInstance.options.scales.y.title.color = textColor; // Y-axis title color
        proyeccionChartInstance.options.scales.y.ticks.color = textColor; // Y-axis ticks color
        // Preserve the custom grid line logic for 100 and 150,
        // and apply the general grid color for other lines based on the theme.
        proyeccionChartInstance.options.scales.y.grid.color = (context) => {
            if (context.tick.value === 150 || context.tick.value === 250) {
                return 'rgb(250, 146, 146)'; // Stays red
            }
            if (context.tick.value === 0) {
                return isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)'; // 0 line color changes with theme
            }
            return generalGridColor; // Other grid lines change with theme
        };
    }
    // X-axis
    if (proyeccionChartInstance.options.scales.x) {
        proyeccionChartInstance.options.scales.x.title.color = textColor; // X-axis title color
        proyeccionChartInstance.options.scales.x.ticks.color = textColor; // X-axis ticks color
        proyeccionChartInstance.options.scales.x.grid.color = generalGridColor; // X-axis grid lines change with theme
    }

    // Explicitly set the chart background color if it needs to be static,
    // though it's already set in the initial chart options.
    // Ensure this is NOT changed dynamically if you want it to remain dark.
    if (proyeccionChartInstance.options.plugins.chartBackground) {
        // Do not update the background color here if it should remain static
        // proyeccionChartInstance.options.plugins.chartBackground.backgroundColor = 'rgb(46, 43, 43)'; // Already set during initialization
    }

    // Update the chart to apply the new options
    proyeccionChartInstance.update();
}

function applyPageTheme(theme) {
    if (theme === 'dark') {
        pageBody.classList.add('dark-mode');
        pageBody.classList.remove('bg-light');
        if(sunIconSvg) sunIconSvg.style.display = 'inline-block';
        if(moonIconSvg) moonIconSvg.style.display = 'none';
    } else {
        pageBody.classList.remove('dark-mode');
        pageBody.classList.add('bg-light'); // Ensure bg-light is present for light mode
        if(sunIconSvg) sunIconSvg.style.display = 'none';
        if(moonIconSvg) moonIconSvg.style.display = 'inline-block';
    }
    updateChartThemeStyle(theme);
}

if (themeToggleButton) {
    themeToggleButton.addEventListener('click', () => {
        const newTheme = pageBody.classList.contains('dark-mode') ? 'light' : 'dark';
        localStorage.setItem('theme', newTheme);
        applyPageTheme(newTheme);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light'; // Default to light
    applyPageTheme(savedTheme);
});
</script>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close(); 
$conn->close();
?>