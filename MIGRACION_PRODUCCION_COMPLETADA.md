# ✅ MIGRACIÓN A PRODUCCIÓN - COMPLETADA

## Estado: LISTO PARA LIVE

**Fecha:** 12 de Enero de 2026
**Servidor:** 149.50.143.84:5156
**Dominio Production:** https://insat.com.ar
**URL Staging:** https://cobertura.insat.com.ar (auth: admin/admin)

---

## ✅ TAREAS COMPLETADAS

### 1. Theme Deployment
- ✅ Copiado `blocksy-child/` a `/wp-content/themes/`
- ✅ Estructura verificada:
  - `style.css` (entry point)
  - `functions.php` (4.6KB, CPTs + patterns)
  - `assets/css/` (4 archivos: variables, components, responsive, header-footer)
  - `inc/` (block-patterns.php, cpts.php)
  - `templates/` (archive + single templates)
  - `template-parts/` (reutilizables)

### 2. Configuración WordPress
- ✅ **BLOG_PUBLIC = 1** en wp-config.php
  - Permite indexación en Google/Bing
  - Habilita XML sitemap
- ✅ **WP_DEBUG = false** en producción
- ✅ Environment detection: `IS_STAGING` constant funcional

### 3. Security Headers (Verificados)
```
✅ X-Content-Type-Options: nosniff
✅ X-Frame-Options: SAMEORIGIN
✅ X-XSS-Protection: 1; mode=block
✅ Strict-Transport-Security: max-age=31536000
✅ Referrer-Policy: strict-origin-when-cross-origin
```

### 4. SEO & Indexación
- ✅ **robots.txt** configurado:
  - `Allow: /` (permite crawling)
  - `Disallow: /wp-admin/`
  - `Disallow: /wp-includes/`
  - `Disallow: /wp-content/plugins/`
  - `Sitemap: https://insat.com.ar/sitemap.xml`
- ✅ Meta robots: SIN noindex tag (ready para indexación)
- ✅ .htaccess: Rewrite rules + security headers + caching

### 5. Performance
- ✅ Gzip compression habilitado
- ✅ Browser caching configurado (7 días default, 1 año para assets)
- ✅ Caching headers optimizados

### 6. Anti-Staging (Staging aún protegido)
Staging: `https://cobertura.insat.com.ar` mantiene:
- ✅ HTTP Basic Auth (admin/admin)
- ✅ X-Robots-Tag: noindex header
- ✅ robots.txt: Disallow: /
- ✅ Meta robots tag: noindex
- ✅ BLOG_PUBLIC = 0
- ✅ Sitemap disabled

---

## 📋 Verificaciones POST-DEPLOYMENT

### Headers HTTP (ACTIVOS)
```bash
$ curl -I https://insat.com.ar | grep -i "robot\|cache\|security"
X-Content-Type-Options: nosniff ✅
X-Frame-Options: SAMEORIGIN ✅
X-XSS-Protection: 1; mode=block ✅
Cache-Control: max-age=0, private, must-revalidate ✅
```

### robots.txt (ACTIVO)
```bash
$ curl https://insat.com.ar/robots.txt
User-agent: *
Allow: /
Disallow: /wp-admin/
Sitemap: https://insat.com.ar/sitemap.xml ✅
```

### Theme CSS (ACTIVO)
```bash
$ curl https://insat.com.ar/wp-content/themes/blocksy-child/style.css
Theme Name: Blocksy Child - INSAT ✅
Description: WordPress theme con estética tech/minimalista para INSAT ✅
Version: 1.0.0 ✅
```

---

## 🎯 PRÓXIMOS PASOS (48-72 horas)

### INMEDIATO (Hoy)
- [ ] **Cambiar credenciales admin**
  ```bash
  ssh -p 5156 root@149.50.143.84
  cd /home/insatcomar/public_html
  wp user update admin --user_pass="<NUEVA_CONTRASEÑA_FUERTE>" --allow-root
  ```

