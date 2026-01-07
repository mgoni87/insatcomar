<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover" />
<title>SES-17 Finder (brújula tilt-comp, siempre visible)</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
  :root{--accent:#26a69a;}
  html,body{margin:0;height:100%;background:#000;color:#fff;font-family:Arial,Helvetica,sans-serif;}
  .app{display:flex;flex-direction:column;height:100%;}
  .topbar{display:flex;gap:8px;flex-wrap:wrap;padding:8px;background:rgba(0,0,0,0.6);align-items:center}
  .btn{background:rgba(255,255,255,0.1);padding:6px 10px;border-radius:6px;color:#fff;border:none;font-size:13px;cursor:pointer}
  .pill{font-size:12px;padding:4px 8px;border-radius:999px;background:rgba(255,255,255,0.12)}
  .camera-wrap{position:relative;flex:1;overflow:hidden;}
  video#cam{width:100%;height:100%;object-fit:cover;}
  canvas#overlay{position:absolute;top:0;left:0;width:100%;height:100%;}
  #markerCenter,#markerSat{
    width:28px;height:28px;border-radius:50%;position:absolute;transform:translate(-50%,-50%);
  }
  #markerCenter{border:3px solid var(--accent);}
  #markerSat{border:3px dashed yellow; box-shadow:0 0 10px rgba(255,255,0,.5);}
  .panel{padding:8px;background:rgba(0,0,0,0.5);font-size:13px;line-height:1.5}
  #map{height:190px;}
  /* Indicador sutil cuando está fuera del FOV (al borde) */
  #markerSat.offscreen{background:rgba(255,255,0,0.08);}
  #suggest{margin-top:6px;font-size:13px}
  #suggest strong{color:#9be7ff}
  #toast{position:fixed;left:50%;bottom:20px;transform:translateX(-50%);background:rgba(0,0,0,0.85);color:#fff;padding:10px 14px;border-radius:8px;font-size:13px;opacity:0;transition:opacity .2s}
  #toast.show{opacity:1}
</style>
</head>
<body>
<div class="app">
  <div class="topbar">
    <button id="btnStart" class="btn">Iniciar cámara</button>
    <button id="btnCalibSun" class="btn">Calibrar Sol</button>
    <button id="btnMapCalib" class="btn">Calibrar Mapa</button>
    <button id="btnToggleMap" class="btn">Vista: Satélite</button>
    <span id="qualityPill" class="pill">Señal: —</span>
    <span id="tiltPill" class="pill">Orientación: —</span>
  </div>

  <div class="camera-wrap">
    <video id="cam" autoplay playsinline></video>
    <canvas id="overlay"></canvas>
    <div id="markerCenter"></div>
    <div id="markerSat"></div>
  </div>

  <div class="panel">
    Lat/Lon: <span id="pos">—</span><br>
    Heading: <span id="heading">—</span> • Pitch: <span id="pitch">—</span><br>
    Sat Az: <span id="satAz">—</span> • Sat El: <span id="satEl">—</span><br>
    ΔAz: <span id="dAz">—</span> • ΔEl: <span id="dEl">—</span>
    <div id="suggest">Sugerencias: —</div>
  </div>

  <div id="map"></div>
</div>

<div id="toast"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/suncalc/suncalc.js"></script>
<script>
//================== CONSTANTES ==================//
const SAT_LONG=-67.1, SAT_ALT=35786, EARTH_A=6378.137, EARTH_E2=0.00669437999014;

//================== ESTADO ==================//
let userLat=null, userLon=null, userAlt=0;

// Heading/Pitch (solo brújula, compensado por tilt)
let deviceHeading=0;   // tilt-compensated, 0..360
let devicePitch=0;     // beta - 90 (vertical = 0)
let headingOffset=0;   // offset de calibración (Sol/Mapa)

// Estabilización mínima (sin gyro)
const BUF_N = 7;
const EMA_ALPHA = 0.2;
const DEADBAND_DEG = 0.8;
let buf = [];
let ema = null;
let lastStable = null;

// Mapa y capas
let map, refPoint=null, markerRef=null;
let osmLayer, satLayer, currentBase='osm';

//================== DOM ==================//
const video=document.getElementById('cam'),
      overlay=document.getElementById('overlay'), ctx=overlay.getContext('2d');
const posEl=document.getElementById('pos'),
      headingEl=document.getElementById('heading'),
      pitchEl=document.getElementById('pitch'),
      satAzEl=document.getElementById('satAz'),
      satElEl=document.getElementById('satEl'),
      dAzEl=document.getElementById('dAz'),
      dElEl=document.getElementById('dEl');
const markerCenter=document.getElementById('markerCenter'),
      markerSat=document.getElementById('markerSat');
const qualityPill=document.getElementById('qualityPill'),
      tiltPill=document.getElementById('tiltPill');
const suggestEl=document.getElementById('suggest');
const toastEl=document.getElementById('toast');

//================== UTIL ==================//
const norm360 = v => ((v%360)+360)%360;
const angDiff = (a,b) => ((a-b+540)%360)-180;
function toast(msg){toastEl.textContent=msg;toastEl.classList.add('show');setTimeout(()=>toastEl.classList.remove('show'),1300);}

// Tilt-compensated heading (estable con el equipo vertical)
function tiltCompensatedHeading(alpha, beta, gamma){
  const d2r = Math.PI/180;
  const _z = alpha * d2r, _x = beta * d2r, _y = gamma * d2r;
  const cX=Math.cos(_x), cY=Math.cos(_y), cZ=Math.cos(_z);
  const sX=Math.sin(_x), sY=Math.sin(_y), sZ=Math.sin(_z);
  const Vx = - cZ * sY - sZ * sX * cY;
  const Vy = - sZ * sY + cZ * sX * cY;
  let heading = Math.atan2(Vx, Vy);
  if (heading < 0) heading += 2*Math.PI;
  return heading * 180/Math.PI;
}

// Estabilización (mediana circular + EMA + deadband)
function pushHeading(h){ buf.push(h); if (buf.length > BUF_N) buf.shift(); }
function circMedianDeg(degs){
  let best=0, bestSum=Infinity;
  for(let a=0;a<360;a++){
    let sum=0; for(const d of degs){ sum += Math.abs(angDiff(d,a)); }
    if(sum<bestSum){bestSum=sum;best=a;}
  }
  return best;
}
function getStableHeading(){
  if (buf.length === 0) return deviceHeading||0;
  const med = circMedianDeg(buf);
  ema = (ema==null) ? med : (EMA_ALPHA*med + (1-EMA_ALPHA)*ema);
  if (lastStable==null || Math.abs(angDiff(ema, lastStable)) > DEADBAND_DEG){
    lastStable = ema;
  }
  return norm360(lastStable);
}
function circularStdDeg(arr){
  if(arr.length<5) return 999;
  let sx=0, sy=0;
  for(const d of arr){ sx+=Math.cos(d*Math.PI/180); sy+=Math.sin(d*Math.PI/180); }
  const R = Math.sqrt(sx*sx+sy*sy)/arr.length;
  return Math.sqrt(Math.max(0,-2*Math.log(Math.max(1e-6,R))))*180/Math.PI;
}

//================== CÁMARA / GEO ==================//
function startCamera(){
  navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}})
    .then(s=>video.srcObject=s)
    .catch(()=>toast('No se pudo abrir la cámara'));
}
function startGeolocation(){
  if(!navigator.geolocation){toast('Sin geoloc');return;}
  navigator.geolocation.getCurrentPosition(p=>{
    userLat=p.coords.latitude; userLon=p.coords.longitude; userAlt=p.coords.altitude||0;
    posEl.textContent=`${userLat.toFixed(5)}, ${userLon.toFixed(5)}`;
    initMap();
  }, ()=>toast('Permití ubicación'), {enableHighAccuracy:true});
}

