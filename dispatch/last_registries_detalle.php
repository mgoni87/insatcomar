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

// Consulta SQL para obtener los últimos 50 registros de la nueva tabla
// Cambiamos la tabla a `consumos_detallados_aplicaciones`
// y los campos a mostrar según la estructura de esa tabla.
// Asumimos que quieres los últimos por `id` de forma descendente.
$sql = "SELECT * FROM consumos_detallados_aplicaciones ORDER BY id DESC LIMIT 50";

// Ejecutar la consulta
$result = $conn->query($sql);

// Verificar si la consulta tiene resultados
if ($result->num_rows > 0) {
    echo "<h3>Últimos 50 registros de 'consumos_detallados_aplicaciones':</h3>";
    // Mostrar los registros
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] .
             " - Consumo Diario ID: " . $row['consumo_diario_id'] .
             " - Aplicación: " . $row['nombre_aplicacion'] .
             " - Consumo GB: " . $row['consumo_gb'] .
             " - Color: " . ($row['color'] ?? 'N/A') . // Usamos el operador null coalescing para 'color' por si es NULL
             "<br>";
    }
} else {
    echo "No se encontraron registros en 'consumos_detallados_aplicaciones'.";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>