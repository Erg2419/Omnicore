<?php
// ===================================
// CONFIGURACIÓN DE GOOGLE MAPS API
// ===================================

// REEMPLAZA ESTA KEY CON TU API KEY REAL
define('GOOGLE_MAPS_API_KEY', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCSOqaSuWyrBrXP6Vl35o3cDdzOSD2Dcz8&libraries=places');

// Configuración de base de datos (si la necesitas centralizada)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gm');

// ===================================
// FUNCIÓN PARA OBTENER LA API KEY
// ===================================
function getGoogleMapsApiKey() {
    return GOOGLE_MAPS_API_KEY;
}

// ===================================
// VERIFICAR QUE LA API KEY ESTÁ CONFIGURADA
// ===================================
if(GOOGLE_MAPS_API_KEY === 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCSOqaSuWyrBrXP6Vl35o3cDdzOSD2Dcz8&libraries=places') {
    // Comentar estas líneas después de configurar
    // trigger_error('⚠️ ADVERTENCIA: Debes configurar tu Google Maps API Key en config.php', E_USER_WARNING);
}
?>