<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Período de Fechas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; font-weight: bold; }
        .result { margin-top: 20px; border: 1px solid #ccc; padding: 15px; background-color: #f9f9f9; }
        .result ul { list-style: none; padding: 0; }
        .result li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Generador de Período de Fechas</h1>

    <form method="post" action="">
        <label for="fecha_inicio">Ingresa una fecha (YYYY-MM-DD):</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : ''; ?>">
        <button type="submit">Mostrar Período</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fechaInput = $_POST['fecha_inicio'];

        // Convertir la fecha de entrada a un objeto DateTime
        $fechaInicio = DateTime::createFromFormat('Y-m-d', $fechaInput);

        // Obtener la fecha de ayer
        $ayer = new DateTime('yesterday');

        // Validar que la fecha de inicio sea válida
        if (!$fechaInicio) {
            echo '<p class="error">Error: El formato de fecha ingresado no es válido. Usa YYYY-MM-DD.</p>';
        } elseif ($fechaInicio > $ayer) {
            echo '<p class="error">Error: La fecha de inicio no puede ser posterior a ayer.</p>';
        } else {
            echo '<div class="result">';
            echo '<h2>Período desde ' . $fechaInicio->format('d/m/Y') . ' hasta ' . $ayer->format('d/m/Y') . ':</h2>';
            echo '<ul>';

            // Iterar desde la fecha de inicio hasta ayer
            $interval = new DateInterval('P1D'); // Intervalo de 1 día
            $period = new DatePeriod($fechaInicio, $interval, $ayer->modify('+1 day')); // +1 day para incluir ayer en el período

            foreach ($period as $date) {
                echo '<li>' . $date->format('d/m/Y (l)') . '</li>'; // Muestra la fecha y el día de la semana
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    ?>

</body>
</html>