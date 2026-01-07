<?php

require_once 'config.php';

date_default_timezone_set(APP_TIMEZONE);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$act = isset($_GET['act']) ? $conn->real_escape_string($_GET['act']) : '';
if (!$act) {
    die('ACT no proporcionado.');
}

$sqlPlan = "
    SELECT plan
    FROM consumos_diarios
    WHERE act = '{$act}'
    ORDER BY fecha_consumo DESC
    LIMIT 1
";

$resultPlan = $conn->query($sqlPlan);
$plan = '';

if ($resultPlan && $row = $resultPlan->fetch_assoc()) {
    $plan = $row['plan'];
}

// 🧠 Cálculos para la tarjeta de resumen
$hoy = date('Y-m-d');
$diaHoy = date('d');

// --- ✅ CORRECCIÓN: Lógica de cálculo de ciclo ---
// Se ajustó la lógica para calcular correctamente el ciclo de consumo actual.
if ((int)$diaHoy >= 25) {
    // El ciclo va del 25 del mes actual al 24 del mes siguiente.
    $inicioCiclo = date('Y-m-25');
    $finCiclo = date('Y-m-24', strtotime('next month'));
} else {
    // El ciclo va del 25 del mes pasado al 24 del mes actual.
    $inicioCiclo = date('Y-m-25', strtotime('last month'));
    $finCiclo = date('Y-m-24');
}

$sqlTotalHoy = "
    SELECT SUM(total_gb) as total
    FROM consumos_diarios
    WHERE act = '{$act}' AND fecha_consumo BETWEEN '{$inicioCiclo}' AND '{$hoy}'
";
$totalConsumidoHastaHoy = ($conn->query($sqlTotalHoy)->fetch_assoc())['total'] ?? 0;

$diasTranscurridos = (new DateTime($inicioCiclo))->diff(new DateTime($hoy))->days + 1;
$totalDiasCiclo = (new DateTime($inicioCiclo))->diff(new DateTime($finCiclo))->days + 1;
$diasRestantes = $totalDiasCiclo - $diasTranscurridos;
$consumoProyectado = ($diasTranscurridos > 0) ? ($totalConsumidoHastaHoy / $diasTranscurridos) * $totalDiasCiclo : 0;

// --- ✅ MODIFICACIÓN: Límite del plan ajustado ---
                $foundFriendlyPlan = 'N/A';
                foreach (PLANES_MAP as $friendlyName => $variants) {
                    if (in_array($plan, $variants)) {
                        $foundFriendlyPlan = $friendlyName;
                        break;
                    }
                }
                $planLimiteGB = 'Desconocido'; // Default if not found in lookup
                // Usar lookup (este array necesita ser definido o cargado, similar a PLANES_MAP)
                // Por simplicidad, asumo un mapeo para los límites de GB. Esto NO está en config.php.
                // DEBES DEFINIR ESTOS LIMITES EN config.php SI QUIERES QUE SEAN CENTRALIZADOS.
                // Ejemplo: const PLAN_LIMITS_GB = [ 'Entry' => 10, 'Standard 10' => 10, ... ];
                // Por ahora, haré una simulación simple.
                // SI NO LO HACES EN config.php, el valor de $planLimiteGB seguirá siendo 'Desconocido'.
                $planLimits = [
                    'Entry' => 10,
                    'Weekend 10' => 10,
                    'Family 40' => 40,
                    'Family 100' => 100,
                    'Professional 50' => 50,
                    'Professional 80' => 80,
                    'Professional 120' => 120,
                    'Pyme 50' => 50,
                    'Pyme 80' => 80,
                    'Pyme 120' => 120,
                    'Agro 150' => 150,
                    'Agro 220' => 220,
                    'Agro 280' => 280,
                    'Estandar' => 100,
                    'Priorizado 80' => 150,
                    'Priorizado 200' => 350
                ];
                if (isset($planLimits[$foundFriendlyPlan])) {
                    $planLimiteGB = $planLimits[$foundFriendlyPlan];
                } else {
                    $planLimiteGB = "N/A (" . htmlspecialchars($plan) . ")"; // Muestra el nombre real si no se encuentra el límite
                }

$porcentajeConsumido = $planLimiteGB > 0 ? ($totalConsumidoHastaHoy / $planLimiteGB) * 100 : 'N/A';

