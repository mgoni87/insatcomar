# 🎯 PLAN MAESTRO: MIGRACIÓN BLOCKSY
**Estado Final Deseado:** Blocksy + Gutenberg + Stackable en Producción  
**Timeline:** 2 semanas  
**Riesgo General:** 🟡 MEDIO-BAJO (staging mitiga riesgos)

---

## 📍 ESTADO ACTUAL (8 enero 2026)

```
✅ COMPLETADO:
  • Fase 0 — Diagnóstico completo (inventario plugins, temas, riesgos)
  • Staging activo en /staging-blocksy/ con BD independiente
  • Blocksy + blocksy-child instalados en staging
  
⏳ EN PROGRESO:
  • Fase 1 — Validación staging (verificar acceso, estructura)
  
📋 POR HACER:
  • Fase 2 → Instalar Stackable + validar
  • Fase 3 → Migrar contenido visual (header/footer, páginas)
  • Fase 4 → Validación SEO + Performance
  • Fase 5 → Switch a producción + limpieza
```

---

## 🚀 PRÓXIMOS PASOS INMEDIATOS (Esta Semana)

### PASO 1: Instalar Stackable en Staging (30 min)

**Comando:**
```bash
ssh -p5156 root@149.50.143.84
cd /home/insatcomar/public_html/staging-blocksy/

# Instalar Stackable
wp plugin install stackable-ultimate-gutenberg-blocks --activate

# Verificar
wp plugin list | grep stackable
# Resultado esperado: stackable-ultimate-gutenberg-blocks  active
```

**Validación:**
- ✅ Plugin activo en listing
- ✅ Sin errores de PHP

---

### PASO 2: Validar Blocksy Companion (Opcional - 10 min)

**Decisión necesaria:**
Blocksy Companion añade funcionalidades extras (headers pre-diseñados, footers, etc.).

**Recomendación:** Revisar si agrega valor

**Comando (si sí):**
```bash
wp plugin install blocksy-companion --activate
```

**Comando (si no):**
```bash
# No hacer nada, saltar
```

---

### PASO 3: Verificar Visualmente en Navegador (30 min)

**Comparación lado-a-lado:**

| Elemento | Producción | Staging | Estado |
|----------|------------|---------|--------|
| Logo | http://insat.com.ar/ | http://insat.com.ar/staging-blocksy/ | [ ] Verificar |
| Menú | " | " | [ ] Verificar |
| Colores | " | " | [ ] Verificar |
| Footer | " | " | [ ] Verificar |
| Responsive | " | " | [ ] Verificar |

**Herramientas:**
- Abre en 2 pestañas (split screen ideal)
- Usa DevTools (F12) para comparar
- Prueba en mobile (Chrome DevTools → Responsive mode)

---

### PASO 4: Documentar Diferencias (30 min)

**Si hay diferencias de estilo:**
1. Tomar screenshot
2. Anotar qué cambió
3. Decidir si es aceptable o necesita ajuste

**Archivos a editar si hay cambios:**
```
/home/insatcomar/public_html/staging-blocksy/wp-content/themes/blocksy-child/
├── style.css              ← Importa Blocksy + custom CSS
├── functions.php          ← Funciones PHP custom
└── assets/
    ├── css/custom.css     ← Estilos personalizados
    └── js/custom.js       ← Scripts personalizados
```

---

## 📋 FASE 3: Migración de Contenido (Semana 1-2)

### A. Header/Footer Global

**Acciones:**
1. Revisar colibri-wp header.php y footer.php
2. Replicar en Blocksy Customizer o código
3. Asegurar:
   - H1 único (típicamente en logo/home)
   - Nav semántica correcta (`<nav>`, `<ul>`, `<li>`)
   - CTA WhatsApp/Teléfono funcionales
   - Datos legales en footer

**Archivos críticos:**
```
/staging-blocksy/wp-content/themes/blocksy-child/
├── template-parts/
│  ├── header-custom.php       ← Si necesita header custom
│  └── footer-custom.php       ← Si necesita footer custom
└── functions.php               ← Hooks para enqueue scripts
```

---

### B. Páginas Críticas (Prioridad)

| # | Página | Acciones | Tiempo |
|---|--------|----------|--------|
| 1️⃣ | **Home** | Recrear hero, beneficios, CTAs | 2h |
| 2️⃣ | **Planes** | Convertir tabla/cards | 1.5h |
| 3️⃣ | **Cobertura** | Mapa + form + FAQ | 2h |
| 4️⃣ | **Prepago** | Sección + CTA | 1h |
| 5️⃣ | **Costo** | Tabla + info | 1h |
| 6️⃣ | **Speedtest** | Embed + info | 1h |
| 7️⃣ | **Contacto** | Formulario + info | 1h |

