# 🚀 GUÍA RÁPIDA: INSAT WordPress Setup

## 📂 ESTRUCTURA ENTREGADA

```
INSAT_WORDPRESS_SETUP/
├── blocksy-child/                    (COPIAR A: wp-content/themes/)
│   ├── style.css
│   ├── functions.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── variables.css         (Paleta #050505, #5F0ED5, etc.)
│   │   │   ├── components.css        (Buttons, cards, forms)
│   │   │   └── responsive.css        (Mobile, tablet, desktop)
│   │   └── js/
│   ├── inc/
│   │   ├── cpts.php                  (News, Tech, Stories)
│   │   └── block-patterns.php        (7 patterns Gutenberg)
│   ├── templates/
│   │   ├── archive-insat-news.php
│   │   └── single-insat-news.php
│   └── template-parts/
│
├── robots-staging.txt                (COPIAR A: /robots.txt en staging)
├── htaccess-staging.txt              (COPIAR CONTENIDO A: .htaccess)
├── wp-config-fragment.php            (AGREGAR CONTENIDO A: wp-config.php)
├── setup-basic-auth.sh               (RUN EN SERVIDOR)
│
├── CHECKLIST_STAGING_SETUP.md        (Pasos 1-10 para setup)
├── CHECKLIST_PERFORMANCE_ACCESSIBILITY.md
└── CHECKLIST_PRODUCCION.md
```

---

## ⚡ INSTALACIÓN RÁPIDA (10 PASOS)

### PASO 1: COPIAR THEME
```bash
# En servidor o SFTP:
cp -r blocksy-child /home/insatcomar/public_html/wp-content/themes/
```

### PASO 2: HTTP BASIC AUTH
```bash
# SSH al servidor:
ssh -p5156 root@149.50.143.84

# Generar .htpasswd:
htpasswd -c /home/insatcomar/.htpasswd admin
# Password: admin

chmod 644 /home/insatcomar/.htpasswd
chown root:www-data /home/insatcomar/.htpasswd
```

### PASO 3: CONFIGURAR .HTACCESS
```bash
# Copiar contenido de htaccess-staging.txt a:
# /home/insatcomar/public_html/cobertura/.htaccess
```

### PASO 4: ROBOTS.TXT
```bash
# Copiar robots-staging.txt a:
# /home/insatcomar/public_html/cobertura/robots.txt
```

### PASO 5: WP-CONFIG.PHP
```bash
# Agregar contenido de wp-config-fragment.php al final de:
# /home/insatcomar/public_html/wp-config.php
# (Antes de "That's all, stop editing!")
```

### PASO 6: ACTIVAR THEME
- WP Admin → Apariencia → Temas
- Activar "Blocksy Child - INSAT"

### PASO 7: DESACTIVAR INDEXACIÓN
- WP Admin → Configuración → Lectura
- Marcar "Disuadir a los motores de búsqueda"

### PASO 8: VERIFICAR PATTERNS
- Crear página de prueba
- Gutenberg → Botón "+" → Buscar "INSAT"
- Deben aparecer 7 patterns

### PASO 9: TEST STAGING
```bash
# Test 1: Debe pedir credenciales
curl -I https://cobertura.insat.com.ar/

# Test 2: Con credenciales, debe permitir
curl -u admin:admin -I https://cobertura.insat.com.ar/

# Test 3: Verificar noindex en meta
curl -u admin:admin https://cobertura.insat.com.ar/ | grep "noindex"

# Test 4: Verificar header
curl -u admin:admin -I https://cobertura.insat.com.ar/ | grep "X-Robots-Tag"
```

### PASO 10: CREAR CONTENIDO
- WP Admin → Novedades: crear 2-3 posts
- WP Admin → Tecnología: crear 2-3 posts
- WP Admin → Historias: crear 2-3 posts

---

## 🎨 VARIABLES CSS (PARA CUSTOMIZAR)

Archivo: `blocksy-child/assets/css/variables.css`

```css
:root {
  /* Cambiar estos valores */
  --color-dark: #050505;           /* Fondo base */
  --color-light: #FFFFFF;          /* Texto base */
  --color-accent: #5F0ED5;         /* CTAs, links */
  --color-accent-hover: #671AD6;   /* CTA hover */
  
  /* Tipografía */
  --size-h1: 3.5rem;
  --size-h2: 2.5rem;
  --size-h3: 1.875rem;
  
  /* Espaciados */
  --space-md: 1rem;
  --space-lg: 1.5rem;
  --space-xl: 2rem;
  --space-2xl: 3rem;
}
```

---

## 🧩 USAR PATTERNS EN GUTENBERG

1. **Crear nueva página**
2. **Gutenberg → Botón "+"**
3. **Buscar "INSAT"**
4. **Seleccionar patrón:**
   - Hero Fullscreen
   - Cards Planes
   - Verificá Cobertura
   - Instalación en 3 Pasos
   - Qué Incluye el Kit
   - Editorial
   - Footer Newsletter

