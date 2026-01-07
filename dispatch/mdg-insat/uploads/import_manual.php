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

$mensaje = "";
$tipo_mensaje = "info"; // info o error

// Configuración de rutas
$folder = "uploads/";
$archivos_config = [
    'clientes' => $folder . "clientes.csv",
    'conexiones' => $folder . "conexiones.csv"
];

// Función para limpiar montos (quitar puntos de miles y cambiar coma por punto)
function limpiarMonto($valor) {
    if (empty($valor)) return 0;
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return (float)$valor;
}

// Función para formatear fechas DD-MM-YYYY a YYYY-MM-DD
function formatearFecha($fecha) {
    if (empty($fecha) || strlen($fecha) < 8) return null;
    $partes = explode('-', $fecha);
    if (count($partes) == 3) {
        return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
    }
    return null;
}

// Función para separar ID y Nombre (Ej: "1107 ANTENA" -> [1107, "ANTENA"])
function separarIdNombre($cadena) {
    if (preg_match('/^(\d+)\s+(.*)$/', trim($cadena), $matches)) {
        return ['id' => (int)$matches[1], 'nombre' => trim($matches[2])];
    }
    return ['id' => 0, 'nombre' => $cadena];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_tipo'])) {
    $tipo = $_POST['procesar_tipo'];
    $path = $archivos_config[$tipo];

    if (!file_exists($path)) {
        $mensaje = "Error: El archivo no se encuentra en la carpeta uploads. Asegúrate de que se llame '" . basename($path) . "'.";
        $tipo_mensaje = "error";
    } else {
        if (($handle = fopen($path, "r")) !== FALSE) {
            // Omitir cabecera
            fgetcsv($handle, 1000, ";");
            $procesados = 0;

            if ($tipo == 'clientes') {
                /** LÓGICA CLIENTES **/
                while (($data = fgetcsv($handle, 2000, ";")) !== FALSE) {
                    if (count($data) < 10) continue;
                    $sql = "INSERT INTO mdg_clientes (IdCliente, CodigoCliente, Nombre, Estado, Saldo, FechaCX, FechaDX) 
                            VALUES (?, ?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            Nombre=VALUES(Nombre), Estado=VALUES(Estado), Saldo=VALUES(Saldo), 
                            FechaCX=VALUES(FechaCX), FechaDX=VALUES(FechaDX)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("isssdss", 
                        $data[0], $data[1], $data[2], $data[3], 
                        limpiarMonto($data[8]), formatearFecha($data[38]), formatearFecha($data[39])
                    );
                    $stmt->execute();
                    $procesados++;
                }
            } else {
                /** LÓGICA CONEXIONES **/
                $conexion->query("UPDATE mdg_conexiones SET flag_actualizacion = 0");

                while (($data = fgetcsv($handle, 3000, ";")) !== FALSE) {
                    if (count($data) < 10) continue;
                    
                    $prodParts = separarIdNombre($data[9]);
                    $planParts = separarIdNombre($data[14]);

                    $sql = "INSERT INTO mdg_conexiones (
                        IdCliente, ClienteNombre, ClienteTipo, ClienteVencimiento, IdConexion, EstadoConexion,
                        Latitud, Longitud, Nodo, IdProducto, Producto, Cantidad, FechaInstalacion, FechaContrato,
                        Sistema, IdPlan, Plan, PlanTipo, Valor, ValorBonificado, flag_actualizacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE 
                        ClienteNombre=VALUES(ClienteNombre), EstadoConexion=VALUES(EstadoConexion), 
                        Latitud=VALUES(Latitud), Longitud=VALUES(Longitud), Valor=VALUES(Valor), flag_actualizacion=1";
                    
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("isssisddsisississsdd", 
                        $data[0], $data[1], $data[2], $data[3], $data[4], $data[5],
                        limpiarMonto($data[6]), limpiarMonto($data[7]), $data[8], $prodParts['id'], $prodParts['nombre'],
                        $data[10], formatearFecha($data[11]), formatearFecha($data[12]), $data[13], 
                        $planParts['id'], $planParts['nombre'], $data[15], limpiarMonto($data[16]), limpiarMonto($data[17])
                    );
                    $stmt->execute();
                    $procesados++;
                }
            }
            fclose($handle);
            $mensaje = "Proceso finalizado. Se actualizaron $procesados registros desde el archivo " . basename($path) . ".";
            $tipo_mensaje = "info";
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
        .info-box code { background: #e2e8f0; padding: 2px 4px; border-radius: 4px; font-weight: bold; }
        .import-option { border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; }
        .import-option:hover { border-color: #2563eb; background: #f0f7ff; }
        .option-text h4 { margin: 0; color: #1e293b; }
        .option-text p { margin: 5px 0 0 0; font-size: 0.8rem; color: #64748b; }
        .btn { padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert.info { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <i data-lucide="folder-sync"></i>
        <h2>Importación desde Servidor</h2>
    </div>

    <div class="info-box">
        <i data-lucide="info" size="14" style="display:inline; margin-right:5px;"></i>
        Sube tus archivos por FTP a la carpeta <code>uploads/</code> con estos nombres exactos:
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><code>clientes.csv</code> (Maestro/Morosos)</li>
            <li><code>conexiones.csv</code> (Planes/Detalle)</li>
        </ul>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="import-option">
            <div class="option-text">
                <h4>Actualizar Clientes</h4>
                <p>Archivo: <code>uploads/clientes.csv</code></p>
            </div>
            <button type="submit" name="procesar_tipo" value="clientes" class="btn">Procesar</button>
        </div>

        <div class="import-option">
            <div class="option-text">
                <h4>Actualizar Conexiones</h4>
                <p>Archivo: <code>uploads/conexiones.csv</code></p>
            </div>
            <button type="submit" name="procesar_tipo" value="conexiones" class="btn">Procesar</button>
        </div>
    </form>

    <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
</div>

<script>lucide.createIcons();</script>
</body>
</html>