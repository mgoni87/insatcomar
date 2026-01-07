/* main.js - offline-first, sin Chart.js
   Mantiene la lógica de requests a 192.168.1.1 que tenías y dibuja charts con canvas 2D.
   Basado en tu main.js original. */
const API_URL = 'http://192.168.1.1/cgi-bin/cgiclient';
let sessionId = '';
let pointingInterval;
let signalData = [];
let esn0Data = [];
const MAX_POINTS = 80;
let maxSignal = -Infinity;
let maxEsn0 = -Infinity;

function safeNumber(v, fallback = NaN) {
    return (typeof v === 'number' && isFinite(v)) ? v : (typeof v === 'string' && v.trim() !== '' ? Number(v) : fallback);
}

async function sendRequest(data) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `request=${encodeURIComponent(JSON.stringify(data))}`
        });
        if (!response.ok) throw new Error('Error en la respuesta: ' + response.status);
        return await response.json();
    } catch (error) {
        console.error('sendRequest error', error);
        throw error;
    }
}

/* UI wiring (igual que antes) */
document.getElementById('checkConnection').addEventListener('click', async () => {
    try {
        const status = await sendRequest({ FunctionName: 'GetDeviceStatus' });
        const connectionDiv = document.getElementById('connectionStatus');
        if (status.RequestResult && status.RequestResult.Success &&
            status.RequestData &&
            status.RequestData.Ethernet && status.RequestData.Ethernet.State === 'ok') {
            connectionDiv.innerText = 'Conexión confirmada. Selecciona un Beam.';
            document.getElementById('beamSelect').disabled = false;
            document.getElementById('startConfig').disabled = false;
        } else {
            connectionDiv.innerText = 'Conexión no lista. Verifica los estados del módem.';
        }
    } catch (error) {
        document.getElementById('connectionStatus').innerText = 'Error: No se pudo conectar al módem. Verifica el WiFi.';
    }
});

document.getElementById('startConfig').addEventListener('click', async () => {
    const beamId = document.getElementById('beamSelect').value;
    if (!beamId) return alert('Selecciona un Beam');

    try {
        const auth = await sendRequest({
            FunctionName: 'AuthenticatePassword',
            SessionId: '',
            Params: { LoginLevel: 'expert', Password: 's3p' }
        });
        sessionId = auth.RequestData && auth.RequestData.SessionId ? auth.RequestData.SessionId : '';

        const existingBeams = await sendRequest({
            FunctionName: 'GetExistingBeams',
            SessionId: sessionId
        });
        const beams = existingBeams.Beams || [];

        if (!beams.includes(parseInt(beamId))) {
            let beamParams;
            if (beamId === '5') {
                beamParams = {
                    InitialCarrier1: { TimeSliceNumber: 1, Freq: 19551950000, SymbolRate: 380952400, TSMode: 'dvbs2x_timesliced', Polarization: 2, Enabled: true },
                    PointingCarrier1: { Carrier: { TimeSliceNumber: 1, Freq: 19551950000, SymbolRate: 380952400, TSMode: 'dvbs2x_timesliced', Polarization: 2, Enabled: true } },
                    InitialCarrier2: { TimeSliceNumber: 1, Freq: 0, SymbolRate: 0, TSMode: 'dvbs2x', Polarization: 0, Enabled: false },
                    PointingCarrier2: { Carrier: { TimeSliceNumber: 1, Freq: 0, SymbolRate: 0, TSMode: 'dvbs2x_timesliced', Polarization: 2, Enabled: false } },
                    PolarizationSkew: 0, OrbitalDegrees: 67.1, SatLatitudeVariance: 0, MaxSkew: 0,
                    DefaultInitialCarrier: 1, DefaultPointingCarrier: 1, Hemisphere: 'west', TxPolarization: 3,
                    AcuXString: '', BeamName: '', Cost: 0, AutomaticPointingTimeout: 210000, GxtFileName: '', ExclusionZones: [], BeamId: 5
                };
            } else if (beamId === '6') {
                beamParams = {
                    InitialCarrier1: { TimeSliceNumber: 1, Freq: 20007230000, SymbolRate: 276190500, TSMode: 'dvbs2x_timesliced', Polarization: 3, Enabled: true },
                    PointingCarrier1: { Carrier: { TimeSliceNumber: 1, Freq: 20007230000, SymbolRate: 276190500, TSMode: 'dvbs2x_timesliced', Polarization: 3, Enabled: true } },
                    InitialCarrier2: { TimeSliceNumber: 1, Freq: 0, SymbolRate: 0, TSMode: 'dvbs2x', Polarization: 0, Enabled: false },
                    PointingCarrier2: { Carrier: { TimeSliceNumber: 1, Freq: 0, SymbolRate: 0, TSMode: 'dvbs2x', Polarization: 0, Enabled: false } },
                    PolarizationSkew: 0.00, OrbitalDegrees: 67.1, SatLatitudeVariance: 0.00, MaxSkew: 0.00,
                    DefaultInitialCarrier: 1, DefaultPointingCarrier: 1, Hemisphere: 'west', TxPolarization: 3,
                    AcuXString: '', BeamName: '', Cost: 0, AutomaticPointingTimeout: 210000, GxtFileName: '', ExclusionZones: [], BeamId: 6
                };
            }
            await sendRequest({ FunctionName: 'AddBeam', Params: beamParams, SessionId: sessionId });
        }

        await sendRequest({ FunctionName: 'SetActiveODUType', Params: { ODUTypeId: 1 }, SessionId: sessionId });
        await sendRequest({ FunctionName: 'SetActiveBeam', Params: { AutoBeamSelection: false, BeamId: parseInt(beamId) }, SessionId: sessionId });
        await sendRequest({ FunctionName: 'StartPointing', Params: { PointingCarrierId: 1 }, SessionId: sessionId });

        document.getElementById('pointingSection').style.display = 'block';
        initCharts(); // inicializa canvas renderer
        startPolling();
    } catch (error) {
        document.getElementById('finalStatus').innerText = 'Error en configuración: ' + (error.message || error);
    }
});

