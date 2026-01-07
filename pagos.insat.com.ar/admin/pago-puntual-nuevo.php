<?php
declare(strict_types=1);
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../includes/conn.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear pago puntual — Insat Pagos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .page-wrap{min-height:100vh;background:#faf7ff}
    .card-sh{border:0;border-radius:16px;box-shadow:0 10px 24px rgba(37,0,90,.08)}
    .required:after{content:" *";color:#c00;font-weight:700}
    .help{font-size:.9rem;color:#6c6c6c}
  </style>
</head>
<body>
  <div class="page-wrap d-flex align-items-start py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card card-sh">
            <div class="card-body p-4 p-lg-5">
              <h1 class="h4 mb-1">Crear pago puntual</h1>
              <p class="text-muted mb-4">El vencimiento se asigna automáticamente para <strong>hoy</strong>.</p>

              <form action="pago-puntual-crear.php" method="post" novalidate>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required">Identificador del cliente</label>
                    <input type="text" name="identificador" class="form-control" placeholder="DNI, CUIT o Nº de cliente" maxlength="32" required>
                    <div class="help">Debe existir en tu base para que el cliente lo encuentre al consultar.</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Nombre</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre y apellido del cliente" maxlength="120" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label required">Concepto</label>
                    <input type="text" name="concepto" class="form-control" placeholder="Ej: Instalación adicional" maxlength="120" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label required">Monto</label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="text" name="monto" class="form-control" placeholder="Ej: 12500,00" inputmode="decimal" required>
                    </div>
                    <div class="help">Usá coma o punto para decimales (se normaliza).</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Email (opcional)</label>
                    <input type="email" name="email" class="form-control" placeholder="cliente@ejemplo.com" maxlength="140">
                  </div>

                  <div class="col-12">
                    <label class="form-label">Notas internas (opcional)</label>
                    <textarea name="notas" class="form-control" rows="3" maxlength="500" placeholder="Detalle que ve el equipo (el cliente no lo ve)"></textarea>
                  </div>
                </div>

                <hr class="my-4">

                <div class="d-flex align-items-center gap-3">
                  <button type="submit" class="btn btn-primary px-4">Crear pago</button>
                  <span class="text-muted">Se generará con estado <strong>PENDIENTE</strong> y etiqueta <strong>Vence hoy</strong>.</span>
                </div>
              </form>

            </div>
          </div>

          <p class="text-center mt-4 text-muted">© <?=date('Y')?> Insat — Portal de pagos</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
