<?php

echo "<h2>Generando reporte HTML para MonitColasMini...</h2>";

// 1. XML de ejemplo proporcionado
$xml_string = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Respuestas><Respuesta><Dato><![CDATA[AtCteTecnica|0|00:00:00|0|0|0|00:00:00|00:00:00|0;IntvVentas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;NLegal|0|00:00:00|0|0|0|00:00:00|00:00:00|0;AtCteAdmin|0|00:00:00|0|0|0|00:00:00|00:00:00|0;IntvC2Cventas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;AtCteComercial|0|00:00:00|0|0|0|00:00:00|00:00:00|0;PredictivoVENTAS|0|00:00:00|0|0|0|00:00:00|00:00:00|0;IntvRetencion|0|00:00:00|0|0|0|00:00:00|00:00:00|0;InsatVentas|0|00:00:00|0|3|1|00:00:12|00:00:10|3;queueMORA|0|00:00:00|0|0|0|00:00:00|00:00:00|0;IntvBajas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;Ventas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;C2CVentas|0|00:00:00|0|3|0|00:00:41|00:00:25|1;WSPVentas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;queuePREVENTA|0|00:00:00|0|0|0|00:00:00|00:00:00|0;PreVentas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;PosventaAR|0|00:00:00|0|0|0|00:00:00|00:00:00|0;OrbithVentasAR|0|00:00:00|0|0|0|00:00:00|00:00:00|0;AtCteInsatArAdmin|0|00:00:00|0|0|1|00:02:32|00:02:32|0;ValleNETventas|0|00:00:00|0|0|0|00:00:00|00:00:00|0;Test|0|00:00:00|0|0|0|00:00:00|00:00:00|0]]></Dato></Respuesta><Respuesta><Dato><![CDATA[702|205|S|S|Libre|AtCteInsatArAdmin|hcrespo||0|00:04:26|01:47:29|-|-|-|-|tope]]></Dato><Dato><![CDATA[605|995|S|S|Libre|AtCteInsatArAdmin|tferreyra||2|00:59:03|02:46:16|00:27:46|01:18:52|-|-|tope]]></Dato><Dato><![CDATA[601|311|S|S|Libre|C2CVentas|laualv||1|01:34:45|01:50:55|00:05:48|01:29:07|-|-|tope]]></Dato><Dato><![CDATA[600|998|S|S|Libre|C2CVentas|evechia||3|00:02:18|01:41:22|00:12:00|00:00:11|-|-|tope]]></Dato></Respuesta><Respuesta><Dato><![CDATA[2|10:39:28 [ 1161532605 ] ( AtCteInsatArAdmin )<br>10:09:43 [ 1161532605 ] ( InsatVentas )]]></Dato></Respuesta><Respuesta><Dato><![CDATA[00:00:41|C2CVentas]]></Dato></Respuesta><Respuesta><Dato><![CDATA[AtCteTecnica---1|IntvVentas---0|NLegal---2|AtCteAdmin---0|IntvC2Cventas---0|AtCteComercial---0|PredictivoVENTAS---0|IntvRetencion---0|InsatVentas---4|queueMORA---1|IntvBajas---0|Ventas---4|C2CVentas---4|WSPVentas---0|queuePREVENTA---0|PreVentas---4|PosventaAR---4|OrbithVentasAR---0|AtCteInsatArAdmin---2|ValleNETventas---0|Test---0]]></Dato></Respuesta><Respuesta><Dato>4|4|0|0|4|4|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0</Dato></Respuesta><Respuesta><Dato><![CDATA[                       <div style="width: 100%;" class="BGBlanco NegritaCen CPadTB10 t_cm BordeInfCCC">Detalle de Estados</div>
                       <div style="display: grid; grid-template-columns: repeat(3, 1fr);">
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">Pendientes en CRM</div>
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">Break</div>
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">Administrativo</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">Capacitacion</div>
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">FdLinea</div>
                        <div class="BGBlanco NegritaCen CPadTB10 t_cm">GestSaliente</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                        <div class="BGBlanco NegritaCen CPadTB10">0</div>
                       </div>
]]></Dato></Respuesta></Respuestas>';

