<?php
// Mostrar errores si hay problemas
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar buffer de salida para evitar conflictos con header()
ob_start();

// Ruta del log
$logFile = __DIR__ . '/scraper.log';

// Obtener parámetros de la URL
$urlIndex = isset($_GET['url_index']) ? (int)$_GET['url_index'] : 0;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Escribir en log que se ingresó al archivo
$timestamp = date('Y-m-d H:i:s');
$mensajeLog = "[$timestamp] 📥 Entrando a scraper_reinicio_auto.php con url_index=$urlIndex, pagina=$pagina, fecha=$fecha\n";
file_put_contents($logFile, $mensajeLog, FILE_APPEND);

// Redireccionar a scraper.php con los parámetros recibidos
$redirectUrl = "scraper.php?url_index=$urlIndex&pagina=$pagina&fecha=$fecha";
header("Location: $redirectUrl");
exit;
?>