/* ---------- Simple Canvas Chart renderer (sin dependencias) ---------- */
/* Soporta: líneas con puntos, axis autoscale, max line punteada */
class TinyLineChart {
    constructor(canvas, opts = {}) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.padding = opts.padding || { top: 10, right: 10, bottom: 24, left: 40 };
        this.series = []; // {name, color, data:[], dashed:false}
        this.maxLine = null; // {value, color}
        this._resize();
        window.addEventListener('resize', () => this._resize());
    }
    _resize() {
        // make canvas resolution-aware
        const rect = this.canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        this.canvas.width = Math.max(300, Math.floor(rect.width * dpr));
        this.canvas.height = Math.max(160, Math.floor((rect.height || 220) * dpr));
        this.ctx.setTransform(dpr,0,0,dpr,0,0);
        this.draw();
    }
    setSeries(series) {
        this.series = series.map(s => ({...s, data: (s.data || []).slice()}));
        this.draw();
    }
    updateSeriesData(index, newData) {
        if (!this.series[index]) return;
        this.series[index].data = newData.slice();
        this.draw();
    }
    setMaxLine(value, color = 'rgba(255,0,0,0.8)') {
        this.maxLine = { value, color };
        this.draw();
    }
    clear() {
        this.ctx.clearRect(0,0,this.canvas.width,this.canvas.height);
    }
    _computeBounds() {
        let min = Infinity, max = -Infinity;
        this.series.forEach(s => {
            s.data.forEach(v => {
                if (v == null || isNaN(v)) return;
                min = Math.min(min, v);
                max = Math.max(max, v);
            });
        });
        if (this.maxLine && isFinite(this.maxLine.value)) max = Math.max(max, this.maxLine.value);
        if (!isFinite(min) || !isFinite(max)) { min = 0; max = 1; }
        // add small padding
        if (min === max) { min -= 1; max += 1; }
        const span = max - min;
        min -= span * 0.08;
        max += span * 0.08;
        return {min, max};
    }
    draw() {
        const ctx = this.ctx;
        const w = this.canvas.width / (window.devicePixelRatio || 1);
        const h = this.canvas.height / (window.devicePixelRatio || 1);
        ctx.clearRect(0,0,w,h);
        const pad = this.padding;
        const plotW = w - pad.left - pad.right;
        const plotH = h - pad.top - pad.bottom;
        // axes
        ctx.save();
        ctx.translate(pad.left, pad.top);

        // background grid
        ctx.fillStyle = '#fff';
        ctx.fillRect(0,0,plotW,plotH);

        // bounds
        const {min, max} = this._computeBounds();
        const range = max - min;

        // y grid lines and labels
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textBaseline = 'middle';
        ctx.strokeStyle = '#eee';
        ctx.lineWidth = 1;
        const ticks = 4;
        for (let i = 0; i <= ticks; i++) {
            const y = (i / ticks) * plotH;
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(plotW, y);
            ctx.stroke();
            const val = (max - (i / ticks) * range);
            const label = (Math.round(val * 100) / 100).toString();
            ctx.fillStyle = '#333';
            ctx.fillText(label, -pad.left + 6, y);
        }

        // x axis labels: show a few ticks
        ctx.fillStyle = '#333';
        const maxLen = Math.max(...this.series.map(s => s.data.length));
        const xTicks = 6;
        for (let i = 0; i <= xTicks; i++) {
            const xi = Math.round((i / xTicks) * (Math.max(0, maxLen - 1)));
            const x = (i / xTicks) * plotW;
            ctx.fillText(xi.toString(), x - 6, plotH + 14);
        }

        // draw maxLine if exists
        if (this.maxLine && isFinite(this.maxLine.value)) {
            const y = ((max - this.maxLine.value) / range) * plotH;
            ctx.save();
            ctx.strokeStyle = this.maxLine.color || 'red';
            ctx.setLineDash([6,4]);
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(plotW, y);
            ctx.stroke();
            ctx.restore();
        }

        // draw each series
        this.series.forEach((s) => {
            const data = s.data || [];
            if (!data.length) return;
            ctx.beginPath();
            ctx.lineWidth = 1.8;
            ctx.strokeStyle = s.color || '#000';
            if (s.dashed) ctx.setLineDash([4,3]); else ctx.setLineDash([]);
            for (let i = 0; i < data.length; i++) {
                const val = data[i];
                if (val == null || isNaN(val)) continue;
                const x = (i / Math.max(1, data.length - 1)) * plotW;
                const y = ((max - val) / range) * plotH;
                if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
            }
            ctx.stroke();

            // draw points as small circles
            ctx.setLineDash([]);
            ctx.fillStyle = s.color || '#000';
            for (let i = 0; i < data.length; i++) {
                const val = data[i];
                if (val == null || isNaN(val)) continue;
                const x = (i / Math.max(1, data.length - 1)) * plotW;
                const y = ((max - val) / range) * plotH;
                ctx.beginPath();
                ctx.arc(x, y, 2.4, 0, Math.PI*2);
                ctx.fill();
            }
        });

        ctx.restore();
    }
}

