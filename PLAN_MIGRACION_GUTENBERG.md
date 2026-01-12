# PLAN DE MIGRACIÓN GUTENBERG - PASO A PASO

## Estado Actual
- Staging: https://comprar.insat.com.ar/
- Tema Activo: Colibri WP (v1.0.144) 
- Plugin Builder: Colibri Page Builder PRO (activo)
- Tema Futuro: Blocksy + Gutenberg + Stackable
- Páginas críticas a migrar: HOME, PLANES, COBERTURA

## FASE 1: MIGRACIÓN HOME PAGE (ID 61)

### Paso 1: Crear backup automático
```bash
# En servidor /home/insatcomar/public_html/comprar.insat.com.ar
wp post get 61 --field=post_content --allow-root > /tmp/page_61_colibri_backup.html
echo "Backup creado: $(wc -c < /tmp/page_61_colibri_backup.html) bytes"
```

### Paso 2: Ejecutar script de migración
```bash
# Ejecutar desde /home/insatcomar/public_html/comprar.insat.com.ar
chmod +x /home/insatcomar/MIGRATION_HOME_GUTENBERG.sh
/home/insatcomar/MIGRATION_HOME_GUTENBERG.sh
```

### Paso 3: Validar en navegador
Abrir: https://comprar.insat.com.ar/
Verificar:
- [ ] Hero section con fondo de imagen
- [ ] Título: "Internet Satelital Ilimitado en Argentina"
- [ ] Subtítulo: "Internet por satélite al mejor precio"
- [ ] Precio: "Planes desde $34.999*"
- [ ] Botón WhatsApp funcional
- [ ] 4 características en columnas

### Paso 4: Buscar problemas de rendering
```bash
# Verificar errores en logs de Apache
tail -100 /var/log/apache2/error_log | grep -i "error\|fatal"

# Verificar errores de WordPress
wp eval 'global $wpdb; $wpdb->show_errors(); echo $wpdb->last_error;' --allow-root
```

## FASE 2: MIGRACIÓN PLANES PAGE (ID 1184)

### Análisis previo
```bash
cd /home/insatcomar/public_html/comprar.insat.com.ar

# Leer estructura actual
echo "=== INFORMACIÓN DE PÁGINA PLANES ==="
wp post get 1184 --format=json --allow-root | jq '.{id,title,status,post_type}'

# Extraer contenido
echo ""
echo "=== CONTENIDO ACTUAL (primeros 500 caracteres) ==="
wp post get 1184 --field=post_content --allow-root | head -c 500
```

### Crear nuevo contenido Gutenberg para PLANES
(Se generará según estructura obtenida en análisis previo)

## FASE 3: MIGRACIÓN COBERTURA PAGE

### Análisis previo
```bash
# Similar a PLANES
wp post get <COBERTURA_ID> --format=json --allow-root | jq '.{id,title,status}'
```

## FASE 4: VALIDACIÓN FINAL (Antes de cambiar a Blocksy)

### Verificar todas las páginas migrables
```bash
cd /home/insatcomar/public_html/comprar.insat.com.ar

# Listar todas las páginas públicas
wp post list --post_type=page --status=publish --format=table --allow-root

# Contar elementos Colibri restantes
echo "Elementos Colibri detectados:"
for page_id in $(wp post list --post_type=page --status=publish --format=ids --allow-root); do
  colibri_count=$(wp post get $page_id --field=post_content --allow-root | grep -c "data-colibri")
  if [ $colibri_count -gt 0 ]; then
    title=$(wp post get $page_id --field=post_title --allow-root)
    echo "  - Página $page_id ($title): $colibri_count componentes Colibri"
  fi
done
```

### Validar respuesta HTTP de todas las páginas
```bash
while IFS= read -r slug; do
  status=$(curl -s -o /dev/null -w "%{http_code}" https://comprar.insat.com.ar/$slug/)
  echo "$slug: HTTP $status"
done << 'EOF'

plans
cobertura
EOF
```

### Verificar velocidad y SEO
```bash
# Meta tags en HOME
curl -s https://comprar.insat.com.ar/ | grep -E "<title>|og:title|description"

# Schema markup
curl -s https://comprar.insat.com.ar/ | grep -c "schema.org"
```

## FASE 5: CAMBIO A BLOCKSY (Después de validar todas las migraciones)

### Cambiar tema en WordPress
```bash
cd /home/insatcomar/public_html/comprar.insat.com.ar
wp theme activate blocksy --allow-root
echo "✓ Tema cambiado a Blocksy"

# Verificar tema activo
wp theme list --allow-root | grep active
```

### Reload Apache
```bash
systemctl reload httpd
sleep 2
echo "✓ Apache reloaded"
```

### Test final
```bash
curl -s https://comprar.insat.com.ar/ | grep -E "blocksy|wp-content/themes"
echo "✓ Blocksy cargado correctamente"
```

## TIMEFRAME ESTIMADO

| Fase | Tarea | Tiempo |
|------|-------|--------|
| 1 | HOME Gutenberg | 10 min |
| 1 | Validar HOME | 5 min |
| 2 | Analizar PLANES | 5 min |
| 2 | PLANES Gutenberg | 10 min |
| 2 | Validar PLANES | 5 min |
| 3 | Analizar COBERTURA | 5 min |
| 3 | COBERTURA Gutenberg | 10 min |
| 3 | Validar COBERTURA | 5 min |
| 4 | Validación Final | 10 min |
| 5 | Switch Blocksy | 5 min |
| **TOTAL** | | **70 min** |

## NOTAS IMPORTANTES

⚠️ **ANTES de ejecutar cualquier migración:**
1. Verificar que Colibri Page Builder PRO esté activo
2. Confirmar que Stackable esté disponible
3. Tener backup de base de datos
4. Avisar a equipo que staging estará en reconstrucción

📋 **DURANTE la migración:**
1. Ejecutar en horario de bajo tráfico
2. Monitorear /var/log/apache2/error_log
3. Refreshear browser con Ctrl+Shift+R (clear cache)
4. Documentar cualquier error encontrado

✅ **DESPUÉS de cada migración:**
1. Verificar visualmente en navegador
2. Confirmar todas las funciones funcionan (botones, links, forms)
3. Validar responsive design (mobile/tablet)
4. Revisar que meta tags estén presentes