//================== BRÚJULA (solo deviceorientation) ==================//
async function startOrientation(){
  if (typeof DeviceOrientationEvent !== 'undefined' &&
      typeof DeviceOrientationEvent.requestPermission === 'function'){
    try{
      const res = await DeviceOrientationEvent.requestPermission();
      if(res !== 'granted'){ toast('Sin permiso orientación'); return; }
    }catch{ toast('Permiso denegado'); return; }
  }
  window.addEventListener('deviceorientation', ev=>{
    if(ev.alpha==null || ev.beta==null || ev.gamma==null) return;

    const rawHeading = tiltCompensatedHeading(ev.alpha, ev.beta, ev.gamma);
    pushHeading(rawHeading);
    deviceHeading = getStableHeading();     // heading estable (absoluto, sin gyro)
    devicePitch   = (ev.beta - 90);         // vertical = 0°

    // UI: indicadores
    const hShown = norm360(deviceHeading + headingOffset);
    headingEl.textContent = hShown.toFixed(1);
    pitchEl.textContent   = devicePitch.toFixed(1);

    const absPitch = Math.abs(devicePitch);
    if(absPitch <= 20){
      tiltPill.innerHTML = 'Orientación: <b>Vertical OK</b> (compensación activa)';
      tiltPill.style.color = 'lime';
    }else if(absPitch <= 35){
      tiltPill.innerHTML = 'Orientación: <b>Inclinada</b> (podría degradar el rumbo)';
      tiltPill.style.color = 'yellow';
    }else{
      tiltPill.innerHTML = 'Orientación: <b>Muy inclinada</b> (enderezá el teléfono)';
      tiltPill.style.color = 'orange';
    }
  });
}