// 2. Parsear el XML
libxml_clear_errors();
$xml = simplexml_load_string($xml_string);

if ($xml === false) {
    echo "<p style='color: red;'><strong>Error: No se pudo parsear la respuesta XML.</strong></p>";
    foreach (libxml_get_errors() as $error) {
        echo "<p style='color: red;'> - " . htmlspecialchars($error->message) . "</p>";
    }
    exit;
}

// 3. Inicializar variables para los cálculos
$total_finalizadas = 0;
$total_no_atendidas = 0;
$total_totales = 0;
$queue_data = [];
$last_non_attended = '';
$max_wait = '';

// 4. Procesar la primera parte del XML (datos de colas)
foreach ($xml->Respuesta as $respuesta_block) {
    foreach ($respuesta_block->Dato as $dato) {
        $parsed_data = (string)$dato;
        if (strpos($parsed_data, ';') !== false) {
            // Datos de colas
            $queues = explode(';', $parsed_data);
            foreach ($queues as $queue) {
                if (!empty($queue)) {
                    $parts = explode('|', $queue);
                    if (count($parts) === 9) {
                        list($cola, $en_espera, $espera_actual, $en_curso, $finalizadas, $no_atendidas, $espera_maxima, $espera_promedio, $_) = $parts;
                        // Calcular Total como En Curso + Finalizadas + No Atendidas
                        $total = (int)$en_curso + (int)$finalizadas + (int)$no_atendidas;
                        // Calcular Nivel %
                        $nivel_porcentaje = ($total > 0) ? number_format(($finalizadas / $total) * 100, 2) : '0.00';
                        $queue_data[] = [
                            'cola' => $cola,
                            'en_espera' => $en_espera,
                            'espera_actual' => $espera_actual,
                            'en_curso' => $en_curso,
                            'finalizadas' => $finalizadas,
                            'no_atendidas' => $no_atendidas,
                            'espera_maxima' => $espera_maxima,
                            'espera_promedio' => $espera_promedio,
                            'total' => $total,
                            'nivel_porcentaje' => $nivel_porcentaje
                        ];
                        // Sumar para totales
                        $total_finalizadas += (int)$finalizadas;
                        $total_no_atendidas += (int)$no_atendidas;
                        $total_totales += $total;
                    }
                }
            }
        } elseif (strpos($parsed_data, '|10:') !== false) {
            // Última llamada no atendida
            $last_non_attended = explode('|', $parsed_data)[1];
        } elseif (strpos($parsed_data, ':') === 2 && strpos($parsed_data, '|') !== false) {
            // Mayor espera
            $max_wait = $parsed_data;
        }
    }
}

// 5. Calcular el nivel total
$nivel_total = ($total_totales > 0) ? number_format(($total_finalizadas / $total_totales) * 100, 2) : '0.00';

// 6. Generar el HTML
$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Edwar Pons">
    <meta name="copyright" content="AsterVoIP (1998 - 2025)">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Last-Modified" content="0">
    <meta http-equiv="Cache-Control" content="no-cache,mustrevalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>PBX - Admin - Reporte MonitColasMini</title>
    <link rel="stylesheet" href="https://phontel.centraltelefonica.com.ar/pbx/Css/fonts.css?v=1.5">
    <link href="https://phontel.centraltelefonica.com.ar/pbx/Css/variables.css?v=1.5" rel="stylesheet" type="text/css">
    <link href="https://phontel.centraltelefonica.com.ar/pbx/Css/themes/default.css?v=1.5" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://phontel.centraltelefonica.com.ar/pbx/Css/materialIcons.css?v=1.5">
    <link href="https://phontel.centraltelefonica.com.ar/pbx/Css/estilos.css?v=1.5" rel="stylesheet" type="text/css">
    <link rel="icon" type="image/png" href="https://phontel.centraltelefonica.com.ar/pbx/Imgs/Cabecera/favicon.png">
