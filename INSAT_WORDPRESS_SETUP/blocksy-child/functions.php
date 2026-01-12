<?php
/**
 * INSAT Child Theme - Blocksy
 * Staging Security + SEO Safety + Theme Hooks
 */

// ==============================================
// 1. ENVIRONMENT DETECTION
// ==============================================
if (!defined('IS_STAGING')) {
    define('IS_STAGING', strpos($_SERVER['HTTP_HOST'] ?? '', 'cobertura.insat.com.ar') !== false);
}

// ==============================================
// 2. THEME SETUP & CSS LOADING
// ==============================================
add_action('wp_enqueue_scripts', function() {
    // Enqueue child theme stylesheet with correct parent dependency
    wp_enqueue_style('insat-child', get_stylesheet_uri(), ['ct-main-styles-css'], '1.0.0');
});

add_theme_support('responsive-embeds');
add_theme_support('wp-block-styles');
add_theme_support('editor-styles');

// ==============================================
// 3. SECURITY: FORCE NOINDEX IN STAGING
// ==============================================
add_action('wp_head', function() {
    if (IS_STAGING) {
        echo '<!-- SEO SAFETY: STAGING ENVIRONMENT -->' . "\n";
        echo '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">' . "\n";
    }
});

// ==============================================
// 4. SECURITY: X-ROBOTS-TAG HEADER
// ==============================================
add_action('wp', function() {
    if (IS_STAGING) {
        header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex');
    }
});

// ==============================================
// 5. SECURITY: DISABLE SITEMAPS IN STAGING
// ==============================================
add_filter('wp_sitemaps_enabled', function($enable) {
    return IS_STAGING ? false : $enable;
});

// ==============================================
// 6. SECURITY: BLOCK SITEMAP ENDPOINTS
// ==============================================
add_action('template_redirect', function() {
    if (!IS_STAGING) return;

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/wp-sitemap') === 0 || strpos($uri, '/sitemap') === 0) {
        status_header(410);
        wp_die('Sitemaps disabled in staging.');
    }
});

// ==============================================
// 7. SECURITY: CANONICAL POINTS TO STAGING
// ==============================================
add_action('wp_head', function() {
    if (IS_STAGING) {
        $staging_url = 'https://cobertura.insat.com.ar' . $_SERVER['REQUEST_URI'];
        echo '<link rel="canonical" href="' . esc_url($staging_url) . '">' . "\n";
    }
}, 5);

// ==============================================
// 8. SECURITY: NOINDEX SEARCHES & PAGINATION
// ==============================================
add_action('wp_head', function() {
    if (IS_STAGING && (is_search() || (get_query_var('paged') && get_query_var('paged') > 1))) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
    }
});

// ==============================================
// 9. SECURITY: DISABLE XMLRPC
// ==============================================
add_filter('xmlrpc_enabled', '__return_false');

// ==============================================
// 10. SECURITY: DISABLE FEEDS IN STAGING
// ==============================================
add_action('template_redirect', function() {
    if (!IS_STAGING) return;
    if (is_feed() || strpos($_SERVER['REQUEST_URI'] ?? '', '/feed') !== false) {
        wp_die('Feeds disabled in staging.');
    }
});

// ==============================================
// 11. SECURITY: NO EMAILS FROM STAGING
// ==============================================
add_filter('wp_mail', function($atts) {
    if (!IS_STAGING) return $atts;
    error_log('STAGING EMAIL (NOT SENT): ' . $atts['to'] . ' | Subject: ' . $atts['subject']);
    return false;
});

// ==============================================
// 12. SECURITY: ROBOTS.TXT RESPONSE
// ==============================================
add_action('do_robots', function() {
    if (!IS_STAGING) return;
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Disallow: /\n";
    exit;
});

// ==============================================
// 13. REGISTER CUSTOM POST TYPES
// ==============================================
require_once get_stylesheet_directory() . '/inc/cpts.php';

// ==============================================
// 14. REGISTER BLOCK PATTERNS
// ==============================================
require_once get_stylesheet_directory() . '/inc/block-patterns.php';

// ==============================================
// 15. REGISTER MENUS
// ==============================================
add_action('init', function() {
    register_nav_menus([
        'primary' => 'Menú Principal',
        'utility' => 'Menú Utilitario',
        'footer' => 'Menú Footer',
    ]);
});
