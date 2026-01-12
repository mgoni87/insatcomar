<?php
/**
 * INSAT Child Theme - Blocksy
 * Staging Security + SEO Safety + Theme Hooks
 */

// ===================================================================
// 1. ENVIRONMENT DETECTION
// ===================================================================
if (!defined('IS_STAGING')) {
    define('IS_STAGING', strpos($_SERVER['HTTP_HOST'] ?? '', 'cobertura.insat.com.ar') !== false);
}

// ===================================================================
// 2. FORCE LOAD CHILD THEME CSS (Blocksy doesn't auto-load child CSS)
// ===================================================================
add_action('wp_enqueue_scripts', function() {
    // Load child theme style.css (which imports all custom CSS)
    wp_enqueue_style(
        'blocksy-child-style',
        get_stylesheet_uri(),
        array('ct-main-styles-css'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );
}, 999); // Priority 999 = load LAST to override

// ===================================================================
// 3. ADD CUSTOM CSS DIRECTLY TO HEAD
// ===================================================================
add_action('wp_head', function() {
    $css_files = array('variables.css', 'components.css', 'responsive.css', 'header-footer.css');
    foreach ($css_files as $file) {
        $path = get_stylesheet_directory() . '/assets/css/' . $file;
        if (file_exists($path)) {
            echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/assets/css/' . $file . '">' . "\n";
        }
    }
});

// ===================================================================
// 4. SECURITY: FORCE NOINDEX IN STAGING
// ===================================================================
add_action('wp_head', function() {
    if (IS_STAGING) {
        echo '<!-- SEO SAFETY: STAGING ENVIRONMENT -->' . "\n";
        echo '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">' . "\n";
    }
});

// ===================================================================
// 5. SECURITY: X-ROBOTS-TAG HEADER
// ===================================================================
add_action('wp', function() {
    if (IS_STAGING) {
        header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex');
    }
});

// ===================================================================
// 6. SECURITY: DISABLE SITEMAPS IN STAGING
// ===================================================================
add_filter('wp_sitemaps_enabled', function($enable) {
    return IS_STAGING ? false : $enable;
});

// ===================================================================
// 7. SECURITY: DISABLE XMLRPC
// ===================================================================
add_filter('xmlrpc_enabled', function() {
    return IS_STAGING ? false : true;
});

// ===================================================================
// 8. THEME SETUP
// ===================================================================
add_theme_support('responsive-embeds');
add_theme_support('wp-block-styles');
add_theme_support('editor-styles');

// Register custom post types if needed
if (file_exists(get_stylesheet_directory() . '/inc/cpts.php')) {
    require_once(get_stylesheet_directory() . '/inc/cpts.php');
}
if (file_exists(get_stylesheet_directory() . '/inc/block-patterns.php')) {
    require_once(get_stylesheet_directory() . '/inc/block-patterns.php');
}
