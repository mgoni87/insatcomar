<?php declare(strict_types=1);
require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/lib/functions.php';
header('Content-Type: text/plain; charset=utf-8');

$term = isset($_GET['id']) ? $_GET['id'] : '';
echo "Diag pagos - term={$term}\n\n";

if (!isset($conn) || !($conn instanceof mysqli)) { echo "Sin DB\n"; exit; }

$rows = find_cliente($conn, $term);
if ($rows === null) { echo "find_cliente() => null (error)\n"; exit; }

echo "Recurrentes: ".count($rows['recurrente'])."\n";
foreach ($rows['recurrente'] as $r) {
  echo "  REC {$r['codigo_cliente']} id_fact:{$r['id_factura']} $ {$r['saldo_vencido']} origen: {$r['origen']}\n";
}
echo "Puntuales: ".count($rows['puntual'])."\n";
foreach ($rows['puntual'] as $r) {
  echo "  PUN {$r['codigo_cliente']} id_fact:{$r['id_factura']} $ {$r['saldo_vencido']} origen: {$r['origen']}\n";
}
