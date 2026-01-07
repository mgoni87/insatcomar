<?php declare(strict_types=1);
http_response_code(403);
header('Content-Type: text/html; charset=UTF-8');
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Acceso restringido</title>
<link rel="icon" href="https://insat.com.ar/img/favicon.png" type="image/png">
<style>
  :root{
    --brand:#7E3CF0;
    --brand-600:#6b2ee2;
    --bg:#f5f7fb;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0; background:var(--bg); color:#1f2142; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,'Helvetica Neue',Arial,'Noto Sans',sans-serif;
    display:flex; align-items:center; justify-content:center; padding:24px;
  }
  .card{
    width:min(700px, 92vw);
    background:#fff; border-radius:20px; box-shadow:0 10px 30px rgba(126,60,240,.15);
    padding:28px 28px;
  }
  .hdr{display:flex; align-items:center; gap:12px; margin-bottom:10px}
  .dot{width:14px; height:14px; border-radius:50%; background:var(--brand); box-shadow:0 0 0 4px rgba(126,60,240,.12)}
  h1{font-size:1.25rem; margin:0}
  p{margin:.25rem 0; color:#555}
  .muted{color:#6b7280; font-size:.95rem}
  .cta{margin-top:18px; display:flex; gap:10px; flex-wrap:wrap}
  .btn{appearance:none; text-decoration:none; padding:10px 16px; border-radius:12px; font-weight:600; display:inline-flex; align-items:center; gap:8px}
  .btn-primary{background:var(--brand); color:#fff; border:1px solid var(--brand)}
  .btn-outline{background:#fff; color:#1f2142; border:1px solid #e6e2fb}
  .small{font-size:.9rem}
</style>
</head>
<body>
  <div class="card" role="alert" aria-live="assertive">
    <div class="hdr">
      <span class="dot" aria-hidden="true"></span>
      <h1>Acceso restringido</h1>
    </div>
    <p>Por motivos de seguridad, esta sección es de uso interno.</p>
    <p class="muted">Si creés que esto es un error, verificá tu conexión y volvé a intentar. También podés regresar al inicio del portal.</p>
    <div class="cta">
      <a class="btn btn-primary" href="/index.php">Volver al inicio</a>
      <a class="btn btn-outline small" href="https://autogestion.insat.com.ar/" target="_blank" rel="noopener">Autogestión</a>
    </div>
    <p class="muted small">Código: 403 • Portal de pagos INSAT</p>
  </div>
</body>
</html>
