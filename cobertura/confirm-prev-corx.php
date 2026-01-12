<?php
include("conn.php");

// Las variables pueden recibirse tanto por GET como por POST
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : (isset($_GET['nombre']) ? $_GET['nombre'] : '');
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : (isset($_GET['telefono']) ? $_GET['telefono'] : '');
$source = isset($_POST['source']) ? $_POST['source'] : (isset($_GET['source']) ? $_GET['source'] : '');
$campaign = isset($_POST['campaign']) ? $_POST['campaign'] : (isset($_GET['campaign']) ? $_GET['campaign'] : '');
$medium = isset($_POST['medium']) ? $_POST['medium'] : (isset($_GET['medium']) ? $_GET['medium'] : '');
$dispositivo = isset($_POST['dispositivo']) ? $_POST['dispositivo'] : (isset($_GET['dispositivo']) ? $_GET['dispositivo'] : '');

if (strlen($telefono) > 10) {
    $telefono = substr($telefono, -10);
}

// Verificar si se ha hecho clic en "NO"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'no') {
    // Eliminar el campo "domicilio" para el "telefono" en cuestión
    $stmt = $conn->prepare("UPDATE formularios SET domicilio = NULL WHERE telefono = ?");
    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $stmt->close();

    // Redirigir al usuario a "finder-web.php" con las variables necesarias
    $redirect_url = "finder-web.php?" . http_build_query([
        'nombre' => $nombre,
        'telefono' => $telefono,
        'source' => $source,
        'campaign' => $campaign,
        'medium' => $medium
    ]);
    header("Location: $redirect_url");
    exit();
}

// Obtener 'domicilio' de la tabla 'formularios' para el 'telefono' dado
$domicilio = '';
$localidad = '';
$stmt = $conn->prepare("SELECT domicilio, localidad FROM formularios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$stmt->bind_result($domicilio_db, $localidad_db);
if ($stmt->fetch()) {
    $domicilio = $domicilio_db;
    $localidad = $localidad_db;
}
$stmt->close();

// Asegurarse de que 'domicilio' esté establecido
if (empty($domicilio)) {
    $domicilio = isset($_POST['domicilio']) ? $_POST['domicilio'] : (isset($_GET['domicilio']) ? $_GET['domicilio'] : '');
}

// Validar y sanitizar $domicilio
$domicilio = htmlspecialchars($domicilio, ENT_QUOTES, 'UTF-8');

$link = "https://encuentrainternet.com.ar/redirects/redirect-wsp-asesor.php?mssg=Hola%20EncuentraInternet,%20a%20continuaci%C3%B3n%20enviar%C3%A9%20mi%20ubicaci%C3%B3n%20para%20conocer%20servicios%20de%20Internet%20y%20Cable%20en%20mi%20zona";
$localidad_trimmed = explode(',', $localidad)[0];
?>
<!DOCTYPE HTML>
<html>
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JDEDE2T0NL"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-JDEDE2T0NL');
    </script>
    <title>Cobertura y precios de servicios de Internet y Cable en <?php echo htmlspecialchars($localidad_trimmed, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="style.css" />
    <!-- Incluir Font Awesome para los íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Estilos personalizados -->
    <style>
        #map { height: 200px; width: 100%; max-width: 800px; margin: 0 auto; }
        body, td, th {
            font-family: Roboto, sans-serif;
        }
        body {
            background-color: #f2f2f2;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            border: none;
            cursor: pointer;
            margin: 10px;
            text-decoration: none;
            width: 120px;
        }
        .btn-no {
            background-color: red;
        }
        .btn-yes {
            background-color: green;
        }
        .btn i {
            margin-right: 10px;
            font-size: 20px;
        }
        .button-container {
            text-align: center;
        }
        /* Asegurarse de que los estilos se apliquen a botones dentro de formularios */
        a.btn, button.btn {
            text-decoration: none;
            color: white;
        }
        form {
            display: inline;
            margin: 0;
            padding: 0;
        }
    </style>
    <!-- Leaflet CSS y JS para OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<div class="login-page">
  <div class="form">
    <div class="login">
        <!-- Mapa mostrado primero -->
        <div id="map"></div>

        <!-- Título debajo del mapa -->
      <div><h1>¿Consultás por esta ubicación?</h1></div>

        <!-- Botones debajo del título, uno al lado del otro -->
        <div class="button-container">
            <!-- Botón "NO" que envía un formulario para borrar "domicilio" y redirigir -->
          <form method="post">
                <input type="hidden" name="action" value="no">
                <button type="submit" class="btn btn-no">
                    <i class="fas fa-times"></i>NO
                </button>
            </form>
            <!-- Botón "SI" que redirige a la consulta -->
            <a href="https://mi.encuentrainternet.com.ar/ver-consulta.php?telefono=<?php echo urlencode($telefono); ?>" class="btn btn-yes">
                <i class="fas fa-check"></i>SI
            </a>
      </div>

        <!-- Campos ocultos -->
        <input name="nombre" type="hidden" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="telefono" type="hidden" value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="localidad" type="hidden" value="<?php echo htmlspecialchars($localidad, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="domicilio" type="hidden" value="<?php echo htmlspecialchars($domicilio, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="source" type="hidden" value="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="campaign" type="hidden" value="<?php echo htmlspecialchars($campaign, ENT_QUOTES, 'UTF-8'); ?>" />
        <input name="dispositivo" type="hidden" value="<?php echo htmlspecialchars($dispositivo, ENT_QUOTES, 'UTF-8'); ?>" />

        <!-- Scripts para inicializar el mapa -->
      <script>
            var domicilio = "<?php echo $domicilio; ?>";
            // Validar las coordenadas
            var coords = domicilio.split(",").map(Number);
            if (coords.length === 2 && !isNaN(coords[0]) && !isNaN(coords[1])) {
                var map = L.map("map").setView(coords, 15);

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
                }).addTo(map);

                L.marker(coords).addTo(map)
                    .bindPopup("¡VOS!")
                    .openPopup();
            } else {
                // Mostrar mensaje de error o ubicación por defecto
                document.getElementById("map").innerHTML = "<p style='text-align:center;'>No se pudo cargar el mapa. Coordenadas inválidas.</p>";
            }
        </script>
    </div>
  </div>
</div>
</body>
</html>
