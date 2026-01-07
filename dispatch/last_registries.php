<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Definición de constantes de conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para obtener los últimos 50 registros, ordenados por ID (o fecha_consumo)
$sql = "SELECT * FROM consumos_diarios ORDER BY id DESC LIMIT 3250"; // O puedes usar ORDER BY fecha_consumo DESC

// Ejecutar la consulta
$result = $conn->query($sql);

// Verificar si la consulta tiene resultados
if ($result->num_rows > 0) {
    // Mostrar los registros
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Cliente ID: " . $row['cliente_id'] . " - ACT: " . $row['act'] ." - Documento: " . $row['documento'] . " - Fecha: " . $row['fecha_consumo'] . " - Total GB: " . $row['total_gb'] . "<br>";
    }
} else {
    echo "No se encontraron registros.";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
