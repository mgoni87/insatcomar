# INSAT Staging - Especificaciones Técnicas Detalladas

**Version**: 1.0  
**Fecha**: 12 de enero de 2026  
**Ambiente**: Staging  
**URL**: https://stag.insat.com.ar

---

## 🖥️ Infraestructura

### Servidor
- **Host**: 149.50.143.84
- **Puerto SSH**: 5156
- **Panel**: cPanel
- **Servidor Web**: Apache httpd
- **Base de Datos**: MariaDB 10.3+
- **PHP**: 8.x
- **SSL**: Let's Encrypt (válido)

### Base de Datos
```
DB Name: stag_insat_wp
DB User: stag_insat
Prefix: wp_
```

---

## 🎨 Frontend Stack

### Tema Principal (Parent)
- **Nombre**: Blocksy
- **Licencia**: GPL v2+

### Tema Child (Personalizado)
- **Nombre**: Blocksy Child - INSAT
- **Versión**: 1.0.0
- **Ubicación**: `/wp-content/themes/blocksy-child/`

#### Archivos del Child Theme
```
style.css
├── Theme metadata header
├── CSS root variables
├── Component styles
│   ├─ Hero section
│   ├─ Cards
│   ├─ Buttons
│   ├─ Forms
│   ├─ Grid system
│   └─ Responsive
└── Media queries

functions.php
├── CPT registration (3x)
├── Taxonomy registration (2x)
├── NOINDEX hooks
├── Feed disabling
└── XML-RPC disabling
```

### CSS Architecture

#### Root Variables
```css
:root {
  --color-primary: #5F0ED5;
  --color-primary-hover: #671AD6;
  --color-bg-dark: #050505;
  --color-bg-secondary: #0B0B0B;
  --color-text-white: #FFFFFF;
  --color-border: rgba(255, 255, 255, 0.12);
  
  /* Typography */
  --font-size-h1: 3.5rem;
  --font-size-h2: 2.5rem;
  --font-size-h3: 1.75rem;
  --font-size-body: 1rem;
  
  /* Spacing */
  --spacing-xs: 0.5rem;
  --spacing-sm: 1rem;
  --spacing-md: 2rem;
  --spacing-lg: 3rem;
}
```

---

## 🔌 Backend Stack

### WordPress
- **Versión**: 6.x
- **Timezone**: America/Argentina/Buenos_Aires
- **Charset**: UTF-8

### Plugins Activos

1. **Disable Feeds** (1.4.4)
   - Desactiva RSS/Atom

2. **Pods** (3.3.4)
   - CPT Management

3. **INSAT Patterns** (1.0.0)
   - Custom Gutenberg patterns

### Custom Post Types

```php
// Novedades
register_post_type('novedades', [
  'public' => true,
  'show_in_rest' => true,
  'has_archive' => true,
  'rewrite' => ['slug' => 'novedades'],
  'supports' => ['title', 'editor', 'thumbnail', 'excerpt']
])

// Tecnología
register_post_type('tecnologia', [...])

// Historias
register_post_type('historias', [...])
```

### Taxonomies
```php
register_taxonomy('cpt-category', [...])
register_taxonomy('cpt-tag', [...])
```

---

## 🔐 Seguridad & SEO

### SEO Safety - 7 Capas NOINDEX

1. **HTTP Header** (.htaccess)
   - X-Robots-Tag: noindex, nofollow

2. **HTML Meta Tag** (wp_head)
   - `<meta name="robots" content="noindex, nofollow">`

3. **WordPress Filter** (functions.php)
   - wp_robots filter

4. **robots.txt**
   - User-agent: * / Disallow: /

5. **.htaccess Sitemap Blocking**
   - Bloquea /sitemap*.xml

6. **Feed Disabling** (functions.php)
   - remove_action()

7. **XML-RPC Disabling** (functions.php)
   - xmlrpc_enabled filter

### Additional Security

#### .htaccess Headers
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

---

## 📐 URL Structure

### Permalink Settings
- **Estructura**: Post name (/%postname%/)

### URL Slugs

**Páginas**
```
/hogares/
/internet-ilimitado/
/tv-satelital/
/wifi-hogar/
/empresa/
/soporte/
/blog/
/faq/
```

**CPT Archives**
```
/novedades/
/tecnologia/
/historias/
```

**Admin**
```
/wp-admin/
/wp-login.php
```

---

## 📋 Maintenance Tasks

### Semanal
- Verificar plugins updates
- Revisar comentarios spam
- Backup de BD

### Mensual
- Actualizar WordPress si hay updates
- Revisar error logs
- Verificar enlaces rotos

### Trimestral
- Auditoría de contenido
- Performance check
- SEO review

---

**Documento Técnico Completo v1.0**
