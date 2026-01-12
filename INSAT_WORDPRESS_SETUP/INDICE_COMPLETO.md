# 📦 INSAT WordPress: ÍNDICE COMPLETO

## 🎯 ¿QUÉ SE ENTREGÓ?

### ✅ SETUP COMPLETO STAGING
- HTTP Basic Auth (usuario/password: admin/admin)
- Anti-indexación (X-Robots-Tag, noindex, robots.txt)
- Canónicos apuntan a staging (cobertura.insat.com.ar)
- Bloqueo de sitemaps
- No envía emails
- SEO Safety redundante (7 capas)

### ✅ CHILD THEME BLOCKSY
- Paleta completa: #050505, #FFFFFF, #5F0ED5
- Tipografías (Inter): 400, 600, 700
- Espaciados (móvil-first): xs, sm, md, lg, xl, 2xl, 3xl, 4xl
- Componentes:
  - Buttons (primary, outline, sizes)
  - Cards (hover effects, responsive)
  - Forms (inputs, textareas, labels)
  - Hero fullscreen
  - Alerts, badges, breadcrumbs
- Responsive: Mobile (0-480px), Tablet (481-768px), Desktop (769px+)
- Accesibilidad: Focus visible, contraste 4.5:1, reduced-motion

### ✅ CUSTOM POST TYPES
- **News** (Novedades): `/novedades/`
- **Tech** (Tecnología): `/tecnologia/`
- **Stories** (Historias): `/historias/`

Cada CPT incluye:
- Taxonomías (categorías)
- Metaboxes SEO (meta description, slug custom)
- Soporte comentarios
- Gutenberg editor

### ✅ 7 BLOCK PATTERNS REUTILIZABLES
1. **Hero Fullscreen** - H1, texto, CTAs, overlay radial
2. **Cards Planes** - 3 columnas, precio "desde"
3. **Verificá Cobertura** - Formulario dirección/CP (MVP)
4. **Instalación 3 Pasos** - Numerados, pasos flow
5. **Qué Incluye Kit** - Grid 4 items con logos
6. **Editorial Últimas** - Query últimas 3 novedades
7. **Footer Newsletter** - Columnas, newsletter, links

### ✅ PLANTILLAS
- `archive-insat-news.php` - Listado de novedades
- `single-insat-news.php` - Detalle novedad (breadcrumb, navegación, relacionados)
- *(Templates para Tech y Stories análogos)*

### ✅ DOCUMENTACIÓN COMPLETA
- **README.md** - Guía rápida (instalación en 10 pasos)
- **CHECKLIST_STAGING_SETUP.md** - Setup paso a paso
- **CHECKLIST_PERFORMANCE_ACCESSIBILITY.md** - Validación completa
- **CHECKLIST_PRODUCCION.md** - Migración a producción

---

## 📂 ESTRUCTURA DE CARPETAS