**Metodología:**
```
PARA CADA PÁGINA:

1. Abrir en staging (wp-admin → Editar página)
2. Ver contenido actual (Colibri shortcodes)
3. Convertir a Gutenberg bloques:
   - [cw-section] → Blocksy Section / Group block
   - [cw-button] → Stackable Button / Gutenberg Button
   - [cw-icon-list] → Stackable Icon List
   - [cw-pricing] → Stackable Pricing Table
   - Etc.
4. Mantener textos, imágenes, CTAs idénticos
5. Validar visualmente
6. Publicar en staging
7. Comparar con producción (404 si algo falla)
```

---

### C. Limpiar Shortcodes Residuales

**Búsqueda en DB:**
```bash
# Ver posts con shortcodes Colibri
wp db query --skip-column-names \
  "SELECT ID, post_title, post_content FROM wp_posts 
   WHERE post_content LIKE '%[cw-%' 
   AND post_status='publish' LIMIT 20"
```

**Mapeo de conversión:**

| Shortcode Colibri | Equivalente Gutenberg | Notas |
|-------------------|----------------------|-------|
| `[cw-section]` | Blocksy Section / Group | ✅ Similar |
| `[cw-column]` | Blocksy Column / Column block | ✅ Similar |
| `[cw-button]` | Gutenberg Button / Stackable Button | ✅ Mejor |
| `[cw-heading]` | Gutenberg Heading | ✅ Nativo |
| `[cw-icon-list]` | Stackable Icon List | ✅ Mejor |
| `[cw-pricing-table]` | Stackable Pricing Table | ✅ Mejor |
| `[cw-faq]` | Stackable FAQ | ✅ Mejor |
| `[cw-text]` | Gutenberg Paragraph | ✅ Nativo |
| `[cw-image]` | Gutenberg Image | ✅ Nativo |
| `[cw-slider]` | ⚠️ Necesita plugin | Revisar |

---

## 🔐 FASE 4: Validación SEO + Performance

### A. SEO Técnico

```bash
# 1. Revisar que URLs no cambien
http://insat.com.ar/planes/     ← Debe ser igual

# 2. Revisar metas (con Smartcrawl activo)
wp post meta get <POST_ID> _smartcrawl_title
wp post meta get <POST_ID> _smartcrawl_description

# 3. Verificar canonical
wp db query "SELECT post_id, meta_value FROM wp_postmeta 
             WHERE meta_key='_smartcrawl_canonical' LIMIT 5"

# 4. Revisar sitemap
curl http://insat.com.ar/staging-blocksy/sitemap.xml | head -20

# 5. Revisar robots.txt
curl http://insat.com.ar/staging-blocksy/robots.txt
```

### B. Performance + CWV

**Herramientas:**
- PageSpeed Insights: https://pagespeed.web.dev/
- GTmetrix: https://gtmetrix.com/

**Métricas a medir:**
- LCP (Largest Contentful Paint) < 2.5s
- FID (First Input Delay) < 100ms
- CLS (Cumulative Layout Shift) < 0.1
- TTFB (Time to First Byte) < 600ms

**Optimizaciones típicas:**
- Hummingbird: Asegurar cache activo
- WP Smushit: Imágenes optimizadas
- Lazy loading: Nativo en Blocksy
- Minificación CSS/JS: Verificar en Hummingbird

---

## 🚀 FASE 5: Switch a Producción

### A. Pre-Switch Checklist (Producción)

```
- [ ] Backup completo DB prod
      mysqldump -u insatcom_wp -p insatcom_wp > backup_$(date +%Y%m%d).sql
      
- [ ] Backup wp-content/
      tar -czf wp-content_backup_$(date +%Y%m%d).tar.gz wp-content/
      
- [ ] Revisar plugin Colibri versión actual
      wp plugin list | grep colibri
      
- [ ] Anotar opción activa del sitio
      wp theme list --status=active
```

### B. Cambio de Tema (Baja Presión)

```bash
# 1. Activar Blocksy en producción
wp --path=/home/insatcomar/public_html/ theme activate blocksy

# Verificación inmediata
# 2. Ir a http://insat.com.ar/ y verificar

# 3. Si hay problemas, revertir
wp --path=/home/insatcomar/public_html/ theme activate colibri-wp

# 4. Contactar para revisar problema
```

### C. Post-Switch (Primeras 24h)

```
- [ ] Validar homepage carga sin 404
- [ ] Verificar menú visible
- [ ] Revisar colores (Blocksy default vs Colibri custom)
- [ ] Probar en mobile
- [ ] Revisar console del navegador (F12 → Errores JS)
- [ ] Verificar GA firing (GTM console)
- [ ] Revisar search engine indexación (búsqueda site:insat.com.ar)
```

### D. Desinstalación Colibri (Solo si Producción OK)