/* charts instances */
let signalChart, esn0Chart;
function initCharts() {
    const signalCanvas = document.getElementById('signalChart');
    const esn0Canvas = document.getElementById('esn0Chart');

    signalChart = new TinyLineChart(signalCanvas);
    esn0Chart = new TinyLineChart(esn0Canvas);

    signalChart.setSeries([
        { name: 'Signal', color: '#2a6cff', data: [] },
        { name: 'Max Signal', color: '#d9534f', data: [], dashed: true }
    ]);
    esn0Chart.setSeries([
        { name: 'EsN0', color: '#2ca02c', data: [] },
        { name: 'Max EsN0', color: '#ff8c00', data: [], dashed: true }
    ]);
}

function pushPoint(arr, v) {
    arr.push(v);
    if (arr.length > MAX_POINTS) arr.shift();
}

/* Polling & update logic (basado en tu original) */
function startPolling() {
    if (pointingInterval) clearInterval(pointingInterval);
    pointingInterval = setInterval(async () => {
        try {
            const status = await sendRequest({ FunctionName: 'GetDeviceStatus' });
            const pointing = await sendRequest({ FunctionName: 'GetPointingState' });

            // extraer valores según tu estructura esperada
            // (ajusta estas rutas si el XML/JSON tiene otro path)
            const satMsg = status.RequestData && status.RequestData.Satellite ? status.RequestData.Satellite.Message : null;
            if (satMsg && satMsg.TextId) {
                const text = (satMsg.TextId || '') + ' ' + (satMsg.Arguments && satMsg.Arguments[0] ? JSON.stringify(satMsg.Arguments[0]) : '');
                document.getElementById('satelliteText').innerText = text;
            }

            // suponemos que GetPointingState devuelve objetos con SignalStrength y Esn0
            let signalVal = NaN, esn0Val = NaN;
            if (pointing && pointing.PointingState) {
                // varias modem apis nombran distintos campos; intentar varias rutas seguras:
                const ps = pointing.PointingState;
                signalVal = safeNumber(ps.SignalStrength ?? ps.Signal ?? ps.RFStrength ?? ps.Signal_dBm, NaN);
                esn0Val = safeNumber(ps.EsNo ?? ps.EsN0 ?? ps.Es_N0 ?? ps.Es_N_0, NaN);
                // si la api devuelve arrays o objetos anidados
                if (isNaN(signalVal) && ps.Carrier && typeof ps.Carrier === 'object') {
                    signalVal = safeNumber(ps.Carrier.SignalStrength ?? ps.Carrier.Signal, NaN);
                }
            }
            // Fallback: intentar leer de status.RequestData demod/metrics si existe
            if (isNaN(signalVal) && status.RequestData && status.RequestData.Demodulator) {
                const d = status.RequestData.Demodulator;
                signalVal = safeNumber(d.SignalStrength ?? d.RFLevel, signalVal);
                esn0Val = safeNumber(d.EsNo ?? d.EsN0, esn0Val);
            }

            // push a arrays (si NaN, push null para gap)
            pushPoint(signalData, isNaN(signalVal) ? null : signalVal);
            pushPoint(esn0Data, isNaN(esn0Val) ? null : esn0Val);

            // actualizar máximos
            const lastSignal = signalData.slice().filter(v => v != null && !isNaN(v));
            const lastEsn0 = esn0Data.slice().filter(v => v != null && !isNaN(v));
            if (lastSignal.length) {
                const m = Math.max(...lastSignal);
                if (m > maxSignal) maxSignal = m;
            }
            if (lastEsn0.length) {
                const m2 = Math.max(...lastEsn0);
                if (m2 > maxEsn0) maxEsn0 = m2;
            }

            // update charts
            if (signalChart) {
                signalChart.setSeries([
                    { name: 'Signal', color: '#2a6cff', data: signalData },
                    { name: 'Max Signal', color: '#d9534f', data: [], dashed: true }
                ]);
                if (isFinite(maxSignal)) signalChart.setMaxLine(maxSignal, 'rgba(217,83,79,0.9)');
            }
            if (esn0Chart) {
                esn0Chart.setSeries([
                    { name: 'EsN0', color: '#2ca02c', data: esn0Data },
                    { name: 'Max EsN0', color: '#ff8c00', data: [], dashed: true }
                ]);
                if (isFinite(maxEsn0)) esn0Chart.setMaxLine(maxEsn0, 'rgba(255,140,0,0.9)');
            }
        } catch (err) {
            console.warn('Polling error', err);
            document.getElementById('finalStatus').innerText = 'Error durante polling: ' + (err.message || err);
        }
    }, 1200); // intervalo ~1.2s (ajustable)
}

