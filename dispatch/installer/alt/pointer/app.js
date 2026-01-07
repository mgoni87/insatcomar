// Nuevo app.js (reemplazar el tuyo)
// Objetivo: obtener heading/azimuth y elevación más fiables en móviles.
// Soporta: webkitCompassHeading (iOS), DeviceOrientationEvent, ajuste por orientación de pantalla,
// botón de permiso iOS y calibración manual (offset).

const targetAzimuth = 345; // Grados desde norte
const targetElevation = 48; // Grados desde horizonte
const tolerance = 2; // margen en grados

const video = document.getElementById('camera');
const azimuthDisplay = document.getElementById('azimuth');
const elevationDisplay = document.getElementById('elevation');
const statusDisplay = document.getElementById('status');
const crosshair = document.getElementById('crosshair');
const requestSensorsButton = document.getElementById('request-sensors');
const calibrateButton = document.getElementById('calibrate');

// Offset de calibración (por si quieres ajustar manualmente)
let azimuthOffset = 0;

// Filtrado simple (low-pass)
let smoothedAzimuth = null;
let smoothedElevation = null;
const SMOOTH_FACTOR = 0.12; // 0 = sin suavizado, 1 = sin historial (ajusta al gusto)

// Iniciar cámara trasera
async function initCamera() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' } }
    });
    video.srcObject = stream;
  } catch (err) {
    console.error('Error accediendo a cámara:', err);
    statusDisplay.textContent = 'Error: Permite acceso a cámara';
  }
}

// Util: ángulo pantalla (0/90/180/270)
function getScreenAngle() {
  if (screen && typeof screen.orientation !== 'undefined' && typeof screen.orientation.angle === 'number') {
    return screen.orientation.angle;
  }
  if (typeof window.orientation === 'number') {
    return window.orientation;
  }
  return 0;
}

// Normaliza a 0..360
function norm360(a) {
  return ((a % 360) + 360) % 360;
}

// Suavizado circular para ángulos
function smoothAngle(prev, next, factor) {
  if (prev === null) return next;
  // calcular diferencia mínima en círculo
  let diff = ((next - prev + 540) % 360) - 180;
  return norm360(prev + diff * factor);
}

// Manejar orientaciones
function handleOrientation(event) {
  // === AZIMUTH (HEADING) ===
  let heading = null;
  if (typeof event.webkitCompassHeading === 'number') {
    // Prioridad: webkitCompassHeading (iOS Safari)
    heading = event.webkitCompassHeading;
  } else if (typeof event.alpha === 'number') {
    // Para Android/Otros: alpha es el giro en Z (alrededor del eje vertical)
    // Se usa 360 - alpha para obtener el norte magnético (convención común)
    heading = 360 - event.alpha;
  } else {
    statusDisplay.textContent = 'No hay datos de magnetómetro/giroscopio';
    return;
  }

  // Ajustar por orientación de pantalla
  // Sumamos el ángulo de pantalla (0, 90, 180, 270) para alinear el heading del dispositivo con el marco de referencia de pantalla.
  const screenAngle = getScreenAngle();
  heading = norm360(heading + screenAngle);

  // Aplicar offset de calibración
  heading = norm360(heading + azimuthOffset);

  // === ELEVATION ===
  // beta (eje X, adelante/atrás, -180..180): 0° plano en mesa; 90° vertical
  // gamma (eje Y, lateral, -90..90): 0° sin inclinación lateral

  let beta = typeof event.beta === 'number' ? event.beta : 0;
  let gamma = typeof event.gamma === 'number' ? event.gamma : 0;

  // El ángulo de inclinación para calcular la elevación debe ser corregido por la rotación (gamma) si el dispositivo no está perfectamente vertical.
  // Sin la matriz de rotación completa, usamos una aproximación mejorada para la elevación desde el horizonte.
  
  let elevation = 0;
  
  if (screenAngle === 0) { // Portrait, Home button at bottom
      // La elevación es más o menos 90 - beta. beta=90 es horizontal(horizonte).
      elevation = 90 - beta;
  } else if (screenAngle === 180) { // Portrait, Home button at top
      // Elevación es 90 + beta. beta=-90 es horizontal(horizonte).
      elevation = 90 + beta;
  } else if (screenAngle === 90) { // Landscape, Home button at right
      // Elevación es -gamma (cuando beta es aprox 90). gamma=-90 es horizonte.
      elevation = -gamma;
  } else if (screenAngle === 270) { // Landscape, Home button at left
      // Elevación es gamma (cuando beta es aprox 90). gamma=90 es horizonte.
      elevation = gamma;
  } else {
      elevation = 90 - beta; // Fallback
  }


  // Limitar elevación a un rango sensato
  elevation = Math.max(-90, Math.min(90, elevation));

  // Suavizar lecturas
  smoothedAzimuth = smoothAngle(smoothedAzimuth, heading, SMOOTH_FACTOR);
  if (smoothedElevation === null) smoothedElevation = elevation;
  else smoothedElevation = smoothedElevation + (elevation - smoothedElevation) * SMOOTH_FACTOR;

  // Comparar con objetivo
  const azDiff = Math.min(Math.abs(smoothedAzimuth - targetAzimuth), 360 - Math.abs(smoothedAzimuth - targetAzimuth));
  const elDiff = Math.abs(smoothedElevation - targetElevation);

  if (azDiff <= tolerance && elDiff <= tolerance) {
    statusDisplay.textContent = '¡Alineado! Punto encontrado.';
    crosshair.style.borderColor = 'lime';
  } else {
    let text = '';
    // Azimut
    if (azDiff > tolerance) {
      const delta = ((targetAzimuth - smoothedAzimuth + 540) % 360) - 180;
      text += (delta > 0 ? 'Gira Derecha' : 'Gira Izquierda');
    }
    // Elevación
    if (elDiff > tolerance) {
      if (text.length > 0) text += ' y ';
      text += (smoothedElevation < targetElevation ? 'Inclina Arriba' : 'Inclina Abajo');
    }
    
    // Si no hay texto, es un error (debería tenerlo por el "else")
    if (text.length === 0) text = 'Apunta el teléfono...'; 
    else text += ' (' + azDiff.toFixed(1) + '°, ' + elDiff.toFixed(1) + '°)';
    
    statusDisplay.textContent = text;
    crosshair.style.borderColor = 'red';
  }

  azimuthDisplay.textContent = `Azimut actual: ${smoothedAzimuth.toFixed(1)}°`;
  elevationDisplay.textContent = `Elevación actual: ${smoothedElevation.toFixed(1)}°`;
}

