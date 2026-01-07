<?php
declare(strict_types=1);
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../includes/conn.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: text/html; charset=utf-8');

$errors = [];
try {
  if (!isset($conn) || !($conn instanceof mysqli)) {
    throw new Exception("Error de conexión a la base de datos.");
  }

  $identificador = trim((string)($_POST['identificador'] ?? ''));
  $nombre        = trim((string)($_POST['nombre'] ?? ''));
  $concepto      = trim((string)($_POST['concepto'] ?? ''));
  $montoRaw      = trim((string)($_POST['monto'] ?? ''));
  $email         = trim((string)($_POST['email'] ?? ''));
  $notas         = trim((string)($_POST['notas'] ?? ''));

  if ($identificador === '' || strlen($identificador) > 32) $errors[] = "Identificador inválido.";
  if ($nombre === '' || strlen($nombre) > 120) $errors[] = "El nombre es obligatorio.";
  if ($concepto === '' || strlen($concepto) > 120) $errors[] = "Concepto inválido.";

  if ($montoRaw === '') {
    $errors[] = "Monto requerido.";
  } else {
    $norm = str_replace(['.', ','], ['', '.'], $montoRaw);
    if (!is_numeric($norm)) $errors[] = "Monto con formato inválido.";
  }

  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email inválido.";
  }

  if ($errors) throw new Exception(implode("\n", $errors));

  $montoCent = (int)round((float)str_replace([','], ['.'], str_replace(['.'], [''], $montoRaw)) * 100);

  // UUID v4
  $data = random_bytes(16);
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
  $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

  $vto            = date('Y-m-d');
  $fecha_emision  = date('Y-m-d');
  $periodo        = date('Y-m');
  $origen         = 'Pago puntual - ' . $concepto;
  $saldo          = number_format($montoCent / 100, 2, '.', '');

  $stmt = $conn->prepare("INSERT INTO pagos_pendientes 
    (uuid, id_cliente, codigo_cliente, nombre, domicilio, nodo, id_factura, 
     fecha_emision, periodo, vencimiento_1, vencimiento_2, vencimiento_3, 
     comprobante, saldo_vencido, origen, tipo)
    VALUES (?, NULL, ?, ?, NULL, NULL, NULL, ?, ?, NULL, NULL, NULL, NULL, ?, ?, 'puntual')");

  $stmt->bind_param('sssssss', $uuid, $identificador, $nombre, $fecha_emision, $periodo, $saldo, $origen);
  $stmt->execute();

  // Mensaje para compartir / copiar
  $link = 'https://pagos.insat.com.ar/?id=' . urlencode($identificador);
  $mensaje = "Pagá tu pedido ingresando en " . $link;

} catch (Throwable $e) {
  $err = $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pago puntual creado — INSAT Pagos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
  <div class="container">
    <div class="col-lg-8 mx-auto">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <?php if (isset($err)): ?>
            <h1 class="h4 mb-3 text-danger">No se pudo crear el pago</h1>
            <pre class="text-danger small mb-0"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></pre>
          <?php else: ?>
            <h1 class="h4 mb-3 text-success">Pago puntual creado correctamente</h1>
            <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($identificador, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mb-1"><strong>Concepto:</strong> <?= htmlspecialchars($concepto, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mb-1"><strong>Monto:</strong> $<?= htmlspecialchars($montoRaw, ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
            <p class="small text-muted">Link para el cliente:</p>
            <div class="alert alert-light border d-flex justify-content-between align-items-start gap-2">
  <code id="msgPago"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></code>
  <button type="button" class="btn btn-outline-primary btn-sm" id="btnCopyMsg" title="Copiar">Copiar</button>
</div>
<div class="small text-success" id="copyOk" style="display:none;">¡Copiado al portapapeles!</div>
            <a href="pago-puntual-nuevo.php" class="btn btn-primary">Crear otro pago</a>
            <a href="../index.php" class="btn btn-outline-secondary">Volver al inicio</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<script>
(function(){
  var btn = document.getElementById('btnCopyMsg');
  if(!btn) return;
  btn.addEventListener('click', async function(){
    var el = document.getElementById('msgPago');
    if(!el) return;
    var text = el.innerText || el.textContent || '';
    try {
      await navigator.clipboard.writeText(text);
      var ok = document.getElementById('copyOk');
      if (ok) { ok.style.display='block'; setTimeout(function(){ ok.style.display='none'; }, 1500); }
    } catch(e) {
      // Fallback for older browsers
      var tmp = document.createElement('textarea');
      tmp.value = text;
      document.body.appendChild(tmp);
      tmp.select();
      try { document.execCommand('copy'); } catch(_){}
      document.body.removeChild(tmp);
      var ok = document.getElementById('copyOk');
      if (ok) { ok.style.display='block'; setTimeout(function(){ ok.style.display='none'; }, 1500); }
    }
  });
})();
</script>
</body>
</html>
