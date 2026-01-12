# WP-CONFIG.PHP FRAGMENT
# Agregar al final de wp-config.php (antes de "That's all, stop editing!")

// ========== STAGING ENVIRONMENT SETUP ==========
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'cobertura.insat.com.ar') !== false) {
    // FORZAR NOINDEX A NIVEL DE WORDPRESS
    define('BLOG_PUBLIC', 0);
    
    // Opcional: Deshabilitar actualizaciones automáticas en staging
    // define('AUTOMATIC_UPDATER_DISABLED', true);
    
    // Debug mode (cambiar a false en producción)
    // define('WP_DEBUG', true);
    // define('WP_DEBUG_DISPLAY', false);
    // define('WP_DEBUG_LOG', true);
}

// ========== PRODUCCIÓN ENVIRONMENT SETUP ==========
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'insat.com.ar') !== false && strpos($_SERVER['HTTP_HOST'] ?? '', 'cobertura') === false) {
    // PERMITIR INDEXACIÓN EN PRODUCCIÓN
    define('BLOG_PUBLIC', 1);
    
    // Deshabilitar debug en producción
    define('WP_DEBUG', false);
    define('WP_DEBUG_DISPLAY', false);
}

// ========== SECURITY HEADERS (AMBOS AMBIENTES) ==========
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

if (!defined('DISALLOW_FILE_MODS')) {
    define('DISALLOW_FILE_MODS', false); // true solo si es necesario
}

// ========== XMLRPC & REST API ==========
if (!defined('XMLRPC_REQUEST')) {
    // XML-RPC está deshabilitado desde functions.php del theme
}

// ========== FIN DE WP-CONFIG.PHP ==========