// --- ✅ MODIFICACIÓN: Lógica de Alertas y Recomendaciones ---
// Se ajustó para mostrar un estado "normal" y una recomendación acorde.
$alerta = '';
$recomendacion = '';
if ($porcentajeConsumido !== 'N/A') {
    if ($porcentajeConsumido >= 90) {
        $alerta = "Estás cerca de consumir el total del plan.";
        $recomendacion = "Considera reducir el uso o ampliar el plan.";
    } elseif ($porcentajeConsumido >= 70) {
        $alerta = "Has consumido más del 70% del plan.";
        $recomendacion = "Monitorea el consumo con atención.";
    } else {
        $alerta = "Consumo normal: " . number_format($porcentajeConsumido, 2) . "% del plan.";
        $recomendacion = "Continúe monitoreando su uso.";
    }
}

// --- ✅ NUEVO: Lógica para el gráfico de consumo diario del ciclo actual ---
$sqlConsumoCicloActual = "
    SELECT fecha_consumo, total_gb
    FROM consumos_diarios
    WHERE act = '{$act}'
      AND fecha_consumo BETWEEN '{$inicioCiclo}' AND '{$finCiclo}'
    ORDER BY fecha_consumo
";
$resultConsumoCicloActual = $conn->query($sqlConsumoCicloActual);

$labelsCicloActual = [];
$dataCicloActual = [];
$consumosPorDia = [];
$fechasFullCicloActual = []; // Para almacenar las fechas completasMM-DD
if ($resultConsumoCicloActual) {
    while ($dia = $resultConsumoCicloActual->fetch_assoc()) {
        $consumosPorDia[$dia['fecha_consumo']] = $dia['total_gb'];
    }
}

// Genera etiquetas para todos los días del ciclo para mostrar días sin consumo.
$periodo = new DatePeriod(new DateTime($inicioCiclo), new DateInterval('P1D'), (new DateTime($finCiclo))->modify('+1 day'));
foreach ($periodo as $value) {
    $fecha = $value->format('Y-m-d');
    $labelsCicloActual[] = $value->format('d/m/Y');
    $fechasFullCicloActual[] = $fecha; // Guarda la fecha completa aquí
    // Solo se agregan datos de consumo hasta el día de hoy.
    if ($fecha <= $hoy) {
        $dataCicloActual[] = $consumosPorDia[$fecha] ?? 0;
    } else {
        $dataCicloActual[] = null; // Días futuros no se grafican.
    }
}
$jsonLabelsCicloActual = json_encode($labelsCicloActual);
$jsonDataCicloActual = json_encode($dataCicloActual);
$jsonFechasFullCicloActual = json_encode($fechasFullCicloActual); // JSON para las fechas completas


$sqlMeses = "
    SELECT
        YEAR(DATE_ADD(fecha_consumo, INTERVAL 7 DAY)) AS anio_ajustado,
        MONTH(DATE_ADD(fecha_consumo, INTERVAL 7 DAY)) AS mes_ajustado,
        SUM(total_gb) AS total_mes
    FROM consumos_diarios
    WHERE act = '{$act}'
    GROUP BY anio_ajustado, mes_ajustado
    ORDER BY anio_ajustado DESC, mes_ajustado DESC
";
$resultMeses = $conn->query($sqlMeses);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Detalle de Consumos - ACT {$act}</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
    <style>
        .card-dark {
            background-color: #2c3e50;
            color: #ecf0f1;
            border: none;
        }
        .text-info { color: #3498db !important; }
        .modal-content-dark {
            background-color: #2c3e50;
            color: #ecf0f1;
        }
        .modal-header-dark {
            border-bottom: 1px solid #4a667b;
        }
        .modal-footer-dark {
            border-top: 1px solid #4a667b;
        }
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%); /* Makes the close button white */
        }
		#appConsumptionChart, #accumulatedAppConsumptionChart {
			background-color: #71808f; /* A very light grey, like Bootstrap's bg-light */
		}
    </style>
</head>
<body class='bg-light'>
<div class='container my-5'>
    <h1 class='mb-4 text-center'>📈 Detalle de Consumos</h1>
    <h3 class='text-center mb-4'>ACT: {$act} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	Plan: {$plan}</h3>