5. **Editar contenido directamente en Gutenberg**
6. **Publicar**

---

## 📱 PUNTOS DE QUIEBRE (BREAKPOINTS)

```css
/* Desktop: 1024px+ (default) */
/* Tablet: 481-768px */
/* Mobile: 0-480px */

/* Respetar estas reglas en custom CSS */
@media (max-width: 768px) { /* TABLET */ }
@media (max-width: 480px) { /* MOBILE */ }
```

---

## 🔐 CREDENCIALES STAGING ACTUAL

```
URL: https://cobertura.insat.com.ar/
Usuario: admin
Password: admin

⚠️ CAMBIAR ANTES DE COMPARTIR CON CLIENTE
```

---

## 🚫 VERIFICAR NO-INDEXING

```bash
# 1. HTTP Header X-Robots-Tag
curl -u admin:admin -I https://cobertura.insat.com.ar/ | grep "X-Robots-Tag"
# Respuesta: X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex

# 2. Meta robots
curl -u admin:admin https://cobertura.insat.com.ar/ | grep -i "robots"
# Respuesta: <meta name="robots" content="noindex, nofollow...

# 3. Robots.txt
curl -u admin:admin https://cobertura.insat.com.ar/robots.txt
# Respuesta: User-agent: * \n Disallow: /

# 4. WP Configuración
# WP Admin → Configuración → Lectura
# "Disuadir a los motores de búsqueda" → MARCADO

# 5. Verificar en Google
# site:cobertura.insat.com.ar
# NO debe aparecer ningún resultado
```

---

## 📊 MONITOREO POST-DEPLOY

### Lighthouse
```
Performance:     ≥ 90
Accessibility:   ≥ 90
Best Practices:  ≥ 90
SEO:            ≥ 90
```

### Core Web Vitals
```
LCP (Largest Contentful Paint):      < 2.5s
FID (First Input Delay):              < 100ms
CLS (Cumulative Layout Shift):        < 0.1
```

### Tools
- Chrome DevTools Lighthouse
- https://pagespeed.web.dev
- https://web.dev/measure

---

## 🐛 TROUBLESHOOTING

### Patterns no aparecen en Gutenberg
```
1. Verificar que inc/block-patterns.php existe
2. Verificar que functions.php carga: require_once get_stylesheet_directory() . '/inc/block-patterns.php';
3. Ir a WP Admin → Apariencia → Editor (FSE)
4. Buscar "INSAT"
5. Si nada: Activar/desactivar theme
```

### HTTP Basic Auth no funciona
```
1. Verificar .htpasswd existe: /home/insatcomar/.htpasswd
2. Verificar ruta en .htaccess: AuthUserFile /home/insatcomar/.htpasswd
3. Verificar permisos: chmod 644 /home/insatcomar/.htpasswd
4. Verificar archivo .htaccess está correcto
5. Reiniciar Apache: systemctl restart httpd (o apache2)
```

### Meta robots noindex no aparece
```
1. Verificar IS_STAGING detecta correctamente:
   php -r "echo strpos($_SERVER['HTTP_HOST'] ?? '', 'cobertura.insat.com.ar') !== false ? 'YES' : 'NO';"
2. Verificar functions.php tiene add_action('wp_head'...)
3. Hacer: wp cache flush
4. Refrescar navegador (Ctrl+F5 hard refresh)
```

### Formularios envían emails en staging
```
1. Verificar función add_filter('wp_mail'...) en functions.php
2. Verificar que wp_mail() devuelve false
3. Revisar logs: tail /var/log/php/error.log
4. Emails deben ser loguados NO enviados
```

---

## ✅ ANTES DE MOSTRAR A CLIENTE

- [ ] Cambiar admin/admin a credencial fuerte
- [ ] Crear contenido de ejemplo (posts, páginas)
- [ ] Verificar que NO indexa (Google site: search)
- [ ] Test responsive en mobile real
- [ ] Lighthouse scores ≥ 90 en todo
- [ ] Verificar emails NO se envían
- [ ] Crear documento "Guía de uso WP Admin" para cliente

---

## 📞 CONTACTO TÉCNICO

**Soporte INSAT Digital**: soporte@insat.com.ar
**Servidor SSH**: root@149.50.143.84 (puerto 5156)
**Dominios**: 
- Staging: https://cobertura.insat.com.ar (con auth)
- Producción: https://insat.com.ar (pública, sin auth)

---

## 🎯 TIMELINE TÍPICO

| Tarea | Tiempo | Estado |
|-------|--------|--------|
| Setup Staging | 30 min | |
| Copiar Theme | 10 min | |
| CPT + Patterns | 15 min | |
| Contenido ejemplo | 1 hour | |
| Testing + fixes | 1-2 hours | |
| **TOTAL** | **3-4 horas** | |

---

**Documentación completa**: Ver archivos CHECKLIST_*.md
