<?php	

header('Content-type: text/html; charset=utf-8');

// Procesamos en envio desde el input via POST
$palabraclave = strval($_POST['busqueda']);
$busqueda = "%{$palabraclave}%";
// Realizamos la conexión MySQLi
$conexion =new mysqli('localhost', 'tucablec_usr', '89pg7C05WG@3' , 'tucablec_bd');

if ($conexion->set_charset('utf8') === false) {
  die('Error: ' .  $conexion->error);
}

// Preparamos la consulta para realizar la busqueda del criterio
$consultaDB = $conexion->prepare("SELECT * FROM ciudad WHERE ciudad_nombre LIKE ? ORDER BY type DESC");
$consultaDB->bind_param("s",$busqueda);			
$consultaDB->execute();
$resultado = $consultaDB->get_result();
// Condicional para tratar a los resultados encontrados
if ($resultado->num_rows > 0) {
	while($registros = $resultado->fetch_assoc()) {
	// Llamando a la columna Pais_Nombre
	$ResultadoPais[] = $registros["ciudad_nombre"];
	}
	echo json_encode($ResultadoPais);
	}
// Cerramos la conexión con el servidor
$consultaDB->close();
?>

