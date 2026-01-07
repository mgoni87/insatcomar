<?php declare(strict_types=1);
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';
if (!isset($conn) || !($conn instanceof mysqli)) { http_response_code(500); echo "Sin DB"; exit; }
if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo "Archivo inválido"; exit; }

$path = $_FILES['csv']['tmp_name'];
$handle = fopen($path, 'r'); if (!$handle) { http_response_code(500); echo "No se pudo abrir el archivo"; exit; }
$delimiter = ';';

$header = fgetcsv($handle, 0, $delimiter);
if (!$header) { http_response_code(400); echo "Cabecera ausente o inválida"; exit; }
$header = array_values(array_filter($header, fn($h) => trim((string)$h) !== ''));

$required = ['Id Cliente','Codigo Cliente','Apellido y Nombre / Razon Social','Domicilio','Nodo','Id Factura','Fecha Emision','Periodo','Vencimiento 1','Vencimiento 2','Vencimiento 3','Comprobante','Saldo Vencido','Origen'];
foreach ($required as $col) { if (!in_array($col, $header, true)) { http_response_code(400); echo "Cabecera inválida: falta '$col'"; exit; } }
$idx = []; foreach ($required as $col) { $idx[$col] = array_search($col, $header, true); }

$sql = "INSERT INTO pagos_pendientes
(uuid, id_cliente, codigo_cliente, nombre, domicilio, nodo, id_factura, fecha_emision, periodo,
 vencimiento_1, vencimiento_2, vencimiento_3, comprobante, saldo_vencido, origen, tipo)
VALUES (UUID(), ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
  nombre=VALUES(nombre), domicilio=VALUES(domicilio), nodo=VALUES(nodo),
  fecha_emision=VALUES(fecha_emision), periodo=VALUES(periodo),
  vencimiento_1=VALUES(vencimiento_1), vencimiento_2=VALUES(vencimiento_2), vencimiento_3=VALUES(vencimiento_3),
  comprobante=VALUES(comprobante), saldo_vencido=VALUES(saldo_vencido), origen=VALUES(origen), tipo=VALUES(tipo)";
$stmt = $conn->prepare($sql); if (!$stmt) { http_response_code(500); echo "Prepare error: ".$conn->error; exit; }

$conn->begin_transaction();
$inserted=0; $updated=0; $rownum=1;

$get = function(array $row, int $i): string { return isset($row[$i]) ? trim((string)$row[$i]) : ''; };

while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
  $rownum++;
  if (count($row) > count($header)) $row = array_slice($row, 0, count($header));
  if (count($row) < count($header)) continue;

  $id_cliente   = (int)$get($row, $idx['Id Cliente']);
  $codigo       = to_utf8($get($row, $idx['Codigo Cliente']));
  $nombre       = to_utf8($get($row, $idx['Apellido y Nombre / Razon Social']));
  $domicilio    = to_utf8($get($row, $idx['Domicilio']));
  $nodo         = to_utf8($get($row, $idx['Nodo']));
  $id_factura   = (int)$get($row, $idx['Id Factura']);
  $fec_emision  = parse_date(to_utf8($get($row, $idx['Fecha Emision']))) ?? '';
  $periodo      = to_utf8($get($row, $idx['Periodo']));
  $v1           = parse_date(to_utf8($get($row, $idx['Vencimiento 1']))) ?? '';
  $v2           = parse_date(to_utf8($get($row, $idx['Vencimiento 2']))) ?? '';
  $v3           = parse_date(to_utf8($get($row, $idx['Vencimiento 3']))) ?? '';
  $comprobante  = (int)$get($row, $idx['Comprobante']);
  $saldo        = parse_amount($get($row, $idx['Saldo Vencido']));
  $origen       = to_utf8($get($row, $idx['Origen']));
  $tipo         = classify_tipo($origen);

  if (!$stmt->bind_param('issssisssssidss', $id_cliente, $codigo, $nombre, $domicilio, $nodo, $id_factura, $fec_emision, $periodo, $v1, $v2, $v3, $comprobante, $saldo, $origen, $tipo)) {
    error_log("Fila {$rownum} bind_param error: ".$stmt->error); continue;
  }
  if (!$stmt->execute()) { error_log("Fila {$rownum} execute error: ".$stmt->error); continue; }
  if ($stmt->affected_rows === 1) $inserted++; elseif ($stmt->affected_rows >= 2) $updated++;
}
fclose($handle);
$conn->commit();
$stmt->close();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><title>Importación</title></head>
<body class="bg-light"><div class="container py-5"><div class="card shadow-sm"><div class="card-body">
<h1 class="h5">Importación finalizada</h1>
<p class="mb-1">Insertados: <b><?= (int)$inserted ?></b></p>
<p class="mb-3">Actualizados: <b><?= (int)$updated ?></b></p>
<a class="btn btn-primary" href="../">Ir al portal</a>
<a class="btn btn-outline-secondary" href="./">Cargar otro archivo</a>
</div></div></div></body></html>
