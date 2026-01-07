<?php declare(strict_types=1);
/**
 * Mercado Pago - Crear preferencia y redirigir a checkout.
 * - PHP 8.x, cURL.
 * - Token: MP_ACCESS_TOKEN (env) o fallback a token de prueba provisto.
 * - back_urls y notification_url consistentes.
 * - Mensajes de error claros en caso de 4xx/5xx (con ?debug=1 muestra respuesta).
 */
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

header('Content-Type: text/html; charset=UTF-8');

$DEBUG = isset($_GET['debug']) && $_GET['debug'] === '1';

// Access token (prioridad ENV)
$ACCESS_TOKEN = getenv('MP_ACCESS_TOKEN') ?: "APP_USR-885270145956084-101101-a066bcd711c1814fb07ac944d60d9aa0-2698241061";
if (!$ACCESS_TOKEN) { http_response_code(500); echo 'Falta MP_ACCESS_TOKEN'; exit; }

$uuid = $_GET['uuid'] ?? '';
if ($uuid === '') { http_response_code(400); echo 'UUID requerido'; exit; }

// Traer deuda
$stmt = $conn->prepare('SELECT * FROM pagos_pendientes WHERE uuid = ? LIMIT 1');
$stmt->bind_param('s', $uuid);
$stmt->execute();
$res = $stmt->get_result();
$deuda = $res->fetch_assoc();
$stmt->close();
if (!$deuda) { http_response_code(404); echo 'Deuda no encontrada'; exit; }

$amount = (float)($deuda['importe'] ?? $deuda['monto'] ?? $deuda['saldo_vencido'] ?? 0);
if ($amount <= 0) { $amount = 0.01; }

$desc   = ($deuda['tipo'] ?? '') === 'recurrente' ? 'Abono mensual' : 'Pago puntual';
$period = trim((string)($deuda['periodo'] ?? ''));
$origen = trim((string)($deuda['origen'] ?? 'INSAT'));
if ($period !== '') { $desc .= ' ' . $period; }
elseif ($origen !== '') { $desc .= ' ' . $origen; }

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$base   = $scheme . '://' . $_SERVER['HTTP_HOST'];

$preference = [
  "items" => [[
    "title" => $desc,
    "quantity" => 1,
    "currency_id" => "ARS",
    "unit_price" => (float)number_format(max($amount, 0.01), 2, ".", ""),
  ]],
  "external_reference" => $uuid,
  "back_urls" => [
    "success" => $base . "/payments/mp-return.php?uuid=" . urlencode($uuid) . "&status=approved",
    "failure" => $base . "/payments/mp-return.php?uuid=" . urlencode($uuid) . "&status=failure",
    "pending" => $base . "/payments/mp-return.php?uuid=" . urlencode($uuid) . "&status=pending",
  ],
  "auto_return" => "approved",
  "notification_url" => $base . "/payments/mp-webhook.php",
  "binary_mode" => true,
  "payment_methods" => [
    "excluded_payment_types" => [
      ["id" => "ticket"] // ejemplo: excluir efectivo (Rapipago/PagoFacil)
    ]
  ]
];

$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Bearer ' . $ACCESS_TOKEN,
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($resp === false) {
  http_response_code(500);
  echo "Error cURL: " . h($err);
  exit;
}

$data = json_decode((string)$resp, true);
if ($http >= 200 && $http < 300 && isset($data['init_point'])) {
  header('Location: ' . $data['init_point']);
  exit;
}

// Error de API
http_response_code(500);
echo "Mercado Pago error ($http). ";
if ($DEBUG) {
  echo '<pre style=\"white-space:pre-wrap; font:12px ui-monospace\">';
  echo h($resp);
  echo '</pre>';
} else {
  echo "Agregá ?debug=1 al URL para ver detalles.";
}