```bash
# SOLO después de 24-48h sin problemas

# 1. Desactivar plugin
wp plugin deactivate colibri-page-builder-pro

# 2. Opcional: desinstalar
wp plugin delete colibri-page-builder-pro

# 3. Limpiar BD de meta options hu\u00e9rfanas
wp plugin install wp-option-cache-purge --activate
wp plugin deactivate wp-option-cache-purge
wp plugin delete wp-option-cache-purge

# Benchmark: Sitio debería ser más rápido
```

---

## 🎯 TIMELINE RECOMENDADO

```
SEMANA 1:
  Lunes (hoy):    Fase 0 — Diagnóstico ✅
  Martes:         Fase 1 — Validar staging
                  Fase 2 — Instalar Stackable
  Miércoles-Jueves: Fase 3a — Header/Footer + Home
  Viernes:        Fase 3b — Planes + Cobertura

SEMANA 2:
  Lunes:          Fase 3c — Prepago + Costo + Speedtest
  Martes:         Fase 3d — Blog + páginas menores
  Miércoles:      Fase 3e — Limpieza shortcodes
  Jueves:         Fase 4 — SEO + Performance
  Viernes:        Aprobación final staging

SEMANA 3:
  Lunes:          Fase 5 — Switch producción
  Martes-Viernes: Monitoreo + Limpieza
```

---

## ⚡ COMANDOS RÁPIDOS DE REFERENCIA

```bash
# SSH rápido
ssh -p5156 root@149.50.143.84

# Navegar a staging
cd /home/insatcomar/public_html/staging-blocksy/

# Navegar a producción
cd /home/insatcomar/public_html/

# Listar temas
wp theme list

# Activar tema
wp theme activate blocksy

# Listar plugins activos
wp plugin list --status=active

# Instalar plugin
wp plugin install <plugin-name> --activate

# Ver BD info
wp db info

# Hacer query DB
wp db query \"SELECT * FROM wp_options WHERE option_name='siteurl'\"

# Buscar shortcodes
wp db query --skip-column-names \"SELECT ID, post_title FROM wp_posts 
WHERE post_content LIKE '%[cw-%' AND post_status='publish'\"

# Crear backup DB
mysqldump -u insatcom_wp -p insatcom_wp > backup.sql

# Ver logs PHP
tail -f /var/log/php-fpm.log
tail -f /var/log/nginx/error.log
```

---

## 🆘 TROUBLESHOOTING RÁPIDO

### Error 500 al cargar staging

**Causa probable:** Plugin incompatible o PHP error

**Solución:**
```bash
# 1. Desactivar todos plugins
wp --all-with-unplugged plugin deactivate

# 2. Revisar error log
tail -50 /var/log/php-fpm.log

# 3. Reactivar plugins uno a uno
wp plugin activate akismet
# Prueba... luego
wp plugin activate siguiente-plugin
```

### Sitio se ve "roto" después de cambio de tema

**Causa probable:** Estilos Colibri no heredan en Blocksy

**Solución:**
```bash
# 1. Entrar a blocksy-child/style.css
# 2. Agregar custom CSS que sobrescriba

@import url('../blocksy/style.css');

/* Custom overrides */
body {
  font-family: 'Tu Font', sans-serif;
}

.h-container {
  max-width: 1200px;
}
```

### Shortcodes Colibri no se convierten

**Causa probable:** Colibri plugin desactivado

**Solución:**
1. Reactivar plugin temporalmente
2. Exportar página con shortcodes renderizados (convertir a HTML)
3. Manualmente convertir a bloques Gutenberg
4. Desactivar plugin nuevamente

---

## 📞 ESCALAMIENTO

Si encuentras bloqueadores:

1. **Plugin incompatible:** Buscar alternativa en WP.org
2. **Shortcode desconocido:** Googlear `[cw-*] shortcode colibri`
3. **Styling quebrado:** Tomar screenshot, comparar con prod
4. **Performance issue:** Ejecutar PageSpeed Insights

---

## ✅ DEFINICIÓN DE "COMPLETADO"

### Staging Listo (Fase 3-4):
- [ ] Todas páginas críticas visibles
- [ ] Shortcodes convertidos a bloques
- [ ] SEO metas intactas (verificar en HTML)
- [ ] Performance mejorado vs Colibri
- [ ] Tracking funcional (GA/GTM console)
- [ ] Mobile responsive OK
- [ ] 404 errors = 0

### Producción Listo (Fase 5):
- [ ] Tema Blocksy activo
- [ ] Homepage carga sin 404
- [ ] Todas páginas tier 1 accesibles
- [ ] SEO intacto (GSC sin nuevos errores)
- [ ] Analytics disparando eventos
- [ ] Usuarios reportan sitio "más rápido"
- [ ] Colibri desactivado (opcionalmente desinstalado)

---

**Plan Maestro v1.0**  
**Actualizado:** 8 enero 2026  
**Siguiente revisión:** Post-Fase 2

