document.addEventListener('DOMContentLoaded', () => {
    const getLocationBtn = document.getElementById('getLocation');
    const loadingDiv = document.getElementById('loading');
    const resultsDiv = document.getElementById('results');
    const errorDiv = document.getElementById('error');
    const errorMessageSpan = errorDiv.querySelector('.error-message');

    const latitudeSpan = document.getElementById('latitude');
    const longitudeSpan = document.getElementById('longitude');
    const azimuthSpan = document.getElementById('azimuth');
    const elevationSpan = document.getElementById('elevation');

    // Longitud orbital del satélite SES-17 (67° Oeste)
    const SAT_LONGITUDE = -67.0; // Oeste es negativo

    getLocationBtn.addEventListener('click', () => {
        if ("geolocation" in navigator) {
            loadingDiv.classList.remove('hidden');
            resultsDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    loadingDiv.classList.add('hidden');
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;

                    latitudeSpan.textContent = userLat.toFixed(4);
                    longitudeSpan.textContent = userLon.toFixed(4);

                    const { azimuth, elevation } = calculateSatelliteAngles(userLat, userLon, SAT_LONGITUDE);

                    // Muestra los resultados si son válidos
                    if (!isNaN(azimuth) && !isNaN(elevation)) {
                        azimuthSpan.textContent = azimuth.toFixed(2);
                        elevationSpan.textContent = elevation.toFixed(2);
                        resultsDiv.classList.remove('hidden');
                    } else {
                        // Si el satélite no es visible, muestra un mensaje de error
                        errorDiv.classList.remove('hidden');
                        errorMessageSpan.textContent = "El satélite SES-17 no es visible desde tu ubicación actual.";
                    }
                },
                (error) => {
                    loadingDiv.classList.add('hidden');
                    errorDiv.classList.remove('hidden');
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessageSpan.textContent = "Acceso a la ubicación denegado. Por favor, habilite los permisos de ubicación en su navegador.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessageSpan.textContent = "Información de ubicación no disponible. Intente de nuevo más tarde.";
                            break;
                        case error.TIMEOUT:
                            errorMessageSpan.textContent = "La solicitud para obtener la ubicación ha caducado.";
                            break;
                        default:
                            errorMessageSpan.textContent = `Error desconocido al obtener la ubicación: ${error.message}`;
                            break;
                    }
                }
            );
        } else {
            errorDiv.classList.remove('hidden');
            errorMessageSpan.textContent = "La geolocalización no es compatible con su navegador.";
        }
    });

    /**
     * Calcula el acimut y la elevación para un satélite geoestacionario.
     * Fórmulas basadas en trigonometría esférica para una mayor precisión.
     * @param {number} userLat Latitud del usuario en grados.
     * @param {number} userLon Longitud del usuario en grados.
     * @param {number} satLon Longitud orbital del satélite en grados.
     * @returns {{azimuth: number, elevation: number}} Objeto con acimut y elevación en grados.
     */
    function calculateSatelliteAngles(userLat, userLon, satLon) {
        // Convertir grados a radianes
        const latRad = userLat * Math.PI / 180;
        const lonRad = userLon * Math.PI / 180;
        const satLonRad = satLon * Math.PI / 180;
        
        // Diferencia de longitud
        const deltaLonRad = satLonRad - lonRad;

        // Constantes
        const K = 35786; // Altura de la órbita geoestacionaria en km
        const R = 6371;  // Radio medio de la Tierra en km

        // Cálculo de la elevación (formula simplificada para satélites geoestacionarios)
        const elevationRad = Math.asin((K * Math.cos(latRad) * Math.cos(deltaLonRad) - R) / Math.sqrt(Math.pow(K, 2) + Math.pow(R, 2) - 2 * K * R * Math.cos(latRad) * Math.cos(deltaLonRad)));
        const elevation = elevationRad * 180 / Math.PI;

        // Cálculo del acimut
        const numerator = Math.sin(deltaLonRad);
        const denominator = Math.cos(latRad) * Math.tan(latRad) - Math.sin(latRad) * Math.cos(deltaLonRad);
        const azimuthRad = Math.atan2(numerator, denominator);

        let azimuth = (azimuthRad * 180 / Math.PI + 360) % 360;

        // Si la elevación es negativa, el satélite no es visible (debajo del horizonte)
        if (elevation < 0) {
            azimuth = NaN; 
            elevation = NaN; 
        }

        return { azimuth, elevation };
    }
});