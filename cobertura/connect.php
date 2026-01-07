<?php
$hostname = 'localhost'; // Especifica el host, es decir, 'localhost'
$user = 'tucablec_usr'; // Especifica el nombre de usuario
$pass = '89pg7C05WG@3'; // Especifica la contraseña
$dbase = 'tucablec_bd'; // Especifica el nombre de la base de datos

// Establecer la conexión usando MySQLi
$connection = new mysqli($hostname, $user, $pass, $dbase);

// Verificar si hay errores de conexión
if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
}
?>