```
INSAT_WORDPRESS_SETUP/
│
├── 📄 README.md
│   └─ Guía rápida + instalación en 10 pasos
│
├── 📋 CHECKLIST_STAGING_SETUP.md
│   └─ Pasos 1-10 para configurar staging (HTTP Auth, noindex, theme, patterns)
│
├── 📋 CHECKLIST_PERFORMANCE_ACCESSIBILITY.md
│   └─ Validación completa: LCP, FID, CLS, contraste, a11y, herramientas
│
├── 📋 CHECKLIST_PRODUCCION.md
│   └─ Pre-launch: seguridad, SEO, analytics, funcionalidad, compatibilidad
│
├── 🔧 blocksy-child/  (COPIAR A: /wp-content/themes/)
│   │
│   ├── style.css
│   │   └─ Imports de CSS files
│   │
│   ├── functions.php
│   │   ├─ Theme setup
│   │   ├─ Security (XMLRPC disabled)
│   │   ├─ Staging detection + noindex + X-Robots-Tag + noindex headers
│   │   ├─ CPT registration (require: inc/cpts.php)
│   │   ├─ Block patterns (require: inc/block-patterns.php)
│   │   └─ Menu registration
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── variables.css
│   │   │   │   └─ CSS custom properties (--color-*, --size-*, --space-*, etc.)
│   │   │   ├── components.css
│   │   │   │   └─ Buttons, cards, forms, hero, sections, grids, badges, alerts
│   │   │   └── responsive.css
│   │   │       └─ Mobile-first (768px, 480px), reduced-motion, print, touch
│   │   └── js/
│   │       └─ (Ready para agregar scripts)
│   │
│   ├── inc/
│   │   ├── cpts.php
│   │   │   ├─ Registra CPTs: news, tech, stories
│   │   │   ├─ Taxonomías por CPT
│   │   │   ├─ Metaboxes SEO (meta-description, custom-slug)
│   │   │   └─ Output meta descriptions en front
│   │   │
│   │   └── block-patterns.php
│   │       ├─ Patrón 1: Hero Fullscreen
│   │       ├─ Patrón 2: Cards Planes (3 col)
│   │       ├─ Patrón 3: Verificá Cobertura
│   │       ├─ Patrón 4: Instalación 3 Pasos
│   │       ├─ Patrón 5: Kit Incluido
│   │       ├─ Patrón 6: Editorial Últimas
│   │       └─ Patrón 7: Footer Newsletter
│   │
│   ├── templates/
│   │   ├── archive-insat-news.php
│   │   │   └─ Grid 3 cols, pagination, card design
│   │   ├── single-insat-news.php
│   │   │   ├─ Breadcrumb
│   │   │   ├─ Meta info (autor, fecha, categoría)
│   │   │   ├─ Featured image
│   │   │   ├─ Contenido + tags
│   │   │   ├─ Navegación prev/next
│   │   │   ├─ Relacionados
│   │   │   └─ Comentarios
│   │   ├── archive-insat-tech.php (idem news)
│   │   ├── single-insat-tech.php (idem news)
│   │   ├── archive-insat-stories.php (idem news)
│   │   └── single-insat-stories.php (idem news)
│   │
│   └── template-parts/
│       ├── header-custom.php (opcional)
│       └── footer-custom.php (opcional)
│
├── 🔐 robots-staging.txt
│   └─ User-agent: *  →  Disallow: /
│
├── 🔐 htaccess-staging.txt
│   ├─ HTTP Basic Auth (<Directory> + AuthType Basic)
│   ├─ X-Robots-Tag header
│   ├─ Bloquea wp-sitemap.*, sitemap.*
│   └─ (COPIAR CONTENIDO A: /cobertura/.htaccess)
│
├── 🔐 wp-config-fragment.php
│   ├─ BLOG_PUBLIC = 0 en staging
│   ├─ BLOG_PUBLIC = 1 en producción
│   ├─ WP_DEBUG = false en producción
│   └─ (AGREGAR CONTENIDO AL FINAL de wp-config.php)
│
└── 🔐 setup-basic-auth.sh
    └─ Script bash para generar .htpasswd en servidor
```

---

## 🚀 PASOS INMEDIATOS

### 1. COPIAR THEME (SFTP o SSH)
```bash
# Opción A: SSH
scp -r -P 5156 blocksy-child root@149.50.143.84:/home/insatcomar/public_html/wp-content/themes/

# Opción B: SFTP (Filezilla)
# Conectar a servidor y copiar carpeta blocksy-child a wp-content/themes/
```

### 2. SETUP BÁSIC AUTH
```bash
ssh -p5156 root@149.50.143.84

htpasswd -c /home/insatcomar/.htpasswd admin
# Password: admin

chmod 644 /home/insatcomar/.htpasswd
chown root:www-data /home/insatcomar/.htpasswd
```

### 3. COPIAR ARCHIVOS STAGING
```bash
# robots.txt
scp -P 5156 robots-staging.txt root@149.50.143.84:/home/insatcomar/public_html/cobertura/robots.txt

# .htaccess (copiar CONTENIDO, no reemplazar)
# Editar manualmente en servidor
```

### 4. WP-CONFIG.PHP
```bash
# Agregar contenido de wp-config-fragment.php al final
# Antes de "That's all, stop editing!"
```

### 5. WP ADMIN
- Activar "Blocksy Child - INSAT" theme
- Configuración → Lectura: Marcar "Disuadir buscadores"
- Crear menús + contenido ejemplo

---

## ✅ VALIDACIONES RÁPIDAS

```bash
# 1. Comprobar HTTP Basic Auth
curl -I https://cobertura.insat.com.ar/
# Respuesta: 401 Unauthorized

# 2. Comprobar con credenciales
curl -u admin:admin -I https://cobertura.insat.com.ar/
# Respuesta: 200 OK

# 3. Comprobar X-Robots-Tag header
curl -u admin:admin -I https://cobertura.insat.com.ar/ | grep "X-Robots-Tag"

# 4. Comprobar meta robots
curl -u admin:admin https://cobertura.insat.com.ar/ | grep "robots"

# 5. Comprobar robots.txt
curl -u admin:admin https://cobertura.insat.com.ar/robots.txt

# 6. WP Admin: Verificar theme activo
# Dashboard → Apariencia → Tema activo = "Blocksy Child - INSAT"

# 7. Gutenberg: Verificar patterns
# Crear página → Gutenberg → Botón "+" → Buscar "INSAT"
```

---

## 🎯 COMPONENTES CSS LISTOS PARA USAR

### Buttons
```html
<button class="btn">Primary</button>
<button class="btn btn-outline">Outline</button>
<button class="btn btn-small">Small</button>
<button class="btn btn-large">Large</button>
<button class="btn btn-full">Full width</button>
```

