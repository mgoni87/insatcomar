<?php
session_start();
// mdm2010_installer.php
// Herramienta para instalar/configurar Newtec MDM2010
// Incluye polling cada 0.5s de GetDeviceStatus y GetPointingState antes del logout,
// muestra el último TextId de Satellite y grafica SignalStrengthDbm y EsN0 en tiempo real.

// ---------- Configuración ----------
define('MODEM_HOST', 'http://192.168.1.1/cgi-bin/cgiclient');
define('EXPERT_PASSWORD', 's3p');

// ---------- Helper para llamadas al módem ----------
function call_modem($jsonPayload) {
    $ch = curl_init();
    $post = 'request=' . $jsonPayload;
    curl_setopt($ch, CURLOPT_URL, MODEM_HOST);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        return ['error' => true, 'message' => "cURL error: $err"];
    }
    $decoded = json_decode($resp, true);
    if ($decoded === null) return ['error' => false, 'raw' => $resp];
    return ['error' => false, 'json' => $decoded, 'raw' => $resp];
}

// ---------- Endpoints AJAX ----------
$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($action) {
    header('Content-Type: application/json; charset=utf-8');

    switch ($action) {
        case 'status':
            $payload = json_encode(["FunctionName" => "GetDeviceStatus"]);
            echo json_encode(call_modem($payload));
            exit;

        case 'pointing_state':
            $payload = json_encode(["FunctionName" => "GetPointingState"]);
            echo json_encode(call_modem($payload));
            exit;

        case 'auth':
            $payload = json_encode([
                'FunctionName' => 'AuthenticatePassword',
                'SessionId' => '',
                'Params' => ['LoginLevel' => 'expert', 'Password' => EXPERT_PASSWORD]
            ]);
            $r = call_modem($payload);
            if (!empty($r['json']['RequestData']['SessionId'])) {
                $_SESSION['session_id'] = $r['json']['RequestData']['SessionId'];
            }
            echo json_encode($r);
            exit;

        case 'get_beams':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $payload = json_encode(['FunctionName' => 'GetExistingBeams', 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'add_beam_5':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $params = json_decode('{ "InitialCarrier1" : { "TimeSliceNumber" : 1, "Freq" : 19551950000, "SymbolRate" : 380952400, "TSMode" : "dvbs2x_timesliced", "Polarization" : 2, "Enabled" : true }, "PointingCarrier1" : { "Carrier" : { "TimeSliceNumber" : 1, "Freq" : 19551950000, "SymbolRate" : 380952400, "TSMode" : "dvbs2x_timesliced", "Polarization" : 2, "Enabled" : true } }, "InitialCarrier2" : { "TimeSliceNumber" : 1, "Freq" : 0, "SymbolRate" : 0, "TSMode" : "dvbs2x", "Polarization" : 0, "Enabled" : false }, "PointingCarrier2" : { "Carrier" : { "TimeSliceNumber" : 1, "Freq" : 0, "SymbolRate" : 0, "TSMode" : "dvbs2x_timesliced", "Polarization" : 2, "Enabled" : false } }, "PolarizationSkew" : 0, "OrbitalDegrees" : 67.1, "SatLatitudeVariance" : 0, "MaxSkew" : 0, "DefaultInitialCarrier" : 1, "DefaultPointingCarrier" : 1, "Hemisphere" : "west", "TxPolarization" : 3, "AcuXString" : "", "BeamName" : "", "Cost" : 0, "AutomaticPointingTimeout" : 210000, "GxtFileName" : "", "ExclusionZones" : [  ], "BeamId" : 5 }', true);
            $payload = json_encode(['FunctionName' => 'AddBeam', 'Params' => $params, 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'set_beam_6':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $params = json_decode('{ "InitialCarrier1" : { "TimeSliceNumber" : 1, "Freq" : 20007230000, "SymbolRate" : 276190500, "TSMode" : "dvbs2x_timesliced", "Polarization" : 3, "Enabled" : true }, "PointingCarrier1" : { "Carrier" : { "TimeSliceNumber" : 1, "Freq" : 20007230000, "SymbolRate" : 276190500, "TSMode" : "dvbs2x_timesliced", "Polarization" : 3, "Enabled" : true } }, "InitialCarrier2" : { "TimeSliceNumber" : 1, "Freq" : 0, "SymbolRate" : 0, "TSMode" : "dvbs2x", "Polarization" : 0, "Enabled" : false }, "PointingCarrier2" : { "Carrier" : { "TimeSliceNumber" : 1, "Freq" : 0, "SymbolRate" : 0, "TSMode" : "dvbs2x_timesliced", "Polarization" : 0, "Enabled" : false } }, "PolarizationSkew" : 0.00, "OrbitalDegrees" : 67.1, "SatLatitudeVariance" : 0.00, "MaxSkew" : 0.00, "DefaultInitialCarrier" : 1, "DefaultPointingCarrier" : 1, "Hemisphere" : "west", "TxPolarization" : 3, "AcuXString" : "", "BeamName" : "", "Cost" : 0, "AutomaticPointingTimeout" : 210000, "GxtFileName" : "", "ExclusionZones" : [  ], "BeamId" : 6 }', true);
            $payload = json_encode(['FunctionName' => 'SetBeamData', 'Params' => $params, 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'set_odu':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $payload = json_encode(['FunctionName' => 'SetActiveODUType', 'Params' => ['ODUTypeId' => 1], 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'set_active_beam':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $beamId = isset($_GET['beam']) ? intval($_GET['beam']) : 6;
            $payload = json_encode(['FunctionName' => 'SetActiveBeam', 'Params' => ['AutoBeamSelection' => false, 'BeamId' => $beamId], 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'start_pointing':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $payload = json_encode(['FunctionName' => 'StartPointing', 'Params' => ['PointingCarrierId' => 1], 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'stop_pointing':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $payload = json_encode(['FunctionName' => 'StopPointing', 'SessionId' => $sessionId]);
            echo json_encode(call_modem($payload));
            exit;

        case 'logout':
            $sessionId = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : '';
            $payload = json_encode(['FunctionName' => 'Logout', 'SessionId' => '', 'Params' => ['SessionId' => $sessionId]]);
            $r = call_modem($payload);
            unset($_SESSION['session_id']);
            echo json_encode($r);
            exit;

        default:
            echo json_encode(['error' => true, 'message' => 'acción desconocida']);
            exit;
    }
}

// ---------- Frontend UI ----------
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Instalador MDM2010 - Pointing en tiempo real</title>
    <style>
        body{font-family: Arial, Helvetica, sans-serif;max-width:1100px;margin:12px auto;padding:10px}
        button{padding:8px 12px;margin:6px}
        pre{background:#f7f7f7;padding:10px;border:1px solid #ddd;overflow:auto}
        .ok{color:green}.err{color:red}
        .flex{display:flex;gap:12px;align-items:center}
        canvas{background:#fff;border:1px solid #ddd}
        #charts{display:flex;gap:12px;flex-wrap:wrap}
        #charts > div{flex:1 1 48%}
    </style>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h1>Instalador MDM2010 — Pointing en tiempo real</h1>
<div class="flex">
    <button id="btnStatus">Obtener estado del módem</button>
    <button id="btnAuth">Iniciar sesión experto</button>
    <button id="btnGetBeams">Listar beams existentes</button>
    <select id="beamSelect">
        <option value="6">Beam 54 (ID 6)</option>
        <option value="5">Beam 50 (ID 5)</option>
    </select>
    <button id="btnEnsureBeam">Asegurar Beam</button>
    <button id="btnSetODU">Seleccionar ODU</button>
    <button id="btnSetActiveBeam">Seleccionar beam activo</button>
    <button id="btnStartPointing">Iniciar pointing</button>
    <button id="btnStopPointing">Detener pointing</button>
    <button id="btnLogout">Cerrar sesión</button>
</div>

<h3>Estado Satellite (última respuesta)</h3>
<div id="satelliteText" style="font-weight:bold">—</div>

<div id="charts">
    <div>
        <h4>SignalStrengthDbm</h4>
        <canvas id="chartDbm" width="600" height="240"></canvas>
    </div>
    <div>
        <h4>EsN0 (dB)</h4>
        <canvas id="chartEsn0" width="600" height="240"></canvas>
    </div>
</div>

<h3>Salida raw</h3>
<pre id="output">—</pre>

<script>
// Simple helper to call server endpoints
async function api(action, params=''){
    const url = location.pathname + '?action=' + encodeURIComponent(action) + (params ? '&' + params : '');
    const r = await fetch(url);
    const txt = await r.text();
    try{ return JSON.parse(txt); } catch(e){ return {raw: txt}; }
}

const outEl = document.getElementById('output');
function print(obj){ outEl.textContent = JSON.stringify(obj, null, 2); }

// Buttons
document.getElementById('btnStatus').addEventListener('click', async ()=>{ print(await api('status')); updateSatelliteText(await api('status')); });
document.getElementById('btnAuth').addEventListener('click', async ()=>{ print(await api('auth')); });
document.getElementById('btnGetBeams').addEventListener('click', async ()=>{ print(await api('get_beams')); });

document.getElementById('btnEnsureBeam').addEventListener('click', async ()=>{
    const chosen = document.getElementById('beamSelect').value;
    const list = await api('get_beams');
    if (list && list.json && list.json.Beams){
        const beams = list.json.Beams;
        if (beams.includes(Number(chosen))){
            print({message: 'Beam ya existe en la lista, se puede saltar crear/actualizar', beams});
            return;
        }
    }
    if (chosen === '5') print(await api('add_beam_5'));
    else print(await api('set_beam_6'));
});

document.getElementById('btnSetODU').addEventListener('click', async ()=>{ print(await api('set_odu')); });
document.getElementById('btnSetActiveBeam').addEventListener('click', async ()=>{
    const chosen = document.getElementById('beamSelect').value;
    print(await api('set_active_beam', 'beam=' + encodeURIComponent(chosen)));
});

document.getElementById('btnStartPointing').addEventListener('click', async ()=>{
    print(await api('start_pointing'));
    startPolling();
});

document.getElementById('btnStopPointing').addEventListener('click', async ()=>{
    stopPolling();
    print(await api('stop_pointing'));
});

document.getElementById('btnLogout').addEventListener('click', async ()=>{
    // Antes de logout, detener polling si está activo (y opcionalmente ejecutar un último polling)
    stopPolling();
    print(await api('logout'));
});

// ----------------- Satellite Text handling -----------------
function updateSatelliteText(statusResponse){
    try{
        const json = statusResponse.json || (statusResponse);
        const sat = json.RequestData && json.RequestData.Satellite;
        if (!sat){ document.getElementById('satelliteText').textContent = 'Sin dato Satellite'; return; }
        // Prefer Arguments[*].TextId if present, si no usar Message.TextId
        const msg = sat.Message || {};
        let display = '';
        if (msg.Arguments && Array.isArray(msg.Arguments) && msg.Arguments.length){
            // mostrar los TextId concatenados con salto de línea
            display = msg.Arguments.map(a => a.TextId || '').filter(s=>s).join('
');
        }
        if (!display && msg.TextId) display = msg.TextId;
        if (!display) display = '(vacío)';
        document.getElementById('satelliteText').textContent = display;
    }catch(e){ console.error(e); }
}

// ----------------- Polling for pointing -----------------
let pollInterval = null;
let pollMs = 500; // 0.5s
let labels = [];
let dataDbm = [];
let dataEsn0 = [];
let maxDbm = null;
let maxEsn0 = null;
const maxPoints = 1200; // evitar crecer indefinidamente (p. ej. 10 minutos a 0.5s -> 1200)

// Chart.js setup
const ctxDbm = document.getElementById('chartDbm').getContext('2d');
const ctxEsn0 = document.getElementById('chartEsn0').getContext('2d');

function createLineChart(ctx, label, yLabel){
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: label, data: yLabel === 'dBm' ? dataDbm : dataEsn0, fill: false, tension: 0.2, pointRadius: 0 },
                { label: 'Máximo observado', data: [], borderDash: [6,4], fill: false, pointRadius: 0 }
            ]
        },
        options: {
            animation:false,
            responsive:true,
            scales: { x: { display:true }, y: { display:true, title: {display:true, text: yLabel} } },
            plugins: { legend: { display:true } }
        }
    });
}

const chartDbm = createLineChart(ctxDbm, 'SignalStrengthDbm', 'dBm');
const chartEsn0 = createLineChart(ctxEsn0, 'EsN0', 'dB');

function shiftIfNeeded(){
    if (labels.length > maxPoints){
        labels.shift(); dataDbm.shift(); dataEsn0.shift();
    }
}

function updateCharts(){
    // Update 'max' dataset to be constant line equal to max
    const maxDbmArr = labels.map(()=> (maxDbm === null ? null : maxDbm));
    const maxEsn0Arr = labels.map(()=> (maxEsn0 === null ? null : maxEsn0));
    chartDbm.data.labels = labels.slice();
    chartDbm.data.datasets[0].data = dataDbm.slice();
    chartDbm.data.datasets[1].data = maxDbmArr;
    chartDbm.update();

    chartEsn0.data.labels = labels.slice();
    chartEsn0.data.datasets[0].data = dataEsn0.slice();
    chartEsn0.data.datasets[1].data = maxEsn0Arr;
    chartEsn0.update();
}

async function doPollOnce(){
    // GetDeviceStatus
    const st = await api('status');
    // update last satellite TextId display
    updateSatelliteText(st);
    // GetPointingState
    const ps = await api('pointing_state');
    // show raw of last pointing state too
    print({status: st, pointing: ps});

    // parse pointing state and update arrays
    try{
        const pjson = ps.json || ps;
        const rd = pjson.RequestData || {};
        const now = new Date().toLocaleTimeString();
        labels.push(now);
        const dbm = (typeof rd.SignalStrengthDbm !== 'undefined') ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm === 'undefined' && typeof rd.SignalStrengthDbm === 'undefined' ? null : rd.SignalStrengthDbm);
        // Some firmwares use SignalStrengthDbm or SignalStrengthDbm (case exact). We'll try common keys
        const dbmVal = (typeof rd.SignalStrengthDbm !== 'undefined') ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm !== 'undefined' ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm !== 'undefined' ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm !== 'undefined' ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm !== 'undefined' ? Number(rd.SignalStrengthDbm) : (typeof rd.SignalStrengthDbm !== 'undefined' ? Number(rd.SignalStrengthDbm) : NaN)))));
        // Fallback: check SignalStrengthDbm or SignalStrengthDbm (case-insensitive)
        let ssdbm = null;
        if (!isNaN(dbmVal)) ssdbm = dbmVal;
        else if (typeof rd.SignalStrengthDbm !== 'undefined') ssdbm = Number(rd.SignalStrengthDbm);
        else if (typeof rd.SignalStrength !== 'undefined') ssdbm = Number(rd.SignalStrength);
        else ssdbm = null;

        const esn0Val = (typeof rd.EsN0 !== 'undefined') ? Number(rd.EsN0) : null;

        dataDbm.push(ssdbm);
        dataEsn0.push(esn0Val);

        if (ssdbm !== null && !isNaN(ssdbm)){
            if (maxDbm === null || ssdbm > maxDbm) maxDbm = ssdbm;
        }
        if (esn0Val !== null && !isNaN(esn0Val)){
            if (maxEsn0 === null || esn0Val > maxEsn0) maxEsn0 = esn0Val;
        }

        shiftIfNeeded();
        updateCharts();

    }catch(e){ console.error('parse pointing', e); }
}

function startPolling(){
    if (pollInterval) return; // ya corriendo
    // primer poll inmediato
    doPollOnce();
    pollInterval = setInterval(doPollOnce, pollMs);
}
function stopPolling(){ if (pollInterval){ clearInterval(pollInterval); pollInterval = null; } }

// Ensure polling is stopped when closing page
window.addEventListener('beforeunload', ()=>{ stopPolling(); });

</script>
</body>
</html>
