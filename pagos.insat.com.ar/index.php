<?php declare(strict_types=1);
/**
 * Portal de pagos - pagos.insat.com.ar
 * PHP 8.3 + MySQLi
 * Conexión: /includes/conn.php define $conn (mysqli)
 */
require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/lib/functions.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');
$term = isset($_POST['id']) ? trim((string)$_POST['id']) : (isset($_GET['id']) ? trim((string)$_GET['id']) : '');
if (isset($_GET['reset'])) { $term=''; }
$hasIdent = ($term !== '');

$_reset = isset($_GET['reset']);
if ($_reset) { $term = ''; $data = ['recurrente'=>[], 'puntual'=>[]]; $clienteNombre = null;
$hasResults = false; }

$term = isset($_POST['id']) ? trim((string)$_POST['id']) : (isset($_GET['id']) ? trim((string)$_GET['id']) : '');
$error = null;
$data = ['recurrente'=>[], 'puntual'=>[]];
$clienteNombre = null;
$hasResults = false;
// Buscar datos
if ($term !== '') {
  if (!isset($conn) || !($conn instanceof mysqli)) {
    $error = 'No se pudo iniciar la conexión ($conn). Verifique /includes/conn.php';
  } else {
    $data = find_cliente($conn, $term) ?: ['recurrente'=>[], 'puntual'=>[]];
    // nombre del cliente (primera fila disponible)
    $any = $data['recurrente'][0] ?? $data['puntual'][0] ?? null;
    if ($any && isset($any['nombre'])) {
      $clienteNombre = $any['nombre'];
    }
  }
}
function saludo_horario(): string {
  $h = (int)date('G');
  if ($h >= 6 && $h < 12) return 'Buenos días';
  if ($h >= 12 && $h < 19) return 'Buenas tardes';
  return 'Buenas noches';
}
// helpers visuales
function venc_badge(?string $venc): string {
  if (!$venc) return '<span class="badge text-bg-secondary">Sin venc.</span>';
  $ts = strtotime($venc);
  if ($ts === false) return '<span class="badge text-bg-secondary">Sin venc.</span>';
  $hoy = strtotime(date('Y-m-d'));
  if ($ts < $hoy) return '<span class="badge text-bg-danger">Vencido '.date('d/m', $ts).'</span>';
  if ($ts === $hoy) return '<span class="badge text-bg-warning">Vence hoy</span>';
  return '<span class="badge text-bg-success">Vence '.date('d/m', $ts).'</span>';
}
// Logo transparente pedido
$logo = 'https://b3300440.smushcdn.com/3300440/wp-content/uploads/2021/02/insat-blanco-transp-editable-e1613612173933.png?lossy=2&strip=1&webp=0';
?><!doctype html>
<html lang="es">
<head>
  <link rel="icon" href="https://insat.com.ar/img/favicon.png" type="image/png">

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal de pagos | INSAT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{ --brand:#7E3CF0; --brand-600:#6a29e9; --brand-100:#f3eaff; }
    html, body { height: 100%; }
    body{
      min-height:100vh; display:flex; flex-direction:column;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans";
      font-size:16px;
      background:#faf9ff;
    }
    /* Header */
    .brand-bg{ background: linear-gradient(135deg, var(--brand), #a787ff); }
    .brand-logo{ height:42px; width:auto; display:block; }
    /* Card contenedora */
    .card-hero{ border:0; box-shadow: 0 10px 30px rgba(126,60,240,.15); border-radius: 1.25rem; background:#fff; }
    /* Botón buscar */
    .btn-brand{ background:var(--brand); border-color:var(--brand); color:#fff; font-weight:600; }
    .btn-brand:hover{ background:var(--brand-600); border-color:var(--brand-600); color:#fff; }
    /* Tarjetas de pago */
    .pay-card{
      border:1px solid #ece9ff;
      border-radius:1rem;
      box-shadow:0 6px 18px rgba(126,60,240,.08);
      background:#fff;
    }
    .pay-card .icon{
      width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center;
      border-radius:12px; background:var(--brand-100); color:var(--brand); font-size:22px;
    }
    .section-title{ border-left:4px solid var(--brand); padding-left:.5rem; }
    /* Botones de pago (proporcionados y centrados) */
    .pay-actions{ width:100%; }
    .pay-actions .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      padding:.65rem .9rem; height:auto; line-height:1.1; font-weight:600;
      border-radius:.9rem;
    }
    .btn-mp{ background:#fff; border:1px solid #cfd3ff; color:#1f1f1f; }
    .btn-mp:hover{ background:#f6f7ff; border-color:#b6bdff; }
    .btn-card{ background:var(--brand); border-color:var(--brand); color:#fff; }
    .btn-card:hover{ background:var(--brand-600); border-color:var(--brand-600); color:#fff; }
    /* Footer negro con separación del botón */
    .footer{ margin-top:auto; background:#000; color:#bbb; }
    .footer a{ color:#fff; text-decoration:none; }
    .footer .admin-wrap{ margin-top:.75rem; }
    /* Disposición responsiva */
    @media (max-width: 576px){
      .pay-card .fw-semibold{ font-size:1.06rem; }
      .pay-card .fs-5{ font-size:1.2rem !important; } /* importe */
      .pay-actions{ display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
      .pay-actions .btn{ width:100%; }
    }
    @media (min-width: 577px){
      .pay-actions{ display:flex; gap:.6rem; justify-content:flex-end; }
    }
  /* Evita que el símbolo $ se separe del monto */
  .price-nowrap{display:inline-flex;align-items:baseline;gap:.25rem;white-space:nowrap}
  /* Header top reset */
  body{margin-top:0}
  header.brand-bg{margin-top:0}
</style>
</head>
<body>
<header class="py-3 brand-bg text-white">
  <div class="container d-flex align-items-center gap-3">
    <img src="<?=$logo?>" alt="INSAT" class="brand-logo">
    <div>
      <div class="fw-semibold">Portal de pagos</div>
      <div class="small opacity-75">Consultá y pagá tus facturas</div>
    </div>
  </div>
</header>
<main class="container my-4 flex-grow-1">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="card card-hero p-4 p-md-5">
        <?php if (!$clienteNombre): ?>
        <?php if (!$clienteNombre): ?><?php if (!$hasResults): ?><?php if (!$hasIdent): ?><h1 class="h4 mb-3">Buscá tu cuenta</h1>
        <p class="text-muted mb-4">Ingresá tu <b>DNI</b>, <b>CUIT</b> o <b>Número de cliente</b> para ver tus pagos pendientes.</p><?php endif; ?><?php endif; ?><?php endif; ?>
        <?php endif; ?>
        <?php if (!($term !== '' && (($data['recurrente']&&count($data['recurrente'])>0) || ($data['puntual']&&count($data['puntual'])>0)))): ?>
<form class="row g-2" method="get" action="">
          <div class="col-12 col-md-9">
            <input type="text" name="id" class="form-control form-control-lg" placeholder="Ej: 20301234567 o 80307142119314" value="<?=h($term)?>" required>
          </div>
          <div class="col-12 col-md-3 d-grid">
            <button class="btn btn-brand btn-lg text-white">Consultar</button>
          </div>
        </form>
<?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger mt-4"><?=h($error)?></div>
        <?php endif; ?>
        <?php if ($hasIdent): ?>
          <?php if ($clienteNombre): ?>
            <div class="mb-3">
              <div class="fs-5 fw-semibold"><?=saludo_horario()?>, <?=h($clienteNombre)?>.</div>
              <div class="text-muted">Estos son tus pagos pendientes:</div>
            </div>
          <?php endif; ?>
          <?php if (!$data['recurrente'] && !$data['puntual']): ?>
            <div class="alert alert-info mt-4 mb-4">
              No se encontraron pagos pendientes. Para verificar tu saldo ingresá en <a href="https://autogestion.insat.com.ar/" class="alert-link" target="_blank" rel="noopener">Autogestión</a>.
            </div>
          <?php endif; ?>
          <?php if ($data['recurrente']): ?>
            <h2 class="h5 mt-2 mb-3 section-title">Pagos recurrentes</h2>
            <div class="row g-3 row-cols-1 row-cols-md-2">
              <?php foreach ($data['recurrente'] as $f):
                $titulo = trim((string)($f['origen'] ?? ''));
                if ($titulo === '') { $titulo = 'Abono mensual'; }
                $sub = trim((string)($f['periodo'] ?? ''));
                $venc = $f['vencimiento_1'] ?? null;
                if (($venc === null || $venc === '' || $venc === '0000-00-00') && (isset($f['tipo']) && $f['tipo']==='puntual')) {
                  $venc = date('Y-m-d');
                }
                $importe = number_format((float)($f['saldo_vencido'] ?? 0),2,',','.');
              ?>
              <div class="col">
                <div class="p-3 pay-card h-100">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="icon"><i class="bi bi-receipt"></i></div>
                      <div>
                        <div class="fw-semibold"><?=h($titulo)?></div>
                        <div class="text-muted small"><?=h($sub)?></div>
                      </div>
                    </div>
                    <div><?=venc_badge($venc)?></div>
                  </div>
                  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="fs-5 fw-bold mb-1 mb-md-0 price-nowrap">$ <?=$importe?></div>
                    <div class="pay-actions">
                      <a class="btn btn-mp" title="Pagar con saldo de Mercado Pago" href="payments/mercadopago.php?uuid=<?=h($f['uuid'])?>">
                        <i class="bi bi-wallet2"></i> <span>Pagar</span>
                      </a>
</div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($data['puntual']): ?>
            <h2 class="h5 mt-4 mb-3 section-title">Pagos puntuales</h2>
            <div class="row g-3 row-cols-1 row-cols-md-2">
              <?php foreach ($data['puntual'] as $f):
                $titulo = trim((string)($f['origen'] ?? ''));
                if ($titulo === '') { $titulo = 'Concepto puntual'; }
                $sub = trim((string)($f['periodo'] ?? ''));
                $venc = $f['vencimiento_1'] ?? null;
                if (($venc === null || $venc === '' || $venc === '0000-00-00') && (isset($f['tipo']) && $f['tipo']==='puntual')) {
                  $venc = date('Y-m-d');
                }
                $importe = number_format((float)($f['saldo_vencido'] ?? 0),2,',','.');
              ?>
              <div class="col">
                <div class="p-3 pay-card h-100">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="icon"><i class="bi bi-bag-check"></i></div>
                      <div>
                        <div class="fw-semibold"><?=h($titulo)?></div>
                        <div class="text-muted small"><?=h($sub)?></div>
                      </div>
                    </div>
                    <div><?=venc_badge($venc)?></div>
                  </div>
                  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="fs-5 fw-bold mb-1 mb-md-0 price-nowrap">$ <?=$importe?></div>
                    <div class="pay-actions">
                      <a class="btn btn-mp" title="Pagar con saldo de Mercado Pago" href="payments/mercadopago.php?uuid=<?=h($f['uuid'])?>">
                        <i class="bi bi-wallet2"></i> <span>Pagar</span>
                      </a>
</div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<!-- Seguridad y validación -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<section class="py-5" style="background:#fbfaff">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="h4 mb-2">Pagá con seguridad</h2>
      <p class="text-muted mb-0">Este es el <strong>sitio oficial</strong> de pagos de Insat. Te contamos cómo verificarlo y pagar con total tranquilidad.</p>
    </div>
    <div class="row g-3 g-lg-4">
      <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                 style="width:48px;height:48px;background:#efe7ff;color:#6d28d9">
              <i class="bi bi-shield-check fs-4"></i>
            </div>
            <h3 class="h6 mb-2">Sitio oficial</h3>
            <p class="text-muted mb-3">La dirección debe ser <strong>pagos.insat.com.ar</strong> y el candadito de seguridad visible.</p>
            <div class="mt-auto small text-muted">Tip: evitá entrar desde enlaces raros o acortadores.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                 style="width:48px;height:48px;background:#e7f7ef;color:#047857">
              <i class="bi bi-lock fs-4"></i>
            </div>
            <h3 class="h6 mb-2">Conexión segura (HTTPS)</h3>
            <p class="text-muted mb-3">El candado y el prefijo <strong>https://</strong> significan que tu conexión está cifrada.</p>
            <div class="mt-auto small text-muted">Nunca ingreses datos si ves alertas de “sitio no seguro”.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                 style="width:48px;height:48px;background:#fff4e6;color:#b45309">
              <i class="bi bi-credit-card fs-4"></i>
            </div>
            <h3 class="h6 mb-2">Medios de pago</h3>
            <p class="text-muted mb-3">Procesamos con plataformas reconocidas (Saldo MP y Tarjeta). Tus datos de tarjeta no quedan guardados en Insat.</p>
            <div class="mt-auto small text-muted">Completá los datos de pago sólo dentro del checkout seguro.</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px">
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                 style="width:48px;height:48px;background:#ffe7ed;color:#be123c">
              <i class="bi bi-exclamation-triangle fs-4"></i>
            </div>
            <h3 class="h6 mb-2">Prevención de fraudes</h3>
            <ul class="text-muted ps-3 mb-3" style="list-style:disc">
              <li>No compartas códigos ni claves por WhatsApp.</li>
              <li>Desconfiá de premios “demasiado buenos”.</li>
              <li>Insat <strong>no</strong> pide depósitos por CBU desconocidos.</li>
            </ul>
            <div class="mt-auto small text-muted">Ante dudas, escribinos al soporte oficial de Insat.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<footer class="footer py-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-6">© <?=date('Y')?> INSAT. Todos los derechos reservados.</div>
      <div class="col-12 col-md-6 admin-wrap d-flex justify-content-md-end mt-2 mt-md-0">
        <a class="btn btn-outline-light btn-sm" href="/admin/"><i class="bi bi-gear"></i> Administración</a>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>