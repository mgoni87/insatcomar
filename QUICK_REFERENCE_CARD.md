# ⚡ QUICK REFERENCE CARD
**Migración Blocksy: Comandos y URLs Críticas**

---

## 🔗 URLs CRÍTICAS

| Sitio | URL |
|-------|-----|
| **Home Prod** | http://insat.com.ar/ |
| **Home Staging** | http://insat.com.ar/staging-blocksy/ |
| **Admin Prod** | http://insat.com.ar/wp-admin |
| **Admin Staging** | http://insat.com.ar/staging-blocksy/wp-admin |
| **PageSpeed Prod** | https://pagespeed.web.dev/?url=insat.com.ar |
| **PageSpeed Staging** | https://pagespeed.web.dev/?url=insat.com.ar/staging-blocksy |

---

## 🖥️ SSH CONEXIÓN

```bash
# Conectar
ssh -p5156 root@149.50.143.84

# Una vez conectado
cd /home/insatcomar/public_html/staging-blocksy/
```

---

## 📦 COMANDOS WP-CLI ESENCIALES

```bash
# STAGING - Navegar primero
cd /home/insatcomar/public_html/staging-blocksy/

# Listar temas
wp theme list

# Activar tema
wp theme activate blocksy

# Listar plugins
wp plugin list

# Instalar plugin
wp plugin install stackable-ultimate-gutenberg-blocks --activate

# Verificar BD
wp db info

# Buscar shortcodes Colibri
wp db query --skip-column-names "SELECT ID, post_title FROM wp_posts WHERE post_content LIKE '%[cw-%' AND post_status='publish' LIMIT 10"

# PRODUCCIÓN - Cambiar tema (cuidado!)
cd /home/insatcomar/public_html/
wp theme activate blocksy

# Revertir si hay problemas
wp theme activate colibri-wp
```

---

## 🎨 BLOCKSY CUSTOMIZATION

**Archivo principal:**
```
/home/insatcomar/public_html/staging-blocksy/wp-content/themes/blocksy-child/
├── style.css              ← Importar Blocksy + custom CSS
├── functions.php          ← Enqueue scripts, hooks
├── template-parts/        ← Custom templates
└── assets/
    ├── css/custom.css     ← Estilos personalizados
    └── js/custom.js       ← Scripts personalizados
```

**Agregar CSS custom en blocksy-child/style.css:**
```css
/*
Theme Name: Blocksy Child
Template: blocksy
*/

@import url('../blocksy/style.css');

/* Custom overrides */
body {
  font-family: 'Sistema Font', sans-serif;
}

button {
  background-color: #0070f3;
}
```

---

## 📋 PÁGINAS CRÍTICAS

| # | Página | Slug | Prioridad | Estado |
|---|--------|------|-----------|--------|
| 1 | Home | / | 🔴 Crítica | [ ] |
| 2 | Planes | /planes/ | 🔴 Crítica | [ ] |
| 3 | Cobertura | /cobertura/ | 🔴 Crítica | [ ] |
| 4 | Prepago | /prepago/ | 🟡 Important | [ ] |
| 5 | Costo | /costo/ | 🟡 Important | [ ] |
| 6 | Speedtest | /speedtest/ | 🟡 Important | [ ] |
| 7 | Contacto | /contacto/ | 🟢 Normal | [ ] |

---

## 🔄 SHORTCODE MAPPING

| Colibri | Gutenberg | Notas |
|---------|-----------|-------|
| [cw-section] | Group block | Similar |
| [cw-column] | Column block | Similar |
| [cw-button] | Stackable Button | Mejor |
| [cw-heading] | Heading block | Nativo |
| [cw-icon-list] | Stackable Icon List | Mejor |
| [cw-pricing] | Stackable Pricing | Mejor |
| [cw-faq] | Stackable FAQ | Mejor |
| [cw-text] | Paragraph block | Nativo |
| [cw-image] | Image block | Nativo |

---

## ⚙️ CONFIGURACIÓN CRÍTICA

### BD Staging
```
Nombre: insatcom_wp_staging
Usuario: insatcom_wp
Host: localhost
Prefijo: Ha09PDgeK_
```

### BD Producción
```
Nombre: insatcom_wp
Usuario: insatcom_wp
Host: localhost
Prefijo: wp_
```

### Themes
```
Producción activo: colibri-wp (v1.0.144)
Staging activo: blocksy-child
Staging padre: blocksy
```