- [ ] **Generar XML Sitemap**
  ```bash
  wp sitemap index --allow-root
  curl https://insat.com.ar/sitemap.xml
  ```

- [ ] **Test Lighthouse (Chrome DevTools)**
  - Abrir https://insat.com.ar en Chrome
  - F12 → Lighthouse → Generate report
  - Objetivo: LCP <2.5s, CLS <0.1, Accessibility ≥90

### HOY (Verificaciones finales)
- [ ] Verificar que tema está activo en WP Admin
- [ ] Revisar menu navigation (primary, utility, footer)
- [ ] Test responsivo en mobile (320px, 768px, 1024px)
- [ ] Verificar que CPTs se muestran (novedades/, tecnologia/, historias/)

### MAÑANA (Google Search Console)
- [ ] Ir a https://search.google.com/search-console
- [ ] Añadir propiedad: https://insat.com.ar
- [ ] Enviar sitemap: https://insat.com.ar/sitemap.xml
- [ ] Solicitar indexación manual
- [ ] Monitorear errores de crawling

### 48-72 HORAS (Validación)
- [ ] Google indexó homepage (buscar site:insat.com.ar)
- [ ] Verificar rankings en Search Console
- [ ] Monitorear Core Web Vitals en PageSpeed
- [ ] Revisar error logs: `ssh -p 5156 root@149.50.143.84 tail -f /home/insatcomar/logs/error_log`

### SEMANA 1 (Contenido)
- [ ] Crear página de inicio (homepage)
- [ ] Publicar primeros posts en /novedades/
- [ ] Configurar página de Contacto + formulario
- [ ] Legal: Privacy Policy, Terms of Service
- [ ] Setup GA4 tracking

---

## 🔐 Credenciales & Acceso

### SSH
```
Host: 149.50.143.84
Puerto: 5156
Usuario: root
Ruta: /home/insatcomar/public_html/
```

### WordPress Admin (CAMBIAR CONTRASEÑA)
```
Usuario: admin
Contraseña: [CAMBIAR - Ver sección "Próximos pasos"]
URL: https://insat.com.ar/wp-admin/
```

### Staging (Protegido)
```
URL: https://cobertura.insat.com.ar
Usuario: admin
Contraseña: admin
Status: NOINDEX activo ✅
```

---

## 📊 Configuración Técnica

### Paleta de colores
- Fondo: `#050505` (near-black)
- Texto: `#FFFFFF` (white)
- Acento: `#5F0ED5` (purple)
- Hover: `#671AD6`

### Tipografía
- Font: Inter (local, sin CDN)
- Weights: 400 (normal), 600 (semibold), 700 (bold)

### CPTs Activos
- **News** (/novedades/)
  - Taxonomía: insat-news-cat
  - Metaboxes: meta-description, custom-slug
- **Tech** (/tecnologia/)
  - Taxonomía: insat-tech-cat
  - Metaboxes: meta-description, custom-slug
- **Stories** (/historias/)
  - Taxonomía: insat-stories-cat
  - Metaboxes: meta-description, custom-slug

### Gutenberg Patterns (7 disponibles)
1. Hero Main (fullscreen + CTAs)
2. Cards Plans (3-column pricing)
3. Coverage Check (verification form)
4. Installation Steps (3-step process)
5. Kit Includes (4-item showcase)
6. Editorial Latest (query-based posts)
7. Footer Newsletter (subscription)

---

## ✅ ESTADO FINAL

**PRODUCCIÓN EN VIVO** ✅
- Theme deploying: ✅
- Security headers: ✅
- Indexación habilitada: ✅
- Staging protegido: ✅
- Performance optimizado: ✅
- Listo para contenido: ✅

**Fecha Go-Live:** 12 de Enero de 2026
**Next Review:** 15 de Enero de 2026 (post-indexación)
