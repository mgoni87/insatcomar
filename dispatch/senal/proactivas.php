<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');
define('WEATHER_API_KEY', '4e47aa8f659a403a8db163113250505'); // Replace with your actual WeatherAPI key

// Create log file
$logFile = 'proactivas_log.txt';
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

try {
    // Initialize database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMessage("Database connection established");

    // Default date range (last 7 days from now)
    $endDate = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
    $defaultStartDate = clone $endDate;
    $defaultStartDate->modify('-7 days');
    $defaultStartDateStr = $defaultStartDate->format('Y-m-d\TH:i');
    $defaultEndDateStr = $endDate->format('Y-m-d\TH:i');

    // Fetch all activations with modem_id and hub
    $stmt = $pdo->query("SELECT act, nro_doc, latitud, longitud, esno_activacion, modem_id, hub FROM activations WHERE activo = 1");
    $activations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    logMessage("Fetched " . count($activations) . " active activations");

    // Initial calculation (will be overridden by AJAX)
    $results = [];
    foreach ($activations as $activation) {
        $act = $activation['act'];
        $esnoActivacion = floatval($activation['esno_activacion']);

        $signalStmt = $pdo->prepare("SELECT timestamp, EsNO, CNO FROM signal_data WHERE act = :act AND timestamp BETWEEN :start_date AND :end_date AND EsNO > 0 AND CNO > 0");
        $signalStmt->execute([':act' => $act, ':start_date' => $defaultStartDateStr, ':end_date' => $defaultEndDateStr]);
        $signalData = $signalStmt->fetchAll(PDO::FETCH_ASSOC);
        logMessage("Initial fetch " . count($signalData) . " signal points for act $act");

        if (empty($signalData)) {
            logMessage("No signal data for act $act, skipping");
            continue;
        }

        $esnoValues = array_column($signalData, 'EsNO');
        $esnoPromedio = array_sum($esnoValues) / count($esnoValues);
        $mean = $esnoPromedio;
        $variance = 0;
        foreach ($esnoValues as $esno) {
            $variance += pow($esno - $mean, 2);
        }
        $desviacionEstandar = sqrt($variance / count($esnoValues));
        $lowSignalCount = 0;
        foreach ($esnoValues as $esno) {
            if (($esnoActivacion - $esno) > 1.5) {
                $lowSignalCount++;
            }
        }
        $bajaSenal = ($lowSignalCount / count($esnoValues)) * 100;
        $resultado = $desviacionEstandar * $bajaSenal;

        $results[] = [
            'act' => $act,
            'nro_doc' => $activation['nro_doc'],
            'latitud' => $activation['latitud'],
            'longitud' => $activation['longitud'],
            'esno_activacion' => $esnoActivacion,
            'esno_promedio' => $esnoPromedio,
            'desviacion_estandar' => $desviacionEstandar,
            'baja_senal' => $bajaSenal,
            'resultado' => $resultado,
            'modem_id' => $activation['modem_id'],
            'hub' => $activation['hub']
        ];
        logMessage("Initial metrics for act $act: EsNO Promedio=$esnoPromedio, Desviacion=$desviacionEstandar, Baja Señal=$bajaSenal%, Resultado=$resultado");
    }

    // Sort by resultado in descending order and get top 250
    usort($results, function($a, $b) {
        return $b['resultado'] <=> $a['resultado'];
    });
    $topResults = array_slice($results, 0, 250);
    logMessage("Initial sorted and selected top 250 records");

    // Display results
    echo "<!DOCTYPE html><html lang='es'><head><meta charset='utf-8'><title>Proactivas</title>";
    echo "<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>";
    echo "<meta name='robots' content='noindex, nofollow' />";
    echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
    echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css'>";
    echo "<script src='https://code.jquery.com/jquery-2.2.4.min.js'></script>";
    echo "<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>";
    echo "<script src='https://code.highcharts.com/highcharts.js'></script>";
    echo "<script src='https://code.highcharts.com/modules/series-label.js'></script>";
    echo "<script src='https://code.highcharts.com/modules/exporting.js'></script>";
    echo "<script src='https://code.highcharts.com/modules/export-data.js'></script>";
    echo "<style>body { font-family: 'Carme', sans-serif; padding-top: 20px; } .content { padding: 20px; max-width: 1200px; margin: 0 auto; } .table-container { margin-top: 20px; } .total-records { margin-bottom: 10px; font-weight: bold; } .modal-dialog { width: 90%; max-width: 1200px; } .modal-body { height: 500px; } .chart-btn { padding: 5px 10px; font-size: 12px; } .date-range { margin-bottom: 20px; } .date-range a.btn { margin-left: 10px; }</style>";
    echo "</head><body><div class='content'><section class='content-header'><h1>Proactivas</h1><ol class='breadcrumb'><li><a href='#'><i class='fa fa-dashboard'></i> Home</a></li><li class='active'>View Proactivas</li></ol></section><section class='content'>";

    // Date range input, process button, and scraper button
    echo "<div class='date-range'><label for='startDate'>Start Date/Time: </label><input type='datetime-local' id='startDate' value='$defaultStartDateStr'><label for='endDate'> End Date/Time: </label><input type='datetime-local' id='endDate' value='$defaultEndDateStr'><button id='processBtn' class='btn btn-primary'>Process</button><a href='scraper_senal.php' target='_blank' class='btn btn-success'>Run Scraper</a></div>";

    // Table container
    echo "<div class='box table-container'><div class='box-header with-border'><h3 class='box-title'>Proactivas Records</h3><div class='total-records' id='totalRecords'>Total: " . count($topResults) . " results</div></div><div class='box-body'><table id='proactivasTable' class='table table-bordered table-hover'><thead><tr><th>ACT</th><th>N° Doc</th><th>Latitud</th><th>Longitud</th><th>EsNO Activación</th><th>EsNO Promedio</th><th>Desviación Estándar</th><th>Baja Señal (%)</th><th>Resultado</th><th>Gráfico</th></tr></thead><tbody id='proactivasTbody'>";
    foreach ($topResults as $row) {
        echo "<tr data-act='" . htmlspecialchars($row['act']) . "' data-modem-id='" . htmlspecialchars($row['modem_id']) . "' data-hub='" . htmlspecialchars($row['hub']) . "' data-latitud='" . htmlspecialchars($row['latitud'] ?? '') . "' data-longitud='" . htmlspecialchars($row['longitud'] ?? '') . "'>";
        echo "<td>" . htmlspecialchars($row['act']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nro_doc']) . "</td>";
        echo "<td>" . htmlspecialchars($row['latitud'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['longitud'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['esno_activacion'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['esno_promedio'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['desviacion_estandar'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['baja_senal'], 2)) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['resultado'], 2)) . "</td>";
        echo "<td><button class='btn btn-primary chart-btn' data-act='" . htmlspecialchars($row['act']) . "' data-modem-id='" . htmlspecialchars($row['modem_id']) . "' data-hub='" . htmlspecialchars($row['hub']) . "' data-latitud='" . htmlspecialchars($row['latitud'] ?? '') . "' data-longitud='" . htmlspecialchars($row['longitud'] ?? '') . "'>Ver Gráfico</button></td>";
        echo "</tr>";
    }
    echo "</tbody></table></div></div>";

    // Modal for signal chart
    echo "<div class='modal fade' id='signalModal' tabindex='-1' role='dialog' aria-labelledby='signalModalLabel'>";
    echo "<div class='modal-dialog' role='document'><div class='modal-content'>";
    echo "<div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
    echo "<h4 class='modal-title' id='signalModalLabel'>Signal Data for ACT <span id='modalAct'></span></h4></div>";
    echo "<div class='modal-body'><div id='signalChart' style='min-width: 310px; height: 400px; margin: 0 auto'></div></div>";
    echo "<div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Close</button></div>";
    echo "</div></div></div>";

    echo "</section></div>";

    // JavaScript Handling
    echo "<script>
        $(document).ready(function() {
            // Function to show chart modal
            function showChartModal(act, modemId, hub, latitud, longitud, startDate, endDate) {
                console.log('Modal data:', { act: act, modemId: modemId, hub: hub, latitud: latitud, longitud: longitud, startDate: startDate, endDate: endDate });
                $('#modalAct').text(act);
                $('#signalModal').modal('show');

                // Validate coordinates
                if (!latitud || !longitud) {
                    console.warn('Missing coordinates for act ' + act + ': latitud=' + latitud + ', longitud=' + longitud);
                    latitud = latitud || -38.053718; // Fallback for debugging
                    longitud = longitud || -57.614307;
                }

                // Fetch signal data
                $.get('get_signal_data.php', { act: act, modem_id: modemId, hub: hub, start_date: startDate, end_date: endDate }, function(signalData) {
                    console.log('Signal data:', signalData);
                    if (signalData.error) {
                        console.error('Signal data error:', signalData.error);
                        return;
                    }

                    // Prepare signal data for chart, using local time (UTC-3) directly
                    var esnoData = [];
                    var cnoData = [];
                    var timestamps = [];
                    signalData.forEach(function(row) {
                        var timestamp = new Date(row.timestamp).getTime() - (3 * 60 * 60 * 1000);
                        console.log('Parsed timestamp:', row.timestamp, 'Local:', new Date(timestamp));
                        esnoData.push([timestamp, parseFloat(row.EsNO)]);
                        cnoData.push([timestamp, parseFloat(row.CNO)]);
                        timestamps.push(row.timestamp);
                    });

                    // Get unique days from timestamps and include endDate if within range
                    var uniqueDays = [...new Set(timestamps.map(ts => ts.split(' ')[0]))];
                    var endDateObj = new Date(endDate);
                    var endDateStr = endDateObj.toISOString().split('T')[0];
                    if (endDateObj >= new Date(timestamps[0] || startDate) && !uniqueDays.includes(endDateStr)) {
                        uniqueDays.push(endDateStr);
                    }
                    console.log('Unique days:', uniqueDays);

                    // Fetch weather data for each unique day
                    var plotBands = [];
                    // Fetch weather data for each unique day
                    var weatherPromises = uniqueDays.map(function(day) {
                        var url = 'https://api.weatherapi.com/v1/history.json?key=' + '" . WEATHER_API_KEY . "' + '&q=' + latitud + ',' + longitud + '&dt=' + day;
                        console.log('Weather API URL:', url);
                        return $.get(url).then(function(weatherData) {
                            console.log('Weather API response for ' + day + ':', weatherData);
                            if (weatherData.error) {
                                console.error('Weather API error for ' + day + ':', weatherData.error.message);
                                return; // Skip processing if API returns an error
                            }
                            // Check if hourly forecast data exists
                            if (!weatherData.forecast || !weatherData.forecast.forecastday || !weatherData.forecast.forecastday[0] || !weatherData.forecast.forecastday[0].hour) {
                                console.warn('No hourly forecast data available for ' + day);
                                return; // Skip processing if hour data is missing
                            }
                            // Process hourly weather data
                            weatherData.forecast.forecastday[0].hour.forEach(function(hour) {
                                if (hour.precip_mm >= 0.3 && hour.precip_mm < 2) {
                                    var timeEpoch = (hour.time_epoch * 1000) - (6 * 60 * 60 * 1000); // Convert UTC to UTC-3
                                    var from = timeEpoch;
                                    var to = timeEpoch + 60 * 60 * 1000; // 60 minutes after
                                    console.log('Rain detected at ' + hour.time + ': precip_mm=' + hour.precip_mm + ', adjusted time=' + new Date(timeEpoch));
                                    plotBands.push({
                                        color: 'rgba(255, 255, 0, 0.2)', // Yellow with transparency
                                        from: from,
                                        to: to,
                                        label: { text: 'Light Rain', style: { color: '#333' } }
                                    });
                                } else if (hour.precip_mm >= 2 && hour.precip_mm < 15) {
                                    var timeEpoch = (hour.time_epoch * 1000) - (6 * 60 * 60 * 1000);
                                    var from = timeEpoch;
                                    var to = timeEpoch + 60 * 60 * 1000;
                                    plotBands.push({
                                        color: 'rgba(255, 153, 35, 0.25)', // Orange with transparency
                                        from: from,
                                        to: to,
                                        label: { text: 'Moderate Rain', style: { color: '#333' } }
                                    });
                                } else if (hour.precip_mm >= 15) {
                                    var timeEpoch = (hour.time_epoch * 1000) - (6 * 60 * 60 * 1000);
                                    var from = timeEpoch;
                                    var to = timeEpoch + 60 * 60 * 1000;
                                    plotBands.push({
                                        color: 'rgba(255, 41, 0, 0.25)', // Red with transparency
                                        from: from,
                                        to: to,
                                        label: { text: 'Heavy Rain', style: { color: '#333' } }
                                    });
                                }
                            });
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            console.error('Weather API request failed for ' + day + ':', textStatus, errorThrown);
                        });
                    });

                    // Wait for all weather API calls to complete
                    Promise.all(weatherPromises).then(function() {
                        console.log('Plot bands:', plotBands);
                        // Render Highcharts with plotBands
                        Highcharts.chart('signalChart', {
                            chart: { zoomType: 'xy' },
                            title: { text: 'Forward Es/N0 - Return C/N0 for ACT ' + act },
                            xAxis: [{
                                type: 'datetime',
                                dateTimeLabelFormats: { day: '%e of %b', hour: '%H:%M' },
                                crosshair: true,
                                plotBands: plotBands
                            }],
                            yAxis: [{
                                labels: { format: '{value}dB', style: { color: Highcharts.getOptions().colors[4] } },
                                title: { text: 'Forward Es/N0', style: { color: Highcharts.getOptions().colors[4] } },
                                opposite: true
                            }, {
                                gridLineWidth: 0,
                                title: { text: 'Return C/N0', style: { color: Highcharts.getOptions().colors[0] } },
                                labels: { format: '{value} dB/Hz', style: { color: Highcharts.getOptions().colors[0] } }
                            }],
                            tooltip: {
                                shared: true,
                                xDateFormat: '%Y-%m-%d %H:%M' // Display in local time (UTC-3)
                            },
                            legend: { layout: 'vertical', align: 'left', x: 80, verticalAlign: 'top', y: 55, floating: true, backgroundColor: '#FFFFFF' },
                            series: [{
                                name: 'Return C/N0',
                                type: 'spline',
                                yAxis: 1,
                                data: cnoData,
                                tooltip: { valueSuffix: ' dB/Hz' },
                                marker: { enabled: false },
                                dashStyle: 'shortdot'
                            }, {
                                name: 'Forward Es/N0',
                                type: 'spline',
                                data: esnoData,
                                tooltip: { valueSuffix: ' dB' },
                                marker: { enabled: true },
                                color: Highcharts.getOptions().colors[4]
                            }]
                        });
                    }).catch(function(error) {
                        console.error('Error processing weather data:', error);
                        // Fallback: Render chart without plotBands
                        Highcharts.chart('signalChart', {
                            chart: { zoomType: 'xy' },
                            title: { text: 'Forward Es/N0 - Return C/N0 for ACT ' + act },
                            xAxis: [{ type: 'datetime', dateTimeLabelFormats: { day: '%e of %b', hour: '%H:%M' }, crosshair: true }],
                            yAxis: [{
                                labels: { format: '{value}dB', style: { color: Highcharts.getOptions().colors[4] } },
                                title: { text: 'Forward Es/N0', style: { color: Highcharts.getOptions().colors[4] } },
                                opposite: true
                            }, {
                                gridLineWidth: 0,
                                title: { text: 'Return C/N0', style: { color: Highcharts.getOptions().colors[0] } },
                                labels: { format: '{value} dB/Hz', style: { color: Highcharts.getOptions().colors[0] } }
                            }],
                            tooltip: {
                                shared: true,
                                xDateFormat: '%Y-%m-%d %H:%M' // Display in local time (UTC-3)
                            },
                            legend: { layout: 'vertical', align: 'left', x: 80, verticalAlign: 'top', y: 55, floating: true, backgroundColor: '#FFFFFF' },
                            series: [{
                                name: 'Return C/N0',
                                type: 'spline',
                                yAxis: 1,
                                data: cnoData,
                                tooltip: { valueSuffix: ' dB/Hz' },
                                marker: { enabled: false },
                                dashStyle: 'shortdot'
                            }, {
                                name: 'Forward Es/N0',
                                type: 'spline',
                                data: esnoData,
                                tooltip: { valueSuffix: ' dB' },
                                marker: { enabled: true },
                                color: Highcharts.getOptions().colors[4]
                            }]
                        });
                    });
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Signal AJAX error:', textStatus, errorThrown);
                });
            }

            // Bind click events to chart buttons
            function bindChartButtonEvents() {
                $('.chart-btn').off('click').on('click', function() {
                    var act = $(this).data('act');
                    var modemId = $(this).data('modem-id');
                    var hub = $(this).data('hub');
                    var latitud = $(this).data('latitud');
                    var longitud = $(this).data('longitud');
                    var startDate = $('#startDate').val();
                    var endDate = $('#endDate').val();
                    showChartModal(act, modemId, hub, latitud, longitud, startDate, endDate);
                });
            }

            // Bind click events to initial buttons
            bindChartButtonEvents();

            // Process button click to update table
            $('#processBtn').click(function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                console.log('Processing with dates:', { startDate: startDate, endDate: endDate });
                $.post('proactivas_process.php', { start_date: startDate, end_date: endDate }, function(data) {
                    console.log('Process response:', data);
                    if (data.error) {
                        console.error('Process error:', data.error);
                        return;
                    }
                    $('#proactivasTbody').html(data.table);
                    $('#totalRecords').text('Total: ' + data.count + ' results');
                    // Rebind click events to new buttons
                    bindChartButtonEvents();
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Process AJAX error:', textStatus, errorThrown);
                });
            });
        });
    </script>";
    echo "</body></html>";

} catch (PDOException $e) {
    logMessage("Database error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>