";

// --- ✅ MODIFICACIÓN: Tarjeta de resumen con el nuevo gráfico ---
if ($act):
    echo "
    <div class='card mb-4 card-dark'>
        <div class='card-header'>Resumen de Consumo y Proyección para ACT: <strong>" . htmlspecialchars($act) . "</strong></div>
        <div class='card-body'>
            <div class='row align-items-center'>
                <div class='col-md-4'>
                    <p><strong>Total Consumido (hasta hoy):</strong> " . number_format($totalConsumidoHastaHoy, 2) . " GB</p>
                    <p><strong>Días Transcurridos en Ciclo:</strong> {$diasTranscurridos} / {$totalDiasCiclo}</p>
                    <p><strong>Días Restantes en Ciclo:</strong> {$diasRestantes}</p>
                    <p><strong>Consumo Proyectado al fin del ciclo:</strong> " . number_format($consumoProyectado, 2) . " GB</p>
                    <p><strong>Límite del Plan:</strong> {$planLimiteGB} GB</p>";

    if ($porcentajeConsumido !== 'N/A') {
        $color = $porcentajeConsumido >= 90 ? 'bg-danger' : ($porcentajeConsumido >= 70 ? 'bg-warning text-dark' : 'bg-success');
        echo "
                    <p><strong>Porcentaje Consumido del Plan:</strong>
                        <span class='badge rounded-pill {$color}'>" . number_format($porcentajeConsumido, 2) . "%</span>
                    </p>";
    }

    if ($alerta) {
        echo "<p class='text-info'><strong>Alerta:</strong> " . htmlspecialchars($alerta) . "</p>";
    }
    if ($recomendacion) {
        echo "<p class='text-info'><strong>Recomendación:</strong> " . htmlspecialchars($recomendacion) . "</p>";
    }

    echo "
                </div>
                <div class='col-md-8'>
                    <canvas id='consumoDiarioCicloChart' height='150'></canvas>
                     <button class='btn btn-primary btn-sm mt-3' onclick=\"showAccumulatedAppConsumptionModal('{$inicioCiclo}', '{$finCiclo}', '{$act}', 'Ciclo Actual');\">Consumo por Apps (Ciclo Actual)</button>
                </div>
            </div>
        </div>
    </div>";
endif;

echo "<div>
	<h3 class='text-center mb-4'>Consumos Históricos</h3>
</div>
";

echo "<div class='accordion' id='accordionMeses'>";

$accordion_html = '';
$accordion_scripts = '';
$fechasFullAccordion = []; // Almacena las fechasMM-DD para cada acordeón para pasarlas al JS