// Manejo de permiso iOS y registro de evento
function setupOrientationListener() {
  // Elegir un solo evento para evitar duplicados
  const orientationEventName = 'ondeviceorientationabsolute' in window ? 'deviceorientationabsolute' : 'deviceorientation';

  // Si existe requestPermission (iOS 13+), mostrar botón para pedir permiso
  if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
    requestSensorsButton.style.display = 'inline-block';
    requestSensorsButton.addEventListener('click', async () => {
      try {
        const perm = await DeviceOrientationEvent.requestPermission();
        if (perm === 'granted') {
          window.addEventListener(orientationEventName, handleOrientation, true);
          requestSensorsButton.style.display = 'none';
          statusDisplay.textContent = 'Sensores activados';
        } else {
          statusDisplay.textContent = 'Permiso a sensores denegado';
        }
      } catch (err) {
        console.error('requestPermission error:', err);
        statusDisplay.textContent = 'Error pidiendo permiso';
      }
    });
  } else {
    // No necesita permiso explícito (Android/otros)
    window.addEventListener(orientationEventName, handleOrientation, true);
  }
}

// Botón calibrar: toma lectura actual y crea offset que haga que "heading" actual apunte a targetAzimuth
calibrateButton.style.display = 'inline-block';
calibrateButton.addEventListener('click', () => {
  if (smoothedAzimuth === null) {
    statusDisplay.textContent = 'Moviendo el teléfono. Esperando lectura.';
    return;
  }
  // Ajuste para que la lectura actual coincida con targetAzimuth
  const desired = targetAzimuth;
  const current = smoothedAzimuth;
  // offset = desired - current (en [0..360])
  azimuthOffset = norm360(desired - current);
  statusDisplay.textContent = `Calibrado. Offset = ${azimuthOffset.toFixed(1)}° aplicado.`;
});

// Manejo de cambio de orientación de pantalla
if (screen && screen.orientation && screen.orientation.addEventListener) {
  screen.orientation.addEventListener('change', () => {
    // reset suavizado para evitar saltos grandes tras rotación
    smoothedAzimuth = null;
    smoothedElevation = null;
  });
} else {
  window.addEventListener('orientationchange', () => {
    smoothedAzimuth = null;
    smoothedElevation = null;
  });
}

// Iniciar
initCamera();
setupOrientationListener();

// Ajuste responsivo video
window.addEventListener('resize', () => {
  video.style.width = '100%';
  video.style.height = '100%';
});