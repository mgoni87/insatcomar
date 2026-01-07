<?php declare(strict_types=1);
/**
 * Integra SiPago Checkout (tarjetas débito/crédito/prepaga).
 * Flujo: obtiene JWT -> crea orden -> redirige a $order->data->links->checkout
 * Variables de entorno necesarias:
 *  SIPAGO_CLIENT_ID
 *  SIPAGO_CLIENT_SECRET
 *  SIPAGO_AUTH_BASE (p.ej. https://auth.geopagos.com)
 *  SIPAGO_API_BASE  (p.ej. https://api.sipago.coop)
 */
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';
$CID   = getenv('SIPAGO_CLIENT_ID') ?: '';
$CSEC  = getenv('SIPAGO_CLIENT_SECRET') ?: '';
$AUTH  = getenv('SIPAGO_AUTH_BASE') ?: 'https://auth.geopagos.com';
$API   = getenv('SIPAGO_API_BASE') ?: 'https://api.sipago.coop';
if (!$CID || !$CSEC) { http_response_code(500); echo "Faltan variables de entorno SiPago (SIPAGO_CLIENT_ID / SIPAGO_CLIENT_SECRET)"; exit; }
$uuid = $_GET['uuid'] ?? '';
if ($uuid === '') { http_response_code(400); echo 'UUID requerido'; exit; }
$stmt = $conn->prepare("SELECT * FROM pagos_pendientes WHERE uuid = ? LIMIT 1");
$stmt->bind_param('s', $uuid);
$stmt->execute();
$res = $stmt->get_result();
$deuda = $res->fetch_assoc();
if (!$deuda) { http_response_code(404); echo "Deuda no encontrada"; exit; }
$amount = (int)round(((float)$deuda['saldo_vencido']) * 100); // SiPago usa amount entero? (032 = ARS). Usamos enteros en centavos.
$desc   = ($deuda['tipo'] === 'recurrente' ? 'Abono mensual ' : 'Pago puntual ') . ($deuda['periodo'] ?: $deuda['origen'] ?: 'INSAT');
// 1) Obtener JWT
$payload = json_encode([
  "grant_type" => "client_credentials",
  "client_id" => $CID,
  "client_secret" => $CSEC,
  "scope" => "*"
], JSON_UNESCAPED_SLASHES);
$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => rtrim($AUTH, '/') . '/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $payload,
  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$tokenResp = curl_exec($ch);
if ($tokenResp === false) { http_response_code(500); echo "cURL auth error: " . curl_error($ch); exit; }
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http < 200 || $http >= 300) { http_response_code(500); echo "Auth error ($http): " . $tokenResp; exit; }
$tok = json_decode($tokenResp, true);
$jwt = $tok['access_token'] ?? '';
if (!$jwt) { http_response_code(500); echo "No se obtuvo access_token de SiPago"; exit; }
// 2) Crear intención de pago
$body = [
  "data" => [
    "attributes" => [
      "redirect_urls" => [
        "success" => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'),
        "failed"  => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/')
      ],
      "currency" => "032",
      "items" => [[
        "id" => $deuda['id_factura'] ?: 0,
        "name" => $desc,
        "unitPrice" => ["currency"=>"032", "amount" => $amount],
        "quantity" => 1
      ]]
    ]
  ]
];
$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => rtrim($API, '/') . '/api/v2/orders',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
  CURLOPT_HTTPHEADER => [
    'Authorization: Bearer ' . $jwt,
    'Content-Type: application/vnd.api+json'
  ],
]);
$orderResp = curl_exec($ch);
if ($orderResp === false) { http_response_code(500); echo "cURL order error: " . curl_error($ch); exit; }
$http2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($http2 >= 200 && $http2 < 300) {
  $order = json_decode($orderResp);
  // Redirigir a link de checkout
  $link = $order->data->links->checkout ?? null;
  if ($link) { header("Location: " . $link); exit; }
  // fallback: mostrar respuesta
  header('Content-Type: text/plain; charset=utf-8');
  echo $orderResp;
  exit;
}
http_response_code(500);
echo "SiPago error ($http2): <pre>" . htmlspecialchars($orderResp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</pre>";
