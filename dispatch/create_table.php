<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load database configuration from config.php
require_once 'config.php'; // Make sure config.php defines DB_HOST, DB_USER, DB_PASS, DB_NAME

// Conexión a la base de datos MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); // Uses constants from config.php

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos '" . DB_NAME . "': " . $conn->connect_error);
}

echo "✅ Conectado a la base de datos '" . DB_NAME . "' exitosamente.<br><br>";

// SQL para eliminar la tabla (si existe)
$sqlDropTable = "DROP TABLE IF EXISTS `consumos_detallados_aplicaciones`;";

// SQL para crear la tabla y sus índices/restricciones
// Este SQL se toma directamente de tu volcado de phpMyAdmin,
// asegurando que 'id' sea AUTO_INCREMENT PRIMARY KEY y se agregue el UNIQUE KEY.
$sqlCreateTable = "
CREATE TABLE `consumos_detallados_aplicaciones` (
  `id` int NOT NULL,
  `consumo_diario_id` int DEFAULT NULL,
  `nombre_aplicacion` varchar(255) NOT NULL,
  `consumo_gb` decimal(10,8) NOT NULL,
  `color` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- CHANGED HERE

ALTER TABLE `consumos_detallados_aplicaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `consumo_diario_id` (`consumo_diario_id`,`nombre_aplicacion`);

ALTER TABLE `consumos_detallados_aplicaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1; -- Start AUTO_INCREMENT from 1

ALTER TABLE `consumos_detallados_aplicaciones`
  ADD CONSTRAINT `consumos_detallados_aplicaciones_ibfk_1` FOREIGN KEY (`consumo_diario_id`) REFERENCES `consumos_diarios` (`id`) ON DELETE CASCADE;
";

// SQL para insertar datos de ejemplo (opcional, si quieres cargar los datos de tu dump)
// Comenta o elimina esta sección si solo quieres la tabla vacía
$sqlInsertData = "
INSERT INTO `consumos_detallados_aplicaciones` (`id`, `consumo_diario_id`, `nombre_aplicacion`, `consumo_gb`, `color`) VALUES
(1, 10012, 'Microsoft', 0.23928975, '#1A90D0'),
(2, 10012, 'Windows Updates', 0.19586566, '#1A90D0'),
(3, 10012, 'WhatsApp TLS', 0.18963728, '#1A90D0'),
(4, 10012, 'Quic Ietf (Youtube/Google Apps)', 0.15187671, '#095FA0');
";


// 1. Eliminar la tabla existente
echo "Intentando eliminar la tabla 'consumos_detallados_aplicaciones' si existe...<br>";
if ($conn->query($sqlDropTable) === TRUE) {
    echo "✅ Tabla 'consumos_detallados_aplicaciones' eliminada exitosamente (si existía).<br><br>";
} else {
    echo "❌ Error al eliminar la tabla 'consumos_detallados_aplicaciones': " . $conn->error . "<br><br>";
}

// 2. Crear la tabla con la nueva estructura
echo "Intentando crear la tabla 'consumos_detallados_aplicaciones' con la nueva estructura...<br>";
if ($conn->multi_query($sqlCreateTable)) {
    do {
        // Almacenar el primer resultado para liberar memoria
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result()); // Mover al siguiente resultado
    echo "✅ Tabla 'consumos_detallados_aplicaciones' creada exitosamente.<br><br>";
} else {
    echo "❌ Error al crear la tabla 'consumos_detallados_aplicaciones': " . $conn->error . "<br><br>";
}

// 3. Insertar datos de ejemplo (opcional)
// Solo ejecuta esto si quieres que se carguen los datos de ejemplo del dump
echo "Intentando insertar datos de ejemplo en 'consumos_detallados_aplicaciones' (opcional)...<br>";
if ($conn->multi_query($sqlInsertData)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "✅ Datos de ejemplo insertados exitosamente.<br><br>";
} else {
    echo "❌ Error al insertar datos de ejemplo: " . $conn->error . "<br><br>";
}


// Cerrar la conexión a la base de datos
$conn->close();

echo "Script finalizado.";
?>