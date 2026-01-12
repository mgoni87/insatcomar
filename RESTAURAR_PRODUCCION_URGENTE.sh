#!/bin/bash
# RESTAURACIÓN RÁPIDA DE PRODUCCIÓN
# Ejecutar EN el servidor si el diagnóstico confirma problema

cd /home/insatcomar/public_html

echo "RESTAURACIÓN EMERGENCY - insat.com.ar"
echo "======================================"

# 1. Verificar y restaurar tema correcto (debe ser theme que estaba funcionando)
echo "[1] Restaurando tema..."
ACTIVE_THEME=$(wp theme list --allow-root 2>&1 | grep "active" | awk '{print $1}' | grep -v "Notice")
echo "Tema activo actual: $ACTIVE_THEME"

# Si el tema activo es "blocksy", REVERTIR A COLIBRI
if [ "$ACTIVE_THEME" = "blocksy" ]; then
  echo "⚠️  Detectado Blocksy activo. Revirtiendo a Colibri..."
  wp theme activate colibri-wp --allow-root 2>&1
  echo "✓ Tema cambiado a Colibri WP"
fi

# 2. Verificar plugins críticos
echo ""
echo "[2] Verificando plugins..."
wp plugin list --allow-root 2>&1 | grep -E "colibri|stackable" | head -10

# 3. Verificar que Colibri Page Builder esté activo
COLIBRI_STATUS=$(wp plugin list --allow-root 2>&1 | grep "colibri-page-builder-pro" | awk '{print $2}')
echo "Estado Colibri Page Builder Pro: $COLIBRI_STATUS"

# 4. Recargar Apache
echo ""
echo "[3] Recargando Apache..."
systemctl reload httpd
sleep 2
echo "✓ Apache reloaded"

# 5. Verificar respuesta HTTP
echo ""
echo "[4] Verificando respuesta de sitio..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://insat.com.ar/)
echo "HTTP Status: $HTTP_CODE"

# 6. Verificar contenido
echo ""
echo "[5] Verificando contenido..."
curl -s https://insat.com.ar/ | head -100 | grep -E "<!DOCTYPE|<title|colibri|blocksy|<body"

echo ""
echo "======================================"
echo "Restauración completada"
echo "Verifica: https://insat.com.ar/"
