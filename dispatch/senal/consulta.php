<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

// Connect to database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch all records
$stmt = $pdo->query("SELECT * FROM activations");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Activations Viewer</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="robots" content="noindex, nofollow" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>

    <style>
        body {
            font-family: 'Carme', sans-serif;
            padding-top: 20px;
        }
        .content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .table-container {
            margin-top: 20px;
        }
        .total-records {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .modal-dialog {
            width: 90%;
            max-width: 1200px;
        }
        .modal-body {
            height: 500px;
        }
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="content">
        <section class="content-header">
            <h1>Activations</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">View Activations</li>
            </ol>
        </section>

        <section class="content">
            <div class="box table-container">
                <div class="box-header with-border">
                    <h3 class="box-title">Activation Records</h3>
                    <div class="total-records">Total: <?php echo count($records); ?> results</div>
                </div>
                <div class="box-body">
                    <table id="activationsTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ACT</th>
                                <th>N° Doc</th>
                                <th>EsNO Activacion</th>
                                <th>Latitud</th>
                                <th>Longitud</th>
                                <th>ID</th>
                                <th>Modem ID</th>
                                <th>Hub</th>
                                <th>Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($records) > 0): ?>
                                <?php foreach ($records as $record): ?>
                                    <tr class="clickable-row" data-act="<?php echo htmlspecialchars($record['act']); ?>">
                                        <td><?php echo htmlspecialchars($record['act']); ?></td>
                                        <td><?php echo htmlspecialchars($record['nro_doc']); ?></td>
                                        <td><?php echo htmlspecialchars($record['esno_activacion']); ?></td>
                                        <td><?php echo htmlspecialchars($record['latitud']); ?></td>
                                        <td><?php echo htmlspecialchars($record['longitud']); ?></td>
                                        <td><?php echo htmlspecialchars($record['id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['modem_id']); ?></td>
                                        <td><?php echo htmlspecialchars($record['hub']); ?></td>
                                        <td><?php echo $record['activo'] ? 'Yes' : 'No'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9"><h4>No records found</h4></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="signalModal" tabindex="-1" role="dialog" aria-labelledby="signalModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="signalModalLabel">Signal Data for ACT <span id="modalAct"></span></h4>
                </div>
                <div class="modal-body">
                    <div id="signalChart" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Make rows clickable
            $('.clickable-row').click(function() {
                var act = $(this).data('act');
                $('#modalAct').text(act);
                $('#signalModal').modal('show');

                // Fetch signal data for the selected ACT
                $.get('get_signal_data.php', { act: act }, function(data) {
                    var esnoData = [];
                    var cnoData = [];
                    data.forEach(function(row) {
                        var timestamp = new Date(row.timestamp).getTime();
                        esnoData.push([timestamp, parseFloat(row.EsNO)]);
                        cnoData.push([timestamp, parseFloat(row.CNO)]);
                    });

                    // Create Highcharts graph
                    Highcharts.chart('signalChart', {
                        chart: {
                            zoomType: 'xy'
                        },
                        title: {
                            text: 'Forward Es/N0 - Return C/N0 for ACT ' + act
                        },
                        xAxis: [{
                            type: 'datetime',
                            dateTimeLabelFormats: {
                                day: '%e of %b'
                            },
                            crosshair: true
                        }],
                        yAxis: [{
                            labels: {
                                format: '{value}dB',
                                style: {
                                    color: Highcharts.getOptions().colors[4]
                                }
                            },
                            title: {
                                text: 'Forward Es/N0',
                                style: {
                                    color: Highcharts.getOptions().colors[4]
                                }
                            },
                            opposite: true
                        }, {
                            gridLineWidth: 0,
                            title: {
                                text: 'Return C/N0',
                                style: {
                                    color: Highcharts.getOptions().colors[0]
                                }
                            },
                            labels: {
                                format: '{value} dB/Hz',
                                style: {
                                    color: Highcharts.getOptions().colors[0]
                                }
                            }
                        }],
                        tooltip: {
                            shared: true
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'left',
                            x: 80,
                            verticalAlign: 'top',
                            y: 55,
                            floating: true,
                            backgroundColor: '#FFFFFF'
                        },
                        series: [{
                            name: 'Return C/N0',
                            type: 'spline',
                            yAxis: 1,
                            data: cnoData,
                            tooltip: {
                                valueSuffix: ' dB/Hz'
                            },
                            marker: {
                                enabled: false
                            },
                            dashStyle: 'shortdot'
                        }, {
                            name: 'Forward Es/N0',
                            type: 'spline',
                            data: esnoData,
                            tooltip: {
                                valueSuffix: ' dB'
                            },
                            marker: {
                                enabled: true
                            },
                            color: Highcharts.getOptions().colors[4]
                        }]
                    });
                }, 'json');
            });
        });
    </script>
</body>
</html>