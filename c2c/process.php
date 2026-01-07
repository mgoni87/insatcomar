<?php

// Verificar si las variables están configuradas y tienen valores
$nombre = isset($_REQUEST['nombre']) ? $_REQUEST['nombre'] : '';
$telefono = isset($_REQUEST['telefono']) ? $_REQUEST['telefono'] : '';
$source = isset($_REQUEST['source']) ? $_REQUEST['source'] : '';
$campaign = isset($_REQUEST['campaign']) ? $_REQUEST['campaign'] : '';
$medium = isset($_REQUEST['medium']) ? $_REQUEST['medium'] : '';

// Convertir el teléfono a un número
$telefono += 0;

// Construir la URL con todas las variables
$link = "https://insat.com.ar/cobertura/finder-web.php?telefono=" . urlencode($telefono) . "&nombre=" . urlencode($nombre) . "&source=" . urlencode($source) . "&campaign=" . urlencode($campaign) . "&medium=" . urlencode($medium);

require 'conn.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = mysqli_query($conn, "SELECT * FROM formularios WHERE telefono='$telefono'");
if(mysqli_num_rows($sql) == 1){	 
    //echo "ESTE TELEFONO EXISTE EN LA TABLA FORMULARIOS. ";
}else{
    $result = $conn->query ("INSERT INTO formularios (nombre, telefono, source, campaign, medium) VALUES ('$nombre', '$telefono', '$source', '$campaign', 'C2C TCB EXTERNO')");
    
    // Redireccionar con la URL construida
    header("Location: $link");
    exit;
}

$sql2 = mysqli_query($conn, "SELECT telefono FROM formularios WHERE telefono='$telefono' AND (vallenet='SI' OR vallenet='Wireless' OR insat='SI' OR orbith='SI')");
if(mysqli_num_rows($sql2) == 1){	 
    //echo "ESTE TELEFONO EXISTE EN LA TABLA FORMULARIOS. ";
}else{
	
	echo "<style>";
	echo "h1, h2, p {";
	echo "    text-align: center;";
	echo "}";
	echo "</style>";
	
    echo "<h1>No hemos detectado servicios aún.</h1>";
	echo "<h2>No podemos generar una llamada. </h2>";
	echo "<p>Inténtalo más tarde o ingresa en tu cuenta para más detalles.</p>";

    exit;
}

$sql = "INSERT INTO c2c (nombre, telefono, source, campaign)
VALUES ('$nombre', '$telefono', 'C2C INSAT', '$campaign')";

if ($conn->query($sql) === TRUE) {
    echo "";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>


<head>
<!-- Browser Color - Chrome, Firefox OS, Opera -->
        <meta name="theme-color" content="#000000"> 
        <!-- Browser Color - Windows Phone --> 
        <meta name="msapplication-navbutton-color" content="#000000"> 
        <!-- Browser Color - iOS Safari --> 
        <meta name="apple-mobile-web-app-status-bar-style" content="#000000">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-1415649-20"></script>
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-1415649-20');
</script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>tuCable | Confirmá tus datos</title>



<style type="text/css">
<!--

input[type=submit] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    float: center;
}

body {
	text-align: center;
}
p {
	font-size: 14px;
}
p {
	font-size: 24px;
}
p {
	font-family: Tahoma, Geneva, sans-serif;
}
.subtit {
	font-size: 9px;
}
.Estilo1 {
	font-size: 12px;
	font-weight: bold;
	color: #FF0000;
}
.Estilo2 {color: #FF0000; font-size: 12px;}
-->
</style>
</head>

<body>
<p><?php echo stripslashes($nombre) ?> te llamaremos ahora al: <?php echo stripslashes($telefono) ?></p>
<p><span class="Estilo1">IMPORTANTE:</span><span class="Estilo2"> La llamada puede ingresar como número privado, anónimo o bloqueado</span></p>
<p class="subtit"><em>Disponible de Lunes a viernes de 9 a 20 hs. y sábados de 10 a 14 hs.</em></p>
<form name="form1" method="post" action="https://phontel.centraltelefonica.com.ar/ws/call.php?numero=<?php echo stripslashes($telefono) ?>&cola=1020&nombre=<?php echo stripslashes($nombre) ?>">
    <div align="center">
      <p>
        <input type="submit" name="VER" id="VER" value="LLAMENME AHORA!">
      </p>
  </div>
</form>
</p>
</body>
</html>