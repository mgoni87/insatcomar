<?php declare(strict_types=1);
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';
header('Content-Type: text/html; charset=UTF-8');

$uuid       = $_GET['uuid'] ?? '';
$status     = $_GET['status'] ?? '';
$payment_id = $_GET['payment_id'] ?? ($_GET['collection_id'] ?? ''); // MP puede enviar distintos nombres
$DEBUG      = isset($_GET['debug']) && $_GET['debug'] === '1';

/**
 * Verificación opcional contra la API de MP para mayor seguridad.
 * Si hay token y payment_id, confirmamos que:
 *   - status = approved
 *   - external_reference = $uuid
 */
function mp_get(string $url, string $token): array {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);
  if ($resp === false) return ['ok' => false, 'http' => 0, 'err' => $err, 'data' => null];
  $data = json_decode((string)$resp, true);
  return ['ok' => ($http >= 200 && $http < 300), 'http' => $http, 'err' => '', 'data' => $data];
}

$ACCESS_TOKEN = getenv('MP_ACCESS_TOKEN') ?: '';

$canDelete = false;
if ($status === 'approved' && $uuid !== '') {
  if ($payment_id !== '' && $ACCESS_TOKEN !== '') {
    // Confirmamos por API
    $resp = mp_get('https://api.mercadopago.com/v1/payments/' . rawurlencode($payment_id), $ACCESS_TOKEN);
    if ($resp['ok'] && is_array($resp['data'])) {
      $mp_status = $resp['data']['status'] ?? '';
      $extref    = $resp['data']['external_reference'] ?? '';
      if ($mp_status === 'approved' && $extref === $uuid) {
        $canDelete = true;
      }
    }
  } else {
    // Fallback: si no hay token o payment_id, confiamos en back_url (mismo comportamiento visual del sitio)
    $canDelete = true;
  }

  if ($canDelete) {
    if ($stmt = $conn->prepare("DELETE FROM pagos_pendientes WHERE uuid = ?")) {
      $stmt->bind_param('s', $uuid);
      $stmt->execute();
      $stmt->close();
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Resultado del pago</title>
<link rel="icon" href="https://insat.com.ar/img/favicon.png" type="image/png">
<style>
  :root { --brand:#5e2ea5; }
  body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif; background:#f6f7fb; margin:0; }
  .wrap { max-width:680px; margin:48px auto; padding:0 16px; }
  .card { background:#fff; border-radius:16px; box-shadow:0 8px 30px rgba(0,0,0,.08); padding:28px; }
  .title { font-size:22px; font-weight:700; margin:0 0 8px; color:#111; }
  .muted { color:#666; font-size:14px; margin:0 0 20px; }
  .badge { display:inline-block; padding:6px 10px; border-radius:999px; font-size:13px; border:1px solid #ddd; background:#fafafa; color:#333; }
  .ok    { background: var(--brand); color:#fff; border-color:var(--brand) }
  .fail  { background:#e53935; color:#fff; border-color:#e53935 }
  .pend  { background:#ef6c00; color:#fff; border-color:#ef6c00 }
  .grid  { display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:14px; }
  .row   { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border:1px solid #eee; border-radius:12px; background:#fcfcfd; }
  .row b { font-weight:600; }
  .footer { margin-top:20px; color:#888; font-size:13px; }
  .btn { display:inline-block; text-decoration:none; background:var(--brand); color:#fff; padding:10px 14px; border-radius:12px; font-weight:600; }
  .btn:hover { opacity:.92; }
  pre.debug { margin-top:16px; background:#0b1020; color:#d9e1ff; padding:14px; border-radius:12px; overflow:auto; font:12px ui-monospace; }
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <?php
      $label = 'Resultado del pago';
      $class = '';
      if ($status === 'approved') { $label = '¡Pago aprobado!'; $class = 'ok'; }
      elseif ($status === 'failure') { $label = 'Pago rechazado'; $class = 'fail'; }
      elseif ($status === 'pending') { $label = 'Pago pendiente'; $class = 'pend'; }
    ?>
    <div class="title"><?= h($label) ?></div>
    <p class="muted">Este es el resultado informado por Mercado Pago al regresar a nuestro sitio.</p>
    <div class="grid">
      <div class="row"><span>UUID</span><b><?= h($uuid ?: '-') ?></b></div>
      <div class="row"><span>Estado</span><span class="badge <?= h($class) ?>"><?= h($status ?: '-') ?></span></div>
      <div class="row"><span>ID de pago</span><b><?= h($payment_id ?: '-') ?></b></div>
      <div class="row"><span>Acción realizada</span><b><?= $canDelete ? 'Pendiente eliminado' : 'Sin cambios' ?></b></div>
    </div>

    <div class="footer" style="display:flex;justify-content:space-between;align-items:center;">
      <a class="btn" href="/index.php">Volver al inicio</a>
      <span>Si el pago no impacta de inmediato, puede demorar unos segundos.</span>
    </div>

    <?php if ($DEBUG): ?>
      <pre class="debug"><?php
        echo "DEBUG\n";
        echo "status      = " . h($status) . "\n";
        echo "uuid        = " . h($uuid) . "\n";
        echo "payment_id  = " . h($payment_id) . "\n";
        echo "ACCESS_TOKEN= " . ($ACCESS_TOKEN ? 'YES' : 'NO') . "\n";
        echo "canDelete   = " . ($canDelete ? 'YES' : 'NO') . "\n";
      ?></pre>
    <?php endif; ?>
  </div>
</div>
</body>
</html>