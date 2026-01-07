<?php
ini_set('display_errors', 1);   // Habilita la visualización de errores
error_reporting(E_ALL);          // Reporta todos los tipos de errores

$host = 'localhost';             // o IP del servidor
$user = 'insatcomar_usr';       // reemplaza con tu usuario
$pass = 'H~B(%rIjcGw-';         // reemplaza con tu contraseña
$dbName = 'insatcomar_dbctes';   // <--- ¡IMPORTANTE! Especifica la base de datos donde quieres probar.

// Conectar al servidor MySQL, ESPECIFICANDO LA BASE DE DATOS
$conn = new mysqli($host, $user, $pass, $dbName); // <-- Pasamos $dbName aquí

// Verificar la conexión
if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos '$dbName': " . $conn->connect_error);
}

echo "Conectado a la base de datos '$dbName' exitosamente.<br>";

// Intentar crear una tabla de prueba
$tableName = "prueba_creacion_tabla_" . rand(1000, 9999);
$sqlCreateTable = "CREATE TABLE `$tableName` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sqlCreateTable) === TRUE) {
    echo "✅ El usuario tiene permisos para crear tablas en la base de datos '$dbName'. Tabla '$tableName' creada.";
    // Opcional: eliminar la tabla si no la necesitas
    $conn->query("DROP TABLE `$tableName`");
    echo "<br>Tabla '$tableName' eliminada (si se creó).";
} else {
    echo "❌ No tienes permisos para crear tablas en la base de datos '$dbName'. Error: " . $conn->error;
}

$conn->close();
?>