$index = 0;
while ($row = $resultMeses->fetch_assoc()) {
    $anio = $row['anio_ajustado'];
    $mes = $row['mes_ajustado'];
    $totalMes = $row['total_mes'];

    $mes_anterior = $mes - 1;
    $anio_inicio = $anio;
    if ($mes_anterior == 0) {
        $mes_anterior = 12;
        $anio_inicio = $anio - 1;
    }
    $fecha_inicio_sql = "{$anio_inicio}-{$mes_anterior}-25";
    $fecha_fin_sql = "{$anio}-{$mes}-24";

    $fecha_inicio_display = date('d/m/Y', strtotime($fecha_inicio_sql));
    $fecha_fin_display = date('d/m/Y', strtotime($fecha_fin_sql));

    $accordion_html .= "
    <div class='accordion-item'>
        <h2 class='accordion-header' id='heading{$index}'>
            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse{$index}'>
                {$fecha_inicio_display} - {$fecha_fin_display} ➔ Total: " . number_format($totalMes, 2) . " GB
            </button>
        </h2>
        <div id='collapse{$index}' class='accordion-collapse collapse' data-bs-parent='#accordionMeses'>
            <div class='accordion-body'>
                <canvas id='chart{$index}' height='100'></canvas>
                <button class='btn btn-primary btn-sm mt-3' onclick=\"showAccumulatedAppConsumptionModal('{$fecha_inicio_sql}', '{$fecha_fin_sql}', '{$act}', '{$fecha_inicio_display} - {$fecha_fin_display}');\">Consumo por Apps (Ciclo)</button>
            </div>
        </div>
    </div>";

    $sqlDias = "SELECT fecha_consumo, total_gb FROM consumos_diarios WHERE act = '{$act}' AND fecha_consumo BETWEEN '{$fecha_inicio_sql}' AND '{$fecha_fin_sql}' ORDER BY fecha_consumo";
    $resultDias = $conn->query($sqlDias);
    $labels = [];
    $data = [];
    $fechasFull = []; // Array para las fechas completas de este acordeón
    while ($dia = $resultDias->fetch_assoc()) {
        $labels[] = date('d/m', strtotime($dia['fecha_consumo']));
        $data[] = $dia['total_gb'];
        $fechasFull[] = $dia['fecha_consumo']; // Almacena la fecha completa
    }
    $fechasFullAccordion[] = $fechasFull; // Guarda las fechas completas para cada acordeón

    // Building accordion_scripts directly as JavaScript
    $accordion_scripts .= "
        var ctx_{$index} = document.getElementById('chart{$index}').getContext('2d');
        var chart_{$index} = new Chart(ctx_{$index}, {
            type: 'bar',
            data: {
                labels: " . json_encode($labels) . ",
                datasets: [{ label: 'Consumo Diario (GB)', data: " . json_encode($data) . ", backgroundColor: 'rgba(54, 162, 235, 0.6)' }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                onClick: function(event, elements) {
                    if (elements.length > 0) {
                        var clickedElement = elements[0];
                        var index = clickedElement.index;
                        var dateLabels = " . json_encode($fechasFull) . ";
                        var clickedDate = dateLabels[index];
                        showAppConsumptionModal(clickedDate, '{$act}');
                    }
                }
            }
        });
    ";
    $index++;
}

echo $accordion_html;

echo "
    </div>
    <div class='text-center mt-4'>
        <a href='consulta.php' class='btn btn-secondary'>⬅ Volver a Consulta</a>
    </div>
</div>

<div class='modal fade' id='appConsumptionModal' tabindex='-1' aria-labelledby='appConsumptionModalLabel' aria-hidden='true'>
  <div class='modal-dialog modal-lg'>
    <div class='modal-content modal-content-dark'>
      <div class='modal-header modal-header-dark'>
        <h5 class='modal-title' id='appConsumptionModalLabel'>Consumo por Aplicación para <span id='modalDate'></span></h5>
        <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <canvas id='appConsumptionChart'></canvas>
        <div id='appConsumptionError' class='alert alert-danger mt-3' style='display:none;'></div>
        <div id='noAppData' class='alert alert-info mt-3' style='display:none;'>No hay datos de consumo por aplicación para esta fecha.</div>
      </div>
      <div class='modal-footer modal-footer-dark'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class='modal fade' id='accumulatedAppConsumptionModal' tabindex='-1' aria-labelledby='accumulatedAppConsumptionModalLabel' aria-hidden='true'>
  <div class='modal-dialog modal-lg'>
    <div class='modal-content modal-content-dark'>
      <div class='modal-header modal-header-dark'>
        <h5 class='modal-title' id='accumulatedAppConsumptionModalLabel'>Consumo Acumulado por Aplicación para <span id='accumulatedModalCycle'></span></h5>
        <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <canvas id='accumulatedAppConsumptionChart'></canvas>
        <div id='accumulatedAppConsumptionError' class='alert alert-danger mt-3' style='display:none;'></div>
        <div id='noAccumulatedAppData' class='alert alert-info mt-3' style='display:none;'>No hay datos de consumo acumulado por aplicación para este ciclo.</div>
      </div>
      <div class='modal-footer modal-footer-dark'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>
      </div>
    </div>
  </div>
</div>

"; // Cierre del bloque de echo principal antes de las etiquetas de script

// === COMIENZO DEL BLOQUE DE SCRIPT ===
?>
<script>
// Variable global para almacenar la instancia del gráfico de aplicaciones
var appChartInstance = null;
var accumulatedAppChartInstance = null; // Nueva variable para el gráfico acumulado

document.addEventListener('DOMContentLoaded', function() {
    // --- Script para el gráfico de resumen ---
    var ctxResumen = document.getElementById('consumoDiarioCicloChart').getContext('2d');
    var resumenChart = new Chart(ctxResumen, {
        type: 'bar',
        data: {
            labels: <?php echo $jsonLabelsCicloActual; ?>,
            datasets: [{
                label: 'Consumo Diario (GB)',
                data: <?php echo $jsonDataCicloActual; ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.8)'
            }]
        },
        options: {
            plugins: { legend: { labels: { color: '#ecf0f1' } } },
            scales: {
                x: { ticks: { color: '#ecf0f1' }, grid: { color: 'rgba(236, 240, 241, 0.1)' } },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Consumo (GB)', color: '#ecf0f1' },
                    ticks: { color: '#ecf0f1' },
                    grid: { color: 'rgba(236, 240, 241, 0.1)' }
                }
            },
            // --- Evento onClick para el gráfico de resumen del ciclo ---
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    var clickedElement = elements[0];
                    var index = clickedElement.index;
                    var dateLabels = <?php echo $jsonFechasFullCicloActual; ?>;
                    var clickedDate = dateLabels[index];
                    showAppConsumptionModal(clickedDate, '<?php echo $act; ?>');
                }
            }
        }
    });

    // Scripts para los gráficos del acordeón
    <?php echo $accordion_scripts; ?>
});

