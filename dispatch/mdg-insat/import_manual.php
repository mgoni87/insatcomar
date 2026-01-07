<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mensaje = "";
$tipo_mensaje = "info"; 

$folder = "uploads/";
$archivos_config = [
    'clientes' => $folder . "clientes.csv",
    'conexiones' => $folder . "conexiones.csv"
];

function limpiarMonto($valor) {
    if (empty($valor)) return 0;
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return (float)$valor;
}

function limpiarCoordenada($valor) {
    if (empty($valor)) return 0;
    $valor = str_replace(',', '.', $valor);
    return (float)$valor;
}

function formatearFecha($fecha) {
    if (empty($fecha) || strlen($fecha) < 8) return null;
    $partes = explode('-', $fecha);
    if (count($partes) == 3) {
        return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
    }
    return null;
}

function separarIdNombre($cadena) {
    $cadena = trim($cadena);
    if (preg_match('/^(\d+)\s+(.*)$/', $cadena, $matches)) {
        return ['id' => (int)$matches[1], 'nombre' => trim($matches[2])];
    }
    return ['id' => 0, 'nombre' => $cadena];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_tipo'])) {
    $tipo = $_POST['procesar_tipo'];
    $path = $archivos_config[$tipo];

    if (!file_exists($path)) {
        $mensaje = "Error: El archivo no se encuentra en la carpeta uploads.";
        $tipo_mensaje = "error";
    } else {
        try {
            if (($handle = fopen($path, "r")) !== FALSE) {
                fgetcsv($handle, 3000, ";", '"', "");
                $procesados = 0;

                if ($tipo == 'clientes') {
                    $sql = "INSERT INTO mdg_clientes (IdCliente, CodigoCliente, Nombre, Estado, Saldo, FechaCX, FechaDX) 
                            VALUES (?, ?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE Nombre=VALUES(Nombre), Estado=VALUES(Estado), Saldo=VALUES(Saldo)";
                    $stmt = $conexion->prepare($sql);
                    while (($data = fgetcsv($handle, 2000, ";", '"', "")) !== FALSE) {
                        if (count($data) < 5) continue;
                        $idCliente = (int)$data[0]; $codCli = $data[1]; $nombre = $data[2]; $estado = $data[3];
                        $saldo = limpiarMonto($data[8]); $fCX = formatearFecha($data[38] ?? ''); $fDX = formatearFecha($data[39] ?? '');
                        $stmt->bind_param("isssdss", $idCliente, $codCli, $nombre, $estado, $saldo, $fCX, $fDX);
                        $stmt->execute();
                        $procesados++;
                    }
                } else {
                    $conexion->query("UPDATE mdg_conexiones SET flag_actualizacion = 0");

                    // SQL ACTUALIZADO PARA PROMOCIONES
                    $sql = "INSERT INTO mdg_conexiones (
                        IdCliente, ClienteNombre, ClienteTipo, ClienteVencimiento, IdConexion, EstadoConexion,
                        Latitud, Longitud, Nodo, IdProducto, Producto, Cantidad, FechaInstalacion, FechaContrato,
                        Sistema, IdPlan, Plan, PlanTipo, Valor, ValorBonificado, 
                        FechaAltaPromo, Promo, PromoCuota, PromoCantidadCuotas, PromoValor, PromoPorcentaje, flag_actualizacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE EstadoConexion=VALUES(EstadoConexion), Valor=VALUES(Valor), Promo=VALUES(Promo), flag_actualizacion=1";
                    
                    $stmt = $conexion->prepare($sql);

                    while (($data = fgetcsv($handle, 3000, ";", '"', "")) !== FALSE) {
                        if (count($data) < 17) continue;
                        
                        // Función interna para limpiar cada celda de caracteres extraños (ANSI a UTF-8)
                        foreach ($data as $key => $valor) {
                            $data[$key] = mb_convert_encoding($valor, 'UTF-8', 'ISO-8859-1');
                        }

                        $prodArr = separarIdNombre($data[9] ?? '');
                        $planArr = separarIdNombre($data[14] ?? '');
                        
                        $idCliente = (int)$data[0];
                        $cliNom = $data[1]; // Ahora ya viene convertido a UTF-8
                        $cliTipo = $data[2];
                        $cliVen = $data[3];
                        $idCon = (int)$data[4];
                        $estCon = $data[5];
                        $lat = limpiarCoordenada($data[6] ?? 0);
                        $lon = limpiarCoordenada($data[7] ?? 0);
                        $nodo = $data[8];
                        $idProd = $prodArr['id'];
                        $nomProd = $prodArr['nombre'];
                        $cant = (int)$data[10];
                        $fInst = formatearFecha($data[11] ?? '');
                        $fCont = formatearFecha($data[12] ?? '');
                        $sist = $data[13];
                        $idPlan = $planArr['id'];
                        $nomPlan = $planArr['nombre'];
                        $plTipo = $data[15];
                        $val = limpiarMonto($data[16] ?? 0);
                        $valB = limpiarMonto($data[17] ?? 0);

                        // CAMPOS DE PROMOCIÓN
                        $fAltaP = formatearFecha($data[18] ?? '');
                        $promo = !empty($data[19]) ? $data[19] : null;
                        $pCuota = (int)($data[20] ?? 0);
                        $pCantC = (int)($data[21] ?? 0);
                        $pVal = limpiarMonto($data[22] ?? 0);
                        $pPje = limpiarMonto($data[23] ?? 0);

                        $stmt->bind_param("isssisddsisississsddssiidd", 
                            $idCliente, $cliNom, $cliTipo, $cliVen, $idCon, $estCon,
                            $lat, $lon, $nodo, $idProd, $nomProd, $cant,
                            $fInst, $fCont, $sist, $idPlan, $nomPlan, $plTipo,
                            $val, $valB, $fAltaP, $promo, $pCuota, $pCantC, $pVal, $pPje
                        );
                        $stmt->execute();
                        $procesados++;
                    }
                }
                fclose($handle);
                $mensaje = "Éxito: Se procesaron $procesados registros de " . $tipo;
            }
        } catch (Exception $e) {
            $mensaje = "Error crítico: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importador Local | Insatcom</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 40px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; color: #1e293b; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; color: #475569; }
        .import-option { border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert.info { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <i data-lucide="folder-sync"></i>
        <h2>Importación desde Servidor</h2>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="import-option">
            <div>
                <h4>Clientes (Morosos)</h4>
                <p style="font-size:0.8rem;color:#64748b;">Ruta: <code>uploads/clientes.csv</code></p>
            </div>
            <button type="submit" name="procesar_tipo" value="clientes" class="btn">Procesar</button>
        </div>

        <div class="import-option">
            <div>
                <h4>Conexiones (Planes)</h4>
                <p style="font-size:0.8rem;color:#64748b;">Ruta: <code>uploads/conexiones.csv</code></p>
            </div>
            <button type="submit" name="procesar_tipo" value="conexiones" class="btn">Procesar</button>
        </div>
    </form>
    <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
</div>
<script>lucide.createIcons();</script>
</body>
</html>