### Plugins Críticos
```
colibri-page-builder-pro    → Desactivar antes de desinstalar
smartcrawl-seo              → MANTENER (SEO on-page)
hummingbird-performance     → MANTENER (cache)
wp-smushit                  → MANTENER (images)
```

---

## 🚨 EN CASO DE PROBLEMA

### Staging da error 500

```bash
# 1. Ver error log
tail -50 /var/log/php-fpm.log

# 2. Desactivar todos plugins
wp plugin deactivate --all

# 3. Reactivar uno a uno
wp plugin activate akismet

# 4. Revisar browser console (F12)
```

### Sitio se ve "roto"

```bash
# Probablemente falta CSS
# Editar blocksy-child/style.css y agregar custom CSS

# O revertir tema
wp theme activate colibri-wp
```

### Shortcodes no convierten

```bash
# Colibri está desactivado
# Reactivar temporalmente para ver contenido
wp plugin activate colibri-page-builder-pro

# Luego migrar manualmente a Gutenberg
# Luego desactivar nuevamente
wp plugin deactivate colibri-page-builder-pro
```

---

## ✅ PRE-FLIGHT CHECKLIST

- [ ] SSH conectado
- [ ] Staging accesible en navegador
- [ ] Blocksy activo (wp theme list)
- [ ] BD staging OK (wp db info)
- [ ] Stackable instalado (wp plugin list)
- [ ] No hay errores en console (F12)
- [ ] Mobile responsive OK
- [ ] Home carga sin 404

---

## 📱 TESTING MOBILE

**DevTools (F12):**
1. Abrir DevTools
2. Ctrl+Shift+M (Windows) o Cmd+Shift+M (Mac)
3. Seleccionar dispositivo (iPhone 12, iPad, etc)
4. Probar interacción

**Breakpoints clave:**
```
Mobile:     < 768px
Tablet:     768px - 1024px
Desktop:    > 1024px
```

---

## 🔍 PERFORMANCE TOOLS

| Herramienta | URL | Qué mide |
|-------------|-----|----------|
| **PageSpeed** | pagespeed.web.dev | LCP, FID, CLS |
| **GTmetrix** | gtmetrix.com | Performance grade |
| **WebPageTest** | webpagetest.org | Waterfall + breakdown |
| **Lighthouse** | F12 → Lighthouse | Local testing |

**Objetivos CWV:**
- LCP < 2.5s (Bueno)
- FID < 100ms (Bueno)
- CLS < 0.1 (Bueno)

---

## 📊 VALIDACIÓN SEO

**Verificaciones manuales:**

1. **URL no cambió**
   ```
   Prod: http://insat.com.ar/planes/
   Staging: http://insat.com.ar/staging-blocksy/planes/
   ✅ Path igual
   ```

2. **Meta title intacto**
   ```bash
   # Ver en source (Ctrl+U) o inspector
   <title>Tu título</title>
   ```

3. **Meta description**
   ```
   <meta name="description" content="...">
   ```

4. **Canonical tag**
   ```
   <link rel="canonical" href="https://insat.com.ar/planes/">
   ```

5. **Schema.org**
   ```
   Buscar en HTML source: "schema.org"
   Validar en https://validator.schema.org/
   ```

---

## 🎯 FASE ACTUAL vs PRÓXIMA

**Ahora (Fase 2):**
- ✅ Instalar Stackable
- ✅ Blocksy Companion (opcional)
- ✅ Validación visual básica

**Próximo (Fase 3):**
- [ ] Header/Footer migrar
- [ ] Home convertir a Gutenberg
- [ ] Planes → Speedtest migrar
- [ ] Limpiar shortcodes

---

## 📞 REPORTAR A TEAM

**Template:**

```
🔄 Migración Blocksy - Reporte Diario

Fecha: [hoy]

✅ Completado:
- [ ] Tarea 1
- [ ] Tarea 2

⏳ En progreso:
- [ ] Tarea 3

🚨 Blockeadores:
- [ ] Problema (si hay)

📷 Screenshots:
- [Adjuntar si hay cambios visuales]

Próxima acción: [Qué sigue]
```

---

## 🆘 CONTACTOS ÚTILES

| Recurso | URL |
|---------|-----|
| Blocksy Docs | https://www.blocksy.com/docs |
| Stackable Docs | https://www.stackableco.com/docs |
| Gutenberg Handbook | https://developer.wordpress.org/block-editor/ |
| WP-CLI Docs | https://developer.wordpress.org/cli/commands/ |

---

**Last Updated:** 8 enero 2026  
**Version:** 1.0