//================== GEO CÁLCULOS ==================//
function geodeticToEcef(lat,lon,alt){
  const r=Math.PI/180; lat*=r; lon*=r;
  const N=EARTH_A/Math.sqrt(1-EARTH_E2*Math.sin(lat)**2);
  return [(N+alt)*Math.cos(lat)*Math.cos(lon),
          (N+alt)*Math.cos(lat)*Math.sin(lon),
          ((1-EARTH_E2)*N+alt)*Math.sin(lat)];
}
function satEcef(lon){ const r=(EARTH_A+SAT_ALT); lon*=Math.PI/180; return [r*Math.cos(lon), r*Math.sin(lon), 0]; }
function ecefToEnu(x,y,z,lat,lon){
  const obs=geodeticToEcef(lat,lon,userAlt);
  const dx=x-obs[0], dy=y-obs[1], dz=z-obs[2];
  lat*=Math.PI/180; lon*=Math.PI/180;
  const e=-Math.sin(lon)*dx+Math.cos(lon)*dy;
  const n=-Math.cos(lon)*Math.sin(lat)*dx - Math.sin(lat)*Math.sin(lon)*dy + Math.cos(lat)*dz;
  const u=Math.cos(lat)*Math.cos(lon)*dx+Math.cos(lat)*Math.sin(lon)*dy+Math.sin(lat)*dz;
  return [e,n,u];
}
function enuToAzEl(e,n,u){
  return [ norm360(Math.atan2(e,n)*180/Math.PI),
           Math.atan2(u,Math.sqrt(e*e+n*n))*180/Math.PI ];
}

//================== MAPA (OSM ↔ SATÉLITE) ==================//
function initMap(){
  if(map || !userLat) return;

  map = L.map('map').setView([userLat,userLon], 15);

  // Capas base
  osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' });
  satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles © Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
  });

  // Iniciar con OSM
  osmLayer.addTo(map);

  // Marcador de tu ubicación
  L.marker([userLat, userLon]).addTo(map).bindPopup('Tu ubicación');

  // Click para elegir punto de referencia
  map.on('click', e=>{
    if(markerRef) markerRef.remove();
    refPoint=e.latlng;
    markerRef=L.marker(refPoint).addTo(map).bindPopup('Referencia').openPopup();
  });
}
function toggleMapStyle(){
  if(!map) return;
  if(currentBase === 'osm'){
    map.removeLayer(osmLayer); satLayer.addTo(map);
    currentBase = 'sat';
    document.getElementById('btnToggleMap').textContent = 'Vista: Mapa';
  }else{
    map.removeLayer(satLayer); osmLayer.addTo(map);
    currentBase = 'osm';
    document.getElementById('btnToggleMap').textContent = 'Vista: Satélite';
  }
}

//================== CALIDAD + SUGERENCIAS ==================//
function updateQualityAndHints(deltaAz, deltaEl, el, inFov){
  const std = circularStdDeg(buf);
  let qtxt='—', qcolor='#ccc';
  if(std<3){ qtxt='Alta'; qcolor='lime'; }
  else if(std<7){ qtxt='Media'; qcolor='yellow'; }
  else { qtxt='Baja (interferencia)'; qcolor='orange'; }
  qualityPill.innerHTML = `Señal: <b>${qtxt}</b>`;
  qualityPill.style.color = qcolor;

  let tips=[];
  if(std>=7) tips.push('Interferencia: alejate de metal/vehículos, quitá funda magnética, reintentá calibrar.');

  const needAz = (Math.abs(deltaAz)>2) ? `Girá ${deltaAz>0?'+':''}${deltaAz.toFixed(0)}° en azimut` : null;
  const needEl = (Math.abs(deltaEl)>2) ? `Ajustá elevación ${deltaEl>0?'+':''}${deltaEl.toFixed(0)}°` : null;
  if(needAz || needEl) tips.push([needAz,needEl].filter(Boolean).join(' • '));

  if(inFov && Math.abs(deltaAz)<=3 && Math.abs(deltaEl)<=3 && el>-5) {
    tips.push('<strong>¡Alineado!</strong> Superponé los círculos y fijá la antena.');
  }

  suggestEl.innerHTML = 'Sugerencias: ' + (tips.length? tips.join(' · ') : '—');
}

