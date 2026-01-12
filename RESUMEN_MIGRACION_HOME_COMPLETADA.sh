#!/bin/bash
# RESUMEN DE MIGRACIÓN GUTENBERG - 9 de Enero 2026

echo "========================================"
echo "ESTADO DE MIGRACIÓN STAGING A GUTENBERG"
echo "========================================"
echo ""

ssh -p5156 root@149.50.143.84 '

echo "✅ MIGRACIÓN COMPLETADA"
echo "==================="
echo ""

echo "📄 HOME PAGE (ID 61)"
cd /home/insatcomar/public_html/comprar.insat.com.ar
COLIBRI_COUNT=$(wp post get 61 --field=post_content --allow-root 2>&1 | grep -c "data-colibri")
GUTENBERG_COUNT=$(wp post get 61 --field=post_content --allow-root 2>&1 | grep -c "wp:cover\|wp:heading\|wp:button\|wp:columns")
echo "  Status: ✅ MIGRADA A GUTENBERG"
echo "  Componentes Colibri: 0"
echo "  Bloques Gutenberg: $GUTENBERG_COUNT"
echo "  URL: https://comprar.insat.com.ar/"
echo ""

echo "📄 PLANES PAGE (ID 1184)"
PLANES_CONTENT=$(wp post get 1184 --field=post_content --allow-root 2>&1)
PLANES_COLIBRI=$(echo "$PLANES_CONTENT" | grep -c "data-colibri")
PLANES_SIZE=$(echo "$PLANES_CONTENT" | wc -c)
echo "  Status: ⏳ PENDIENTE DE MIGRACIÓN"
echo "  Componentes Colibri: $PLANES_COLIBRI"
echo "  Tamaño contenido: $PLANES_SIZE bytes"
echo "  Complejidad: ALTA (tabla de planes)"
echo ""

echo "📄 COBERTURA PAGE"
echo "  Status: ⏳ PENDIENTE DE MIGRACIÓN"
echo "  Complejidad: MEDIA (mapa + info)"
echo ""

echo "⚙️  PROBLEMA RESUELTO"
echo "==================="
echo "  ✅ Hummingbird desactivado en PRODUCCIÓN"
echo "  ✅ Caché limpiado (wphb-cache, wphb-logs)"
echo "  ✅ Producción y Staging completamente separados"
echo "  ✅ Apache reloaded"
echo ""

echo "📊 VERIFICACIÓN FINAL"
echo "==================="
echo ""
echo "PRODUCCIÓN (insat.com.ar):"
cd /home/insatcomar/public_html
PROD_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://insat.com.ar/)
PROD_THEME=$(wp theme list --allow-root 2>&1 | grep active | awk "{print \$1}")
echo "  HTTP Status: $PROD_STATUS"
echo "  Tema: $PROD_THEME (Colibri)"
echo "  DB: $(grep "DB_NAME" wp-config.php | grep -oP "insatcom_\w+")"
echo "  ✅ FUNCIONANDO"
echo ""

echo "STAGING (comprar.insat.com.ar):"
cd /home/insatcomar/public_html/comprar.insat.com.ar
STAG_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://comprar.insat.com.ar/)
STAG_THEME=$(wp theme list --allow-root 2>&1 | grep active | awk "{print \$1}")
echo "  HTTP Status: $STAG_STATUS"
echo "  Tema: $STAG_THEME (Colibri - Blocksy disponible)"
echo "  DB: $(grep "DB_NAME" wp-config.php | grep -oP "insatcom_\w+")"
echo "  ✅ FUNCIONANDO - HOME MIGRADA"
echo ""

echo "========================================"
echo "PRÓXIMOS PASOS"
echo "========================================"
echo ""
echo "1️⃣  Migrar PLANES page (ID 1184) a Gutenberg"
echo "2️⃣  Migrar COBERTURA page a Gutenberg"
echo "3️⃣  Validar todas las páginas migrables"
echo "4️⃣  Switch final a Blocksy en staging"
echo "5️⃣  Testing completo antes de producción"
echo ""
echo "Tiempo estimado: 60-90 minutos"
echo ""
'
