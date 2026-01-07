<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Consulta para obtener todas las columnas y todos los registros
$sql = "SELECT * FROM mdg_conexiones ORDER BY IdConexion ASC";
$res = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor Completo de Conexiones</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 20px; color: #1e293b; }
        .container-fluid { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        
        /* Contenedor con scroll horizontal para muchas columnas */
        .table-wrapper { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.75rem; min-width: 2500px; }
        th { background: #f8fafc; padding: 12px 8px; text-align: left; border-bottom: 2px solid #e2e8f0; position: sticky; top: 0; }
        td { padding: 10px 8px; border-bottom: 1px solid #f1f5f9; }
        tr:hover { background: #f8fafc; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.7rem; }
        .bg-null { background: #fee2e2; color: #991b1b; } /* Resaltar celdas vacías */
        .back-link { margin-top: 20px; display: inline-block; text-decoration: none; color: #2563eb; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="header">
        <i data-lucide="database"></i>
        <h2>Explorador de Datos: mdg_conexiones</h2>
    </div>

    <p>Mostrando <b><?php echo $res->num_rows; ?></b> registros encontrados en la base de datos.</p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>IdCliente</th>
                    <th>ClienteNombre</th>
                    <th>Tipo</th>
                    <th>Venc.</th>
                    <th>IdCnx</th>
                    <th>Estado</th>
                    <th>Lat</th>
                    <th>Long</th>
                    <th>Nodo</th>
                    <th>Producto</th>
                    <th>Cant</th>
                    <th>Instalación</th>
                    <th>Contrato</th>
                    <th>Sistema</th>
                    <th>IdPlan</th>
                    <th>Plan</th>
                    <th>Valor</th>
                    <th>Valor Bonif.</th>
                    <th>Promo</th>
                    <th>Cuota</th>
                    <th>Cant Cuotas</th>
                    <th>Promo Valor</th>
                    <th>%</th>
                    <th>Prod Original</th>
                    <th>Plan Original</th>
                    <th>Ajuste</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['IdCliente']; ?></td>
                    <td><b><?php echo $row['ClienteNombre']; ?></b></td>
                    <td><?php echo $row['ClienteTipo']; ?></td>
                    <td><?php echo $row['ClienteVencimiento']; ?></td>
                    <td><?php echo $row['IdConexion']; ?></td>
                    <td><?php echo $row['EstadoConexion']; ?></td>
                    <td><?php echo $row['Latitud']; ?></td>
                    <td><?php echo $row['Longitud']; ?></td>
                    <td><?php echo $row['Nodo']; ?></td>
                    <td><?php echo $row['Producto']; ?></td>
                    <td><?php echo $row['Cantidad']; ?></td>
                    <td><?php echo $row['FechaInstalacion']; ?></td>
                    <td><?php echo $row['FechaContrato']; ?></td>
                    <td><?php echo $row['Sistema']; ?></td>
                    <td><?php echo $row['IdPlan']; ?></td>
                    <td><?php echo $row['Plan']; ?></td>
                    <td>$<?php echo number_format($row['Valor'], 2); ?></td>
                    <td>$<?php echo number_format($row['ValorBonificado'], 2); ?></td>
                    
                    <td class="<?php echo is_null($row['Promo']) || $row['Promo'] == '' ? 'bg-null' : ''; ?>">
                        <?php echo $row['Promo'] ?? '<i>NULL</i>'; ?>
                    </td>
                    
                    <td><?php echo $row['PromoCuota']; ?></td>
                    <td><?php echo $row['PromoCantidadCuotas']; ?></td>
                    <td>$<?php echo number_format($row['PromoValor'], 2); ?></td>
                    <td><?php echo $row['PromoPorcentaje']; ?>%</td>
                    <td><?php echo $row['ProductoOriginal']; ?></td>
                    <td><?php echo $row['PlanOriginal']; ?></td>
                    <td><?php echo $row['Ajuste']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="dashboard.php" class="back-link"><i data-lucide="arrow-left" size="14"></i> Volver al Dashboard</a>
</div>

<script>lucide.createIcons();</script>
</body>
</html>