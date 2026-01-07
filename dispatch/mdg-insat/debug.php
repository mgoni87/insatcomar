<?php
header('Content-Type: text/html; charset=utf-8');
require 'db.php';

echo "<h2>🔍 Diagnóstico Profundo: Promociones</h2>";

// 1. Ver qué columnas existen realmente
echo "<h3>1. Estructura de la columna Promo</h3>";
$res_columns = $conexion->query("SHOW COLUMNS FROM mdg_conexiones LIKE 'Promo'");
$col = $res_columns->fetch_assoc();
echo "Tipo de dato en DB: <b>" . $col['Type'] . "</b><br>";

// 2. Muestreo aleatorio de datos
echo "<h3>2. Muestreo de los primeros 10 registros (Crudo)</h3>";
$res_raw = $conexion->query("SELECT IdConexion, Promo FROM mdg_conexiones LIMIT 10");
echo "<table border='1' style='border-collapse:collapse;'><tr><th>IdConexion</th><th>Contenido de Campo 'Promo'</th><th>Longitud</th></tr>";
while($row = $res_raw->fetch_assoc()) {
    $len = strlen($row['Promo']);
    $val = ($row['Promo'] === null) ? "<i style='color:red;'>NULL</i>" : "'".$row['Promo']."'";
    echo "<tr><td>{$row['IdConexion']}</td><td>$val</td><td>$len</td></tr>";
}
echo "</table>";

// 3. Buscar cualquier cosa que NO sea vacío
echo "<h3>3. Buscando registros con CUALQUIER dato</h3>";
$sql_search = "SELECT Promo, COUNT(*) as cantidad 
               FROM mdg_conexiones 
               WHERE Promo IS NOT NULL 
               AND Promo != '' 
               AND Promo != ' '
               AND Promo != '0'
               GROUP BY Promo";
$res_search = $conexion->query($sql_search);

if ($res_search->num_rows > 0) {
    echo "✅ ¡Se encontraron promociones!:<br>";
    while($row = $res_search->fetch_assoc()) {
        echo "- <b>{$row['Promo']}</b>: {$row['cantidad']} registros<br>";
    }
} else {
    echo "❌ <b>ERROR:</b> No hay ni un solo registro con datos en la columna Promo.<br>";
    
    // 4. Verificar si la columna Promo existe en el CSV original pero no se importó
    echo "<h3>4. Verificando integridad de la importación</h3>";
    $res_total = $conexion->query("SELECT 
        SUM(CASE WHEN Promo IS NULL THEN 1 ELSE 0 END) as nulos,
        SUM(CASE WHEN Promo = '' THEN 1 ELSE 0 END) as vacios
        FROM mdg_conexiones");
    $stats = $res_total->fetch_assoc();
    echo "Registros NULL: " . $stats['nulos'] . "<br>";
    echo "Registros Vacíos (' '): " . $stats['vacios'] . "<br>";
}
?>