/**
 * Función para mostrar el modal de consumo por aplicación.
 * @param {string} date La fecha en formato YYYY-MM-DD.
 * @param {string} act El número de ACT.
 */
function showAppConsumptionModal(date, act) {
    // Limpiar mensajes anteriores y ocultar gráfico
    document.getElementById('appConsumptionError').style.display = 'none';
    document.getElementById('noAppData').style.display = 'none';
    document.getElementById('appConsumptionChart').style.display = 'block';

    // Destruir instancia de gráfico anterior si existe
    if (appChartInstance) {
        appChartInstance.destroy();
        appChartInstance = null;
    }

    // Actualizar título del modal
    document.getElementById('modalDate').innerText = formatDateForDisplay(date);

    // Realizar la solicitud AJAX
    fetch('get_app_consumption.php?act=' + act + '&fecha=' + date)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                document.getElementById('appConsumptionError').innerText = data.error;
                document.getElementById('appConsumptionError').style.display = 'block';
                document.getElementById('appConsumptionChart').style.display = 'none';
                return;
            }

            if (data.apps.length === 0) {
                document.getElementById('noAppData').style.display = 'block';
                document.getElementById('appConsumptionChart').style.display = 'none';
                return;
            }

            var appNames = data.apps.map(app => app.name);
            var appValues = data.apps.map(app => app.y);
            var appColors = data.apps.map(app => app.color);

            var ctxApp = document.getElementById('appConsumptionChart').getContext('2d');
            appChartInstance = new Chart(ctxApp, {
                type: 'doughnut', // Gráfico de anillo o pie
                data: {
                    labels: appNames,
                    datasets: [{
                        label: 'Consumo (GB)',
                        data: appValues,
                        backgroundColor: appColors,
                        borderColor: '#71808f', // Color del borde de las secciones
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right', // Leyenda a la derecha
                            labels: {
                                color: '#ecf0f1', // Color de texto de la leyenda
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map((label, i) => {
                                            const backgroundColor = data.datasets[0].backgroundColor[i];

                                            const total = data.datasets[0].data.reduce((sum, value) => sum + value, 0);
                                            const value = data.datasets[0].data[i];
                                            const percentage = total > 0 ? (value / total * 100).toFixed(2) : '0.00';

                                            return {
                                                text: `${label}: ${value.toFixed(2)} GB (${percentage}%)`,
                                                fillStyle: backgroundColor,
                                                strokeStyle: '#71808f',
                                                lineWidth: 2,
                                                hidden: !chart.isDatasetVisible(0) || chart.getDatasetMeta(0).data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        title: {
                            display: false,
                            text: 'Consumo por Aplicación',
                            color: '#ecf0f1'
                        },
                        tooltip: { // Mostrar porcentaje en el tooltip
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    let value = context.parsed;
                                    let total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                                    let percentage = (value / total * 100).toFixed(2);
                                    return label + value.toFixed(2) + ' GB (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching app consumption:', error);
            document.getElementById('appConsumptionError').innerText = 'Error al cargar datos de consumo por aplicación: ' + error.message;
            document.getElementById('appConsumptionError').style.display = 'block';
            document.getElementById('appConsumptionChart').style.display = 'none';
        });

    // Mostrar el modal
    var myModal = new bootstrap.Modal(document.getElementById('appConsumptionModal'));
    myModal.show();
}

/**
 * Función para mostrar el modal de consumo acumulado por aplicación.
 * @param {string} startDate La fecha de inicio del ciclo en formato YYYY-MM-DD.
 * @param {string} endDate La fecha de fin del ciclo en formato YYYY-MM-DD.
 * @param {string} act El número de ACT.
 * @param {string} cycleDisplay La cadena para mostrar en el título del modal (ej. 'Ciclo Actual' o '01/01/2023 - 31/01/2023').
 */
function showAccumulatedAppConsumptionModal(startDate, endDate, act, cycleDisplay) {
    // Limpiar mensajes anteriores y ocultar gráfico
    document.getElementById('accumulatedAppConsumptionError').style.display = 'none';
    document.getElementById('noAccumulatedAppData').style.display = 'none';
    document.getElementById('accumulatedAppConsumptionChart').style.display = 'block';

    // Destruir instancia de gráfico anterior si existe
    if (accumulatedAppChartInstance) {
        accumulatedAppChartInstance.destroy();
        accumulatedAppChartInstance = null;
    }

    // Actualizar título del modal
    document.getElementById('accumulatedModalCycle').innerText = cycleDisplay;

    // Realizar la solicitud AJAX al nuevo endpoint
    fetch('get_accumulated_app_consumption.php?act=' + act + '&startDate=' + startDate + '&endDate=' + endDate)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                document.getElementById('accumulatedAppConsumptionError').innerText = data.error;
                document.getElementById('accumulatedAppConsumptionError').style.display = 'block';
                document.getElementById('accumulatedAppConsumptionChart').style.display = 'none';
                return;
            }

            if (data.apps.length === 0) {
                document.getElementById('noAccumulatedAppData').style.display = 'block';
                document.getElementById('accumulatedAppConsumptionChart').style.display = 'none';
                return;
            }

            var appNames = data.apps.map(app => app.name);
            var appValues = data.apps.map(app => app.y);
            // You might want to generate a consistent set of colors for accumulated data as well
            var appColors = generateColors(appNames.length);

            var ctxAccumulatedApp = document.getElementById('accumulatedAppConsumptionChart').getContext('2d');
            accumulatedAppChartInstance = new Chart(ctxAccumulatedApp, {
                type: 'bar', // Using bar chart for accumulated as requested
                data: {
                    labels: appNames,
                    datasets: [{
                        label: 'Consumo Acumulado (GB)',
                        data: appValues,
                        backgroundColor: appColors,
                        borderColor: '#71808f',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Usually no legend for single dataset bar chart
                        },
                        title: {
                            display: false,
                            text: 'Consumo Acumulado por Aplicación',
                            color: '#ecf0f1'
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#ecf0f1' },
                            grid: { color: 'rgba(236, 240, 241, 0.1)' }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Consumo (GB)', color: '#ecf0f1' },
                            ticks: { color: '#ecf0f1' },
                            grid: { color: 'rgba(236, 240, 241, 0.1)' }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching accumulated app consumption:', error);
            document.getElementById('accumulatedAppConsumptionError').innerText = 'Error al cargar datos de consumo acumulado por aplicación: ' + error.message;
            document.getElementById('accumulatedAppConsumptionError').style.display = 'block';
            document.getElementById('accumulatedAppConsumptionChart').style.display = 'none';
        });

    // Mostrar el nuevo modal
    var myAccumulatedModal = new bootstrap.Modal(document.getElementById('accumulatedAppConsumptionModal'));
    myAccumulatedModal.show();
}


// Función auxiliar para formatear la fecha para mostrar en el modal
function formatDateForDisplay(dateString) {
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}

// Simple function to generate distinct colors
function generateColors(numColors) {
    const colors = [];
    for (let i = 0; i < numColors; i++) {
        const hue = (i * 137.508) % 360; // Use golden angle approximation for even distribution
        colors.push(`hsl(${hue}, 70%, 60%)`);
    }
    return colors;
}
</script>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>