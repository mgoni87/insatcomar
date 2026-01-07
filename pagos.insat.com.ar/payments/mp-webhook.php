<?php declare(strict_types=1);
/**
 * Webhook Mercado Pago.
 * - Lee evento, obtiene payment_id, consulta API MP para validar.
 * - Si status=approved => elimina el pending (pagos_pendientes) por UUID (external_reference).
 * - Responde 200 siempre (idempotente). Log mínima en error_log.
 */
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';

// Leer cuerpo
$raw = file_get_contents('php://input') ?: '';
$hmac = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

// Parse básico del payload
$body = json_decode($raw, true) ?: [];
$type = $body['type'] ?? ($body['action'] ?? '');

// Soporte también para query params de notificación clásica
$payment_id = $_GET['data_id'] ?? $_GET['id'] ?? '';
$topic      = $_GET['type'] ?? $_GET['topic'] ?? '';

function mp_get(string $url, string $token): array {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($resp === false) { $err = curl_error($ch); curl_close($ch); return ['http'=>$http,'data'=>null,'err'=>$err]; }
  curl_close($ch);
  return ['http'=>$http,'data'=>json_decode((string)$resp,true),'err'=>null];
}

$ACCESS_TOKEN = getenv('MP_ACCESS_TOKEN') ?: "APP_USR-885270145956084-101101-a066bcd711c1814fb07ac944d60d9aa0-2698241061";
if ($ACCESS_TOKEN === '') {
  // fallback: intentar detectar token hardcodeado en mercadopago.php
  // (no recomendable, pero mantiene compatibilidad con tu versión actual)
  $mp_file = __DIR__ . '/mercadopago.php';
  if (is_file($mp_file)) {
    $src = file_get_contents($mp_file);
    if (preg_match('/\$ACCESS_TOKEN\s*=\s*\'([^\']+)\'/', $src, $m)) {
      $ACCESS_TOKEN = $m[1];
    }
  }
}
if ($ACCESS_TOKEN === '') { http_response_code(200); echo 'OK'; exit; } // no token => no procesar

// Si vino notificación moderna (type=payment) y data.id
if (!$payment_id && isset($body['data']['id'])) {
  $payment_id = (string)$body['data']['id'];
}
// Si topic indica "payment", continuar; si no, 200 OK (ignoramos)
if (!$payment_id) { http_response_code(200); echo 'OK'; exit; }

// Consultar detalle del pago a la API MP
$base_api = 'https://api.mercadopago.com/v1/payments/' . urlencode($payment_id);
$resp = mp_get($base_api, $ACCESS_TOKEN);
if (!$resp['data']) { http_response_code(200); echo 'OK'; exit; }

$payment = $resp['data'];
$status  = $payment['status'] ?? '';
$extref  = $payment['external_reference'] ?? '';

if ($status === 'approved' && $extref) {
  // Eliminar pendiente por UUID = external_reference
  if ($stmt = $conn->prepare("DELETE FROM pagos_pendientes WHERE uuid = ?")) {
    $stmt->bind_param('s', $extref);
    $stmt->execute();
    $stmt->close();
  }
}

// Responder 200 si o si (MP reintenta de todos modos si 5xx)
http_response_code(200);
header('Content-Type: text/plain; charset=UTF-8');
echo "OK";
