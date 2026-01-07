<?php
session_start();
require 'db.php';

// Protección de sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

if (isset($_POST['importar'])) {
    if ($_FILES['archivo_csv']['error'] == 0) {
        $filename = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($filename, "r");
        
        // Omitir la primera línea (cabeceras)
        fgetcsv($handle, 10000, ";");

        $contador = 0;

        // Preparar la consulta con todos los campos
        $sql = "INSERT INTO mdg_clientes 
                (IdCliente, CodigoCliente, Nombre, Estado, TipoCliente, AreaTel_1, Tel_1, AreaTel_2, Tel_2, AreaTel_3, Tel_3, Email_1, Email_2, Email_3, TipoDocumento, NroDocumento, FormaPago, ConvenioPago, FechaCX, FechaDX, FechaSuspendido, EmpresaVenta, AgenciaVenta, Saldo, CantFacturas) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                Nombre=VALUES(Nombre), Estado=VALUES(Estado), Saldo=VALUES(Saldo), CantFacturas=VALUES(CantFacturas), FechaActualizacion=NOW()";

        $stmt = $conexion->prepare($sql);

        while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
            // 1. Limpiar Saldo (ej: 69.525,10 -> 69525.10)
            $saldo = str_replace(['.', ','], ['', '.'], $data[8]);

            // 2. Formatear fechas (de DD-MM-YYYY a YYYY-MM-DD para MySQL)
            $fCX = !empty($data[38]) ? date('Y-m-d', strtotime($data[38])) : null;
            $fDX = !empty($data[39]) ? date('Y-m-d', strtotime($data[39])) : null;
            $fSusp = !empty($data[41]) ? date('Y-m-d', strtotime($data[41])) : null;

            // 3. Mapeo de variables según tus índices
            $stmt->bind_param("issssssssssssssissssssdii", 
                $data[0],  $data[1],  $data[2],  $data[3],  $data[10], // IdC, Cod, Nom, Est, TipoC
                $data[19], $data[20], $data[21], $data[22], $data[23], // Tels 1 y 2 y Area 3
                $data[24], $data[25], $data[27], $data[29], $data[31], // Tel3, Emails, TipoDoc
                $data[32], $data[33], $data[34], $fCX,      $fDX,      // NroDoc, Pago, Conv, Fechas
                $fSusp,    $data[17], $data[18], $saldo,    $data[5]   // FechaS, Empresa, Agencia, Saldo, CantFact
            );

            $stmt->execute();
            $contador++;
        }

        fclose($handle);
        $mensaje = "¡Éxito! Se procesaron $contador registros.";
    } else {
        $mensaje = "Error código: " . $_FILES['archivo_csv']['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Clientes</title>
</head>
<body>
    <nav>
        <a href="dashboard.php">Panel</a> | <a href="logout.php">Salir</a>
    </nav>
    <hr>
    <h2>Importar Clientes (CSV)</h2>
    <p><?php echo $mensaje; ?></p>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Selecciona el archivo .csv:</label>
        <input type="file" name="archivo_csv" accept=".csv" required>
        <br><br>
        <button type="submit" name="importar">Cargar Datos</button>
    </form>
</body>
</html>