### Cards
```html
<div class="card">
  <h3>Título</h3>
  <p>Contenido</p>
</div>
```

### Grid
```html
<div class="grid grid-3">
  <div class="card">Item 1</div>
  <div class="card">Item 2</div>
  <div class="card">Item 3</div>
</div>
```

### Hero
```html
<div class="hero">
  <div class="hero-content">
    <h1>Titulo grande</h1>
    <p>Subtítulo</p>
    <div class="hero-buttons">
      <button class="btn">CTA 1</button>
      <button class="btn btn-outline">CTA 2</button>
    </div>
  </div>
</div>
```

### Forms
```html
<div class="form-group">
  <label for="name">Nombre:</label>
  <input id="name" type="text" placeholder="Tu nombre" />
</div>
```

### Alerts
```html
<div class="alert">Mensaje</div>
<div class="alert alert-success">Success!</div>
<div class="alert alert-warning">Warning!</div>
<div class="alert alert-error">Error!</div>
```

---

## 📊 PALETA DE COLORES

| Variable | Hex | Uso |
|----------|-----|-----|
| `--color-dark` | #050505 | Fondo base, text dark |
| `--color-light` | #FFFFFF | Texto base, backgrounds |
| `--color-accent` | #5F0ED5 | CTAs, links, focus |
| `--color-accent-hover` | #671AD6 | CTA hover |
| `--color-border` | rgba(255,255,255,.12) | Bordes cards |
| `--color-text-secondary` | rgba(255,255,255,.7) | Texto secundario |
| `--color-text-tertiary` | rgba(255,255,255,.5) | Texto terciario |

---

## 🎨 TIPOGRAFÍA

| Propiedad | Valor |
|-----------|-------|
| Font Family | Inter (local) |
| Sizes | h1: 3.5rem, h2: 2.5rem, h3: 1.875rem, body: 1rem |
| Weights | 400, 600, 700 |
| Letter Spacing | -0.02em (headings) |

---

## 📱 RESPONSIVE BREAKPOINTS

| Dispositivo | Rango | CSS |
|-------------|-------|-----|
| Mobile | 0-480px | `@media (max-width: 480px)` |
| Tablet | 481-768px | `@media (max-width: 768px)` |
| Desktop | 769px+ | Default |

---

## 🔐 SEGURIDAD STAGING

| Layer | Configuración |
|-------|----------------|
| **HTTP Auth** | BasicAuth (admin/admin) |
| **Meta Robots** | noindex, nofollow, noarchive |
| **X-Robots-Tag** | noindex, nofollow, noarchive, nosnippet |
| **robots.txt** | Disallow: / |
| **Canónicos** | Apuntan a cobertura.insat.com.ar |
| **Sitemaps** | Deshabilitados (410 GONE) |
| **Emails** | Loguean, no se envían |
| **Analytics** | NO conectado a GA producción |

---

## 📈 PERFORMANCE TARGETS

| Métrica | Target | Herramienta |
|---------|--------|-------------|
| Lighthouse Performance | ≥ 90 | Chrome DevTools |
| LCP | < 2.5s | PageSpeed Insights |
| FID | < 100ms | PageSpeed Insights |
| CLS | < 0.1 | PageSpeed Insights |
| Accessibility | ≥ 90 | Chrome DevTools |

---

## 🎯 ARQUITECTURA FINAL (PRODUCCIÓN)

```
insat.com.ar/
├── / (Home) → Patterns + blocks
├── /hogares/
│   ├── /hogares/internet-ilimitado/
│   ├── /hogares/internet-ilimitado-tv/
│   └── /hogares/wifi-plus-mesh/
├── /empresas/
├── /cobertura/ → Mapa + verificador
├── /especificaciones/
├── /soporte/
│   ├── /soporte/preguntas-frecuentes/
│   └── /soporte/evita-estafas/
├── /novedades/ (Archive) + /novedades/{slug}/
├── /tecnologia/ (Archive) + /tecnologia/{slug}/
├── /historias/ (Archive) + /historias/{slug}/
├── /legal/terminos/
├── /legal/privacidad/
└── /legal/cookies/
```

---

## 📞 SOPORTE

| Aspecto | Contacto |
|--------|----------|
| Hosting/SSH | root@149.50.143.84:5156 |
| Dominio | insat.com.ar |
| WordPress Admin | https://cobertura.insat.com.ar/wp-admin/ (con auth) |
| Theme Files | /wp-content/themes/blocksy-child/ |
| Database | Verificar credenciales en wp-config.php |

---

## ✨ LISTO PARA USAR

**Toda la documentación está lista para implementación inmediata.**

👉 **Comenzar por**: [README.md](README.md) → [CHECKLIST_STAGING_SETUP.md](CHECKLIST_STAGING_SETUP.md)
