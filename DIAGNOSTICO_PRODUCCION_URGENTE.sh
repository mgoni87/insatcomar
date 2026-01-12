#!/bin/bash
# DIAGNÓSTICO URGENTE - insat.com.ar EN BLANCO
# Ejecutar EN el servidor con: bash DIAGNOSTICO_PRODUCCION_URGENTE.sh

echo "==========================================="
echo "DIAGNÓSTICO CRÍTICO - insat.com.ar"
echo "==========================================="

# Cambiar a raíz
cd /home/insatcomar/public_html

echo ""
echo "[1] Verificar estructura de directorios..."
ls -la | grep -E "^d" | head -15

echo ""
echo "[2] Verificar wp-config.php de PRODUCCIÓN..."
ls -la wp-config.php*
echo ""
echo "--- Primeras 50 líneas de wp-config.php ---"
head -50 wp-config.php | grep -E "DB_|WP_HOME|WP_SITEURL|define|//"

echo ""
echo "[3] Verificar errores en Apache logs..."
echo "--- Errores últimos 50 líneas ---"
tail -50 /var/log/apache2/error_log | grep -v "^$"

echo ""
echo "[4] Estado de temas en PRODUCCIÓN..."
cd /home/insatcomar/public_html
wp theme list --allow-root 2>&1 | grep -v "Notice"

echo ""
echo "[5] Estado de plugins en PRODUCCIÓN..."
wp plugin list --allow-root 2>&1 | head -20

echo ""
echo "[6] Comprobar si HOME page (ID 61) tiene contenido..."
CONTENT_SIZE=$(wp post get 61 --field=post_content --allow-root 2>&1 | wc -c)
echo "Tamaño de contenido page 61: $CONTENT_SIZE bytes"

if [ $CONTENT_SIZE -lt 100 ]; then
  echo "⚠️  ALERTA: Página HOME tiene contenido muy pequeño"
  echo "--- Contenido actual ---"
  wp post get 61 --field=post_content --allow-root 2>&1
fi

echo ""
echo "[7] Verificar WP_HOME y WP_SITEURL..."
wp eval 'echo "WP_HOME: " . WP_HOME . "\n"; echo "WP_SITEURL: " . site_url();' --allow-root 2>&1

echo ""
echo "[8] Comprobar conexión a BD..."
wp eval 'global $wpdb; echo "BD: " . $wpdb->dbname . "\n"; echo "Usuario: " . $wpdb->dbuser . "\n"; $posts = $wpdb->get_var("SELECT COUNT(*) FROM wp_posts WHERE post_type='"'"'page'"'"'"); echo "Páginas en BD: $posts";' --allow-root 2>&1

echo ""
echo "==========================================="
echo "FIN DIAGNÓSTICO"
echo "==========================================="