document.getElementById('stopPointing').addEventListener('click', async () => {
    try {
        if (pointingInterval) {
            clearInterval(pointingInterval);
            pointingInterval = null;
        }
        // intentar detener en modem
        await sendRequest({ FunctionName: 'StopPointing', SessionId: sessionId });
        document.getElementById('finalStatus').innerText = 'Apuntamiento finalizado.';
    } catch (err) {
        document.getElementById('finalStatus').innerText = 'Error al detener: ' + (err.message || err);
    }
});

/* inicializar charts cuando el DOM esté listo */
window.addEventListener('load', () => {
    // si quieres probar offline sin módem, descomenta la línea siguiente para simular:
    // simulateDemoData();
});

/* demo helper (opcional) */
function simulateDemoData() {
    initCharts();
    let t=0;
    setInterval(() => {
        pushPoint(signalData, Math.sin(t/6)*6 - 50 + (Math.random()*2));
        pushPoint(esn0Data, Math.cos(t/8)*2 + 6 + (Math.random()*0.4));
        t++;
        if (signalChart) {
            signalChart.setSeries([{name:'Signal',color:'#2a6cff',data:signalData}]);
            signalChart.setMaxLine(Math.max(...signalData.filter(v=>v!=null)), 'rgba(217,83,79,0.9)');
        }
        if (esn0Chart) {
            esn0Chart.setSeries([{name:'EsN0',color:'#2ca02c',data:esn0Data}]);
            esn0Chart.setMaxLine(Math.max(...esn0Data.filter(v=>v!=null)), 'rgba(255,140,0,0.9)');
        }
    }, 500);
}
