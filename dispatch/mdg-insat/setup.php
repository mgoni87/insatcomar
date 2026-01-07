<?php
require 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS mdg_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    if ($conexion->query($sql) === TRUE) {
        echo "Tabla 'mdg_users' creada o ya existente con éxito.";
    }
} catch (Exception $e) {
    echo "Error al crear la tabla: " . $e->getMessage();
}

$conexion->close();
?>