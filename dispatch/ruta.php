<?php
$directorioScript = __DIR__;
echo "El directorio del script es: " . $directorioScript;
// Ejemplo de salida: /var/www/html/tusitio

// Si quieres la ruta al script usando __DIR__:
$rutaAbsolutaScript = __DIR__ . '/scraper.php';
echo "La ruta absoluta al script es: " . $rutaAbsolutaScript;

?>