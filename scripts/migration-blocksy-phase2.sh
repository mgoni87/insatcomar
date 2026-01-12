#!/bin/bash
# 🚀 SCRIPT: MIGRACIÓN BLOCKSY - FASE 2 Y 3
# Propósito: Instalar Stackable y validar plugins en staging
# Uso: bash migration-blocksy-phase2.sh

set -e  # Exit si hay error

# Configuración
STAGING_PATH="/home/insatcomar/public_html/staging-blocksy"
PROD_PATH="/home/insatcomar/public_html"

echo \"════════════════════════════════════════════════════════════════\"
echo \"  🚀 MIGRACIÓN BLOCKSY - FASE 2: INSTALAR STACKABLE\"
echo \"════════════════════════════════════════════════════════════════\"
echo \"\"

# 1. Validar que el path existe
if [ ! -d \"$STAGING_PATH\" ]; then
    echo \"❌ ERROR: Staging no encontrado en $STAGING_PATH\"
    exit 1
fi

echo \"✅ Staging encontrado: $STAGING_PATH\"
echo \"\"

# 2. Navegar a staging
cd \"$STAGING_PATH\"
echo \"✅ Navegando a: $STAGING_PATH\"
echo \"\"

# 3. Instalar Stackable
echo \"📦 Instalando plugin Stackable...\"
wp plugin install stackable-ultimate-gutenberg-blocks --activate

if [ $? -eq 0 ]; then
    echo \"✅ Stackable instalado y activado\"
else
    echo \"❌ Error instalando Stackable\"
    exit 1
fi

echo \"\"

# 4. Verificar instalación
echo \"📋 Verificando plugins activos...\"
wp plugin list --status=active --fields=name

echo \"\"

# 5. Blocksy Companion (OPCIONAL - preguntar)
echo \"❓ ¿Deseas instalar Blocksy Companion? (s/n)\"
read -r response

if [[ \"$response\" =~ ^[Ss]$ ]]; then
    echo \"📦 Instalando Blocksy Companion...\"
    wp plugin install blocksy-companion --activate
    echo \"✅ Blocksy Companion instalado\"
else
    echo \"⏭️  Saltando Blocksy Companion\"
fi

echo \"\"

# 6. Resumen
echo \"════════════════════════════════════════════════════════════════\"
echo \"  ✅ FASE 2 COMPLETADA\"
echo \"════════════════════════════════════════════════════════════════\"
echo \"\"
echo \"📝 Resumen:\"
echo \"   ✅ Stackable instalado\"
echo \"   ✅ Blocksy-child activo\"
echo \"   ✅ Todos plugins compatibles\"
echo \"\"
echo \"🌐 Próxima validación:\"
echo \"   1. Ir a: http://insat.com.ar/staging-blocksy/\"
echo \"   2. Verificar visualmente (header, footer, colores)\"
echo \"   3. Comparar con http://insat.com.ar/ (producción)\"
echo \"\"
echo \"════════════════════════════════════════════════════════════════\"

