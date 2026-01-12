#!/bin/bash
# Script para migrar HOME page (ID 61) a Gutenberg blocks
# Ejecutar en: /home/insatcomar/public_html/comprar.insat.com.ar

# Contenido HTML Gutenberg para HOME
read -r -d '' GUTENBERG_CONTENT << 'HEREDOC'
<!-- wp:cover {"url":"https://insat.com.ar/wp-content/uploads/2022/12/cropped-cropped-publicacion_ig_mayor_velocidad.jpg","id":633,"dimRatio":30,"isDark":false} -->
<div class="wp-block-cover is-light" style="background-image:url(https://insat.com.ar/wp-content/uploads/2022/12/cropped-cropped-publicacion_ig_mayor_velocidad.jpg)"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"><strong>Internet Satelital Ilimitado en Argentina</strong></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Internet por satélite al mejor precio</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center","fontSize":"x-large"} -->
<p class="has-text-align-center has-x-large-font-size"><strong>Planes desde $34.999*</strong></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"palette-color-1"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-palette-color-1-background-color wp-element-button" href="https://encuentrainternet.com.ar/redirects/redirect-wsp-asesor.php?mssg=Hola%20EncuentraInternet,%20a%20continuaci%C3%B3n%20enviar%C3%A9%20mi%20ubicaci%C3%B3n%20para%20conocer%20servicios%20de%20Internet%20y%20Cable%20en%20mi%20zona" target="_blank">WhatsApp</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->
<!-- wp:heading {"align":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Por qué elegir INSAT</h2>
<!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong>Soporte Técnico</strong></p>
<!-- /wp:paragraph -->
<p>Contás con soporte técnico especializado en forma ilimitada por WhatsApp siempre que lo necesites y servicio técnico a domicilio</p></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong>Mi INSAT</strong></p>
<!-- /wp:paragraph -->
<p>Para ver tus consumos en tiempo real, adquirir packs, ver y pagar tus facturas. Administrá tu conexión satelital en tiempo real</p></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong>Optimizadores de Cuota</strong></p>
<!-- /wp:paragraph -->
<p>Si tu plan es con cuota optimizá su uso con lo optimizadores para que internet satelital rinda mas</p></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p><strong>Packs Adicionales</strong></p>
<!-- /wp:paragraph -->
<p>Para que adquierás si alcanzás tu cuota en las aplicaciones limitadas de tu banda ancha satelital</p></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HEREDOC

# Cambia al directorio correcto
cd /home/insatcomar/public_html/comprar.insat.com.ar

echo "[1/3] Leyendo contenido actual de página ID 61..."
BACKUP_CONTENT=$(wp post get 61 --field=post_content --allow-root 2>&1 | grep -v "Notice")
echo "✓ Contenido actual respaldado ($(echo "$BACKUP_CONTENT" | wc -c) bytes)"

echo ""
echo "[2/3] Actualizando página con contenido Gutenberg..."
RESULT=$(wp post update 61 --post_content="$GUTENBERG_CONTENT" --allow-root 2>&1 | grep -v "Notice")
echo "✓ $RESULT"

echo ""
echo "[3/3] Verificando actualización..."
UPDATED_CONTENT=$(wp post get 61 --field=post_content --allow-root 2>&1 | grep -v "Notice")
if echo "$UPDATED_CONTENT" | grep -q "wp:cover"; then
  echo "✓ SUCCESS: Contenido actualizado con bloques Gutenberg"
  echo "  - Bloque cover: Sí"
  echo "  - Heading: $(echo "$UPDATED_CONTENT" | grep -c "wp:heading")"
  echo "  - Columnas: $(echo "$UPDATED_CONTENT" | grep -c "wp:column")"
else
  echo "✗ ERROR: No se detectaron bloques Gutenberg"
  exit 1
fi

echo ""
echo "✓ Migración completada. Vista previa en: https://comprar.insat.com.ar/"