//================== CALIBRACIONES (Sol / Mapa) ==================//
function calibrateSun(){
  if(!userLat){ toast('Ubicación necesaria'); return; }
  const sun = SunCalc.getPosition(new Date(), userLat, userLon);
  const trueAz = norm360(sun.azimuth*180/Math.PI + 180);
  headingOffset = ((trueAz - deviceHeading + 540) % 360) - 180;
  toast('Offset Sol: ' + headingOffset.toFixed(1) + '°');
}
function calibrateMap(){
  if(!refPoint){ toast('Elegí un punto en el mapa'); return; }
  const r=Math.PI/180, dLon=(refPoint.lng-userLon)*r;
  const y=Math.sin(dLon)*Math.cos(refPoint.lat*r);
  const x=Math.cos(userLat*r)*Math.sin(refPoint.lat*r)-Math.sin(userLat*r)*Math.cos(refPoint.lat*r)*Math.cos(dLon);
  const br = norm360(Math.atan2(y,x)*180/Math.PI);
  headingOffset = ((br - deviceHeading + 540) % 360) - 180;
  toast('Offset mapa: ' + headingOffset.toFixed(1) + '°');
}

//================== LOOP RENDER (punto siempre visible) ==================//
function loop(){
  overlay.width=overlay.clientWidth; overlay.height=overlay.clientHeight; ctx.clearRect(0,0,overlay.width,overlay.height);

  // círculo central SIEMPRE visible
  const cx=overlay.width/2, cy=overlay.height/2;
  markerCenter.style.left=cx+'px'; markerCenter.style.top=cy+'px'; markerCenter.style.display='block';

  if(userLat!=null){
    // Posición satélite
    const s=satEcef(SAT_LONG);
    const enu=ecefToEnu(...s,userLat,userLon);
    const [az,el]=enuToAzEl(...enu);
    satAzEl.textContent=az.toFixed(1); satElEl.textContent=el.toFixed(1);

    // Heading corregido con offset (absoluto, sin gyro)
    const hCorr = norm360(deviceHeading + headingOffset);
    const deltaAz = angDiff(az, hCorr);
    const deltaEl = el - devicePitch;

    dAzEl.textContent = deltaAz.toFixed(1);
    dElEl.textContent = deltaEl.toFixed(1);

    // Proyección a pantalla
    const fovH=60, fovV=40;
    let x = cx + (deltaAz/(fovH/2))*(overlay.width/2);
    let y = cy - (deltaEl/(fovV/2))*(overlay.height/2);

    // ¿Está dentro del FOV?
    const inFov = (Math.abs(deltaAz)<=fovH/2 && Math.abs(deltaEl)<=fovV/2 && Math.abs(deltaAz)<90 && el>-5);

    // Clamp para mantener SIEMPRE visible
    const pad = 16;
    const clampedX = Math.min(overlay.width - pad, Math.max(pad, x));
    const clampedY = Math.min(overlay.height - pad, Math.max(pad, y));
    const wasClamped = (Math.abs(clampedX - x) > 1) || (Math.abs(clampedY - y) > 1);

    // Mostrar punto (si entra en FOV va a su posición real; si no, se pega al borde)
    markerSat.style.display='block';
    markerSat.style.left = (wasClamped ? clampedX : x) + 'px';
    markerSat.style.top  = (wasClamped ? clampedY : y) + 'px';
    markerSat.classList.toggle('offscreen', !inFov);

    // Sugerencias
    updateQualityAndHints(deltaAz, deltaEl, el, inFov);
  }

  requestAnimationFrame(loop);
}

//================== EVENTOS ==================//
document.getElementById('btnStart').onclick=()=>{
  startCamera();
  startGeolocation();
  startOrientation();
  loop();
};
document.getElementById('btnCalibSun').onclick=calibrateSun;
document.getElementById('btnMapCalib').onclick=calibrateMap;
document.getElementById('btnToggleMap').onclick=toggleMapStyle;
</script>
</body>
</html>