</head>
<body>
    <section id="idCuerpo">
        <div id="MonColF" class="tahoma_11">
            <div class="wrapper">
                <div id="oneDiv" class="one">
                    <div id="co">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <td colspan="11" class="TLeft BGBlanco t_cm">Llamados en Cola</td>
                                </tr>
                                <tr>
                                    <th class="tleft BGBlanco t_cm" rowspan="2" style="width: 20%;">Cola</th>
                                    <th class="tcenter BGBlanco t_cm" colspan="2">Atendidas</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">No Atendidas</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">Total</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">Nivel %</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">Espera Máxima</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">Espera Promedio</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">EN ESPERA</th>
                                    <th class="tcenter BGBlanco t_cm" rowspan="2">Espera Actual</th>
                                </tr>
                                <tr>
                                    <th class="tcenter BGBlanco t_cm" width="10%">En Curso</th>
                                    <th class="tcenter BGBlanco t_cm" width="10%">Finalizadas</th>
                                </tr>
                            </thead>
                            <tbody id="co_tb">
HTML;

// 7. Agregar datos de colas al HTML
foreach ($queue_data as $queue) {
    $html .= <<<HTML
                                <tr>
                                    <td class="tleft">{$queue['cola']}</td>
                                    <td class="tcenter">{$queue['en_curso']}</td>
                                    <td class="tcenter">{$queue['finalizadas']}</td>
                                    <td class="tcenter">{$queue['no_atendidas']}</td>
                                    <td class="tcenter">{$queue['total']}</td>
                                    <td class="tcenter">{$queue['nivel_porcentaje']}</td>
                                    <td class="tcenter">{$queue['espera_maxima']}</td>
                                    <td class="tcenter">{$queue['espera_promedio']}</td>
                                    <td class="tcenter">{$queue['en_espera']}</td>
                                    <td class="tcenter">{$queue['espera_actual']}</td>
                                </tr>
HTML;
}

$html .= <<<HTML
                            </tbody>
                        </table>
                    </div>
                    <div id="ane">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td class="BGBlanco CPadT10 TCenter" width="25%"><h3>Atendidos</h3></td>
                                    <td class="BGBlanco CPadT10 TCenter" width="25%"><h3>Nivel</h3></td>
                                    <td class="BGBlanco CPadT10 TCenter" width="50%"><h3>Mayor Espera</h3></td>
                                </tr>
                                <tr>
                                    <td id="ll_at" class="vAlignM CPadTB10" width="25%">{$total_finalizadas}</td>
                                    <td id="prom_at" class="vAlignM CPadTB10" width="25%">{$nivel_total}</td>
                                    <td id="mte" class="vAlignM CPadTB10">{$max_wait}</td>
                                </tr>
                                <tr>
                                    <td class="BGBlanco CPadT10 TCenter"><h3>No Atendidos</h3></td>
                                    <td class="BGBlanco CPadT10" colspan="2"><h3>Últimas 3 No Atendidos</h3></td>
                                </tr>
                                <tr>
                                    <td id="ll_nat" class="vAlignM CPadTB15 TCenter" width="25%">{$total_no_atendidas}</td>
                                    <td id="dt_nat" class="vAlignM CPadTB15 TRight CPadR05" colspan="2" width="75%">{$last_non_attended}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
HTML;

// 8. Guardar el HTML en un archivo para verificación
$output_file = 'reporte_monit_colas_mini.html';
file_put_contents($output_file, $html);
echo "<p><strong>Reporte HTML generado y guardado en:</strong> $output_file</p>";
echo "<p>Por favor, abre el archivo '$output_file' en un navegador para verificar el contenido.</p>";

// 9. Mostrar un extracto del HTML generado para depuración
echo "<h3>Extracto del HTML generado:</h3>";
echo "<pre>" . htmlspecialchars(substr($html, 0, 1000)) . "...</pre>";

echo "<h2>Script finalizado.</h2>";

?>