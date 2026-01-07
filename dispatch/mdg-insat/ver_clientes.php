<?php
session_start();
require 'db.php';

// Protección de sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lógica de búsqueda avanzada
$where = "";
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $b = $conexion->real_escape_string($_GET['buscar']);
    $where = "WHERE Nombre LIKE '%$b%' OR CodigoCliente LIKE '%$b%' OR NroDocumento LIKE '%$b%' OR IdCliente LIKE '%$b%'";
}

// Consulta de todas las columnas
$sql = "SELECT * FROM mdg_clientes $where ORDER BY FechaActualizacion DESC LIMIT 1000";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - Vista Completa</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; color: #333; }
        .wrapper { overflow-x: auto; border: 1px solid #ccc; } /* Scroll horizontal */
        table { border-collapse: collapse; width: 100%; min-width: 2500px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; white-space: nowrap; }
        th { background-color: #2c3e50; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .saldo { font-weight: bold; color: #c0392b; text-align: right; }
        
        .header-tools { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 4px; background: #3498db; color: white; }
    </style>
</head>
<body>

    <div class="header-tools">
        <h2>Base de Datos de Clientes</h2>
        <div>
            <a href="import_manual.php" class="btn" style="background:#27ae60;">Importar CSV</a>
            <a href="logout.php" class="btn" style="background:#e74c3c;">Cerrar Sesión</a>
        </div>
    </div>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="buscar" placeholder="Buscar por Nombre, Doc, Código o ID..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
            <button type="submit" class="btn">Buscar</button>
            <?php if(isset($_GET['buscar'])): ?>
                <a href="list_clientes.php" style="font-size: 12px; margin-left:10px;">Limpiar filtros</a>
            <?php endif; ?>
        </form>
    </div>

    <p><small>Mostrando los últimos 1000 registros actualizados.</small></p>

    <div class="wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Tipo Cliente</th>
                    <th>Saldo</th>
                    <th>Facturas</th>
                    <th>Tipo Documento</th>
                    <th>Documento</th>
                    <th>Teléfono 1</th>
                    <th>Teléfono 2</th>
                    <th>Teléfono 3</th>
                    <th>Email 1</th>
                    <th>Email 2</th>
                    <th>Email 3</th>
                    <th>Forma Pago</th>
                    <th>Convenio</th>
                    <th>Fecha CX</th>
                    <th>Fecha DX</th>
                    <th>Fecha Susp.</th>
                    <th>Empresa Venta</th>
                    <th>Agencia Venta</th>
                    <th>Actualizado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $row['IdCliente']; ?></strong></td>
                    <td><?php echo $row['CodigoCliente']; ?></td>
                    <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                    <td>
                        <span class="badge <?php echo $row['Estado'] == 'Conectado' ? 'badge-success' : ''; ?>">
                            <?php echo $row['Estado']; ?>
                        </span>
                    </td>
                    <td><?php echo $row['TipoCliente']; ?></td>
                    <td class="saldo">$<?php echo number_format($row['Saldo'], 2, ',', '.'); ?></td>
                    <td style="text-align:center;"><?php echo $row['CantFacturas']; ?></td>
                    <td><?php echo $row['TipoDocumento']; ?></td>
                    <td><?php echo $row['NroDocumento']; ?></td>
                    <td><?php echo "({$row['AreaTel_1']}) {$row['Tel_1']}"; ?></td>
                    <td><?php echo "({$row['AreaTel_2']}) {$row['Tel_2']}"; ?></td>
                    <td><?php echo "({$row['AreaTel_3']}) {$row['Tel_3']}"; ?></td>
                    <td><?php echo $row['Email_1']; ?></td>
                    <td><?php echo $row['Email_2']; ?></td>
                    <td><?php echo $row['Email_3']; ?></td>
                    <td><?php echo $row['FormaPago']; ?></td>
                    <td><?php echo $row['ConvenioPago']; ?></td>
                    <td><?php echo $row['FechaCX']; ?></td>
                    <td><?php echo $row['FechaDX']; ?></td>
                    <td><?php echo $row['FechaSuspendido']; ?></td>
                    <td><?php echo $row['EmpresaVenta']; ?></td>
                    <td><?php echo $row['AgenciaVenta']; ?></td>
                    <td><small><?php echo $row['FechaActualizacion']; ?></small></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>