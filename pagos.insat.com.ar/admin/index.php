<?php declare(strict_types=1);
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../lib/functions.php';
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Portal de pagos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
<div class="p-3 border-bottom bg-light d-flex flex-wrap gap-2 justify-content-between align-items-center"><div class="fw-semibold">Accesos rápidos</div><div class="d-flex gap-2"><a class="btn btn-primary btn-sm" href="pago-puntual-nuevo.php"><i class="bi bi-plus-circle"></i> Crear pago puntual</a><a class="btn btn-outline-primary btn-sm" href="upload.php"><i class="bi bi-upload"></i> Subir pagos puntuales (CSV)</a></div></div>
        <div class="card-body">
          <h1 class="h5 mb-3">Actualizar saldos (CSV)</h1>
          <p class="text-muted">Subí el archivo CSV exportado por el sistema (<b>separador ;</b> y codificación Windows/Latin1).</p>
          <form class="row g-3" action="upload.php" method="post" enctype="multipart/form-data">
            <div class="col-12">
              <input class="form-control" type="file" name="csv" accept=".csv,text/csv" required>
            </div>
            <div class="col-12 d-grid">
              <button class="btn btn-primary">Subir e importar</button>
            </div>
          </form>
          <hr class="my-4">
          <p class="small text-muted">Formato esperado: columnas <code>Id Cliente; Codigo Cliente; Apellido y Nombre / Razon Social; Domicilio; Nodo; Id Factura; Fecha Emision; Periodo; Vencimiento 1; Vencimiento 2; Vencimiento 3; Comprobante; Saldo Vencido; Origen</code></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
