# ✅ CHECKLIST: MIGRACIÓN A PRODUCCIÓN

## 🔐 SEGURIDAD

- [ ] Retirar HTTP Basic Auth:
  - [ ] Remover `<Directory>` auth del .htaccess de staging
  - [ ] Producción: .htaccess limpio (sin básic auth)
- [ ] Cambiar credenciales admin/admin a fuerte:
  - [ ] En WordPress: Cambiar contraseña admin
  - [ ] En servidor: Actualizar .htpasswd con credenciales fuertes
- [ ] Cambiar contraseña de BD
- [ ] Remover código de debug:
  - [ ] wp-config.php: `define('WP_DEBUG', false);`
  - [ ] wp-config.php: `define('WP_DEBUG_DISPLAY', false);`
  - [ ] Eliminar cualquier debug.log accesible públicamente
- [ ] Activar HTTPS + SSL válido
  - [ ] Certificate: Let's Encrypt (gratuito)
  - [ ] Redirigir HTTP → HTTPS
  - [ ] HSTS header activado (opcional pero recomendado)
- [ ] Firewall / ModSecurity configurado
  - [ ] Reglas básicas contra SQL injection
  - [ ] Protección contra XSS
  - [ ] Rate limiting activado

---

## 🤖 SEO & INDEXACIÓN

- [ ] **Retirar NOINDEX de WordPress**:
  - [ ] Settings → Reading:
    - [ ] **DESMARCAR** "Disuadir a los motores de búsqueda de indexar este sitio"
    - [ ] Guardar
- [ ] **Remover meta robots noindex del código**:
  - [ ] En functions.php: Comentar/remover línea:
    ```php
    // if (IS_STAGING) {
    //   echo '<meta name="robots" content="noindex, ...';
    // }
    ```
- [ ] **Canónicos apuntan a producción** (insat.com.ar):
  - [ ] Verificar en source de páginas
  - [ ] Buscar: `<link rel="canonical" href="https://insat.com.ar/..."`
  - [ ] **NO** debe apuntar a cobertura.insat.com.ar
- [ ] **Generar y publicar sitemap.xml**:
  - [ ] En WordPress: Instalar Yoast SEO o Rankmath
  - [ ] Verificar: https://insat.com.ar/sitemap.xml
  - [ ] Debe devolver XML válido (NO 404)
- [ ] **Robots.txt correcto**:
  ```
  User-agent: *
  Disallow: /wp-admin/
  Disallow: /wp-includes/
  
  Sitemap: https://insat.com.ar/sitemap.xml
  ```
- [ ] **Enviar sitemap a Google Search Console**:
  - [ ] Verificar dominio en GSC
  - [ ] Enviar sitemap.xml
  - [ ] Esperar indexación (48-72hs)
- [ ] **Enviar a Bing Webmaster**:
  - [ ] Verificar dominio
  - [ ] Submit sitemap

---

## 📊 ANALYTICS & AD NETWORKS

- [ ] **Google Analytics 4**:
  - [ ] Conectar GA4 a **propiedad de PRODUCCIÓN** (NO staging)
  - [ ] Verificar tracking ID/Measurement ID
  - [ ] Test: Generar evento en sitio → aparecer en tiempo real en GA
- [ ] **Google Tag Manager**:
  - [ ] Conectar a **contenedor de PRODUCCIÓN**
  - [ ] Test: Disparar tags correctamente
- [ ] **Google Ads**:
  - [ ] Conectar conversion tracking (si aplica)
  - [ ] Verificar pixel instala correctamente
- [ ] **Facebook Pixel**:
  - [ ] Instalar en producción (NO staging)
  - [ ] Test eventos: ViewContent, Purchase, Lead
  - [ ] Verificar en Facebook Events Manager

---

## ⚡ PERFORMANCE (VALIDAR ANTES)

- [ ] **Lighthouse Audit**:
  - [ ] Abrir https://insat.com.ar en Chrome
  - [ ] DevTools → Lighthouse → Run
  - [ ] Performance: > 90
  - [ ] Accessibility: > 90
  - [ ] Best Practices: > 90
  - [ ] SEO: > 90
- [ ] **PageSpeed Insights**:
  - [ ] https://pagespeed.web.dev
  - [ ] Ingresar URL producción
  - [ ] Mobile Core Web Vitals: todos GREEN
  - [ ] Desktop Core Web Vitals: todos GREEN
- [ ] **Cache configurado**:
  - [ ] Browser cache: assets estáticos 1 año
  - [ ] Server cache: HTML 1 hora
  - [ ] Verificar headers Cache-Control

---

## 🔗 FUNCIONALIDAD

- [ ] **Todos los links internos funcionan**:
  - [ ] Test menú principal
  - [ ] Test breadcrumbs
  - [ ] Test footer links
  - [ ] Test "Zona de Clientes" (externo)
  - [ ] Validar con herramienta: Broken Link Checker
- [ ] **Formularios envían emails**:
  - [ ] Newsletter: verificar llegada de email
  - [ ] Contacto (si existe): verificar llegada
  - [ ] Verificar que emails NO van a spam
- [ ] **Búsqueda funciona** (si existe)
- [ ] **Paginación funciona**:
  - [ ] Navegar posts con anterior/siguiente
  - [ ] Paginación numérica
- [ ] **Mobile responsive**:
  - [ ] Test en reales devices (iPhone, Android)
  - [ ] Test en 320px, 480px, 768px, 1024px
  - [ ] Interfaz readable, botones clickeables
- [ ] **Menús correctos**:
  - [ ] Menú principal: Hogares, Empresas, Cobertura, etc.
  - [ ] Menú utilitario: Zona de Clientes
  - [ ] Menú footer: Links legales, contacto
- [ ] **Footer correcto**:
  - [ ] Logo INSAT
  - [ ] Links legales
  - [ ] Newsletter signup
  - [ ] Social media links (si aplica)
  - [ ] Copyright: © 2026 INSAT

---

## 📄 CONTENIDO

- [ ] **Copy sin errores ortográficos**:
  - [ ] Review completo de texto
  - [ ] Validar tono es INSAT (NO Starlink copy)
- [ ] **Imágenes con metadatos**:
  - [ ] Alt text descriptivo (NO vacío)
  - [ ] Title attribute si aplica
  - [ ] Optimizadas (WebP, tamaño correcto)
- [ ] **Meta descriptions presentes**:
  - [ ] Todas las páginas principales
  - [ ] CPTs: News, Tech, Stories
  - [ ] 160 caracteres máximo
  - [ ] Incluir palabra clave principal
- [ ] **Slugs consistentes**:
  - [ ] /hogares/ (NO /planes/, /internet/)
  - [ ] /novedades/ (NO /blog/, /noticias/)
  - [ ] /tecnologia/ (NO /tech-articles/)
  - [ ] Minúsculas, con guiones (NO espacios)
- [ ] **Redirecciones 301 si aplica**:
  - [ ] URLs viejas → nuevas
  - [ ] Implementadas en .htaccess o plugin
  - [ ] Test: Visitar URL vieja → redirige a nueva

---

## 🖥️ COMPATIBILIDAD

### Browsers Desktop
- [ ] Chrome (últimas 2 versiones)
- [ ] Firefox (últimas 2 versiones)
- [ ] Safari (últimas 2 versiones)
- [ ] Edge (últimas 2 versiones)

### Mobile
- [ ] iOS Safari (últimas 2 versiones)
- [ ] Chrome Android (últimas 2 versiones)
- [ ] Samsung Internet

### Validar Especialmente
- [ ] Formularios funcionan en todos
- [ ] Dropdowns/menús accesibles
- [ ] Modales cierran correctamente
- [ ] Imágenes cargan
- [ ] Videos reproducen (si aplica)

---

## 💾 BACKUPS

- [ ] **Backup completo de BD (PROD)**:
  ```bash
  mysqldump -u user -p database > backup_prod_$(date +%Y%m%d).sql
  ```
- [ ] **Backup completo de archivos (PROD)**:
  ```bash
  tar -czf backup_prod_$(date +%Y%m%d).tar.gz /home/insatcomar/public_html/
  ```
- [ ] **Plan de rollback documentado**:
  - [ ] Cómo revertir a versión anterior
  - [ ] Restore scripts listos
  - [ ] Testing del restore (1x)

---

## 📞 COMUNICACIÓN

- [ ] **Stakeholders notificados**:
  - [ ] Fecha/hora de go-live
  - [ ] Ventana de cambios
  - [ ] Responsables de soporte
- [ ] **Team preparado**:
  - [ ] Runbook documentado
  - [ ] Contactos de emergencia
  - [ ] Escalation path claro
- [ ] **Documentación actualizada**:
  - [ ] README con instrucciones
  - [ ] Contacto soporte técnico
  - [ ] Reportar bugs

---

## 🚀 DESPUÉS DE PUBLICAR (PRIMERAS 48HS)

- [ ] **Monitoreo 24/7**:
  - [ ] Uptime monitoring activo (Pingdom, UptimeRobot)
  - [ ] Error logs monitoreados
  - [ ] Performance monitoreado
- [ ] **Google Search Console**:
  - [ ] Verificar que indexa correctamente
  - [ ] Buscar: `site:insat.com.ar`
  - [ ] Debe aparecer contenido (NO cobertura.insat.com.ar)
- [ ] **Verificar Analytics**:
  - [ ] Conversiones registrando correctamente
  - [ ] Eventos disparándose
  - [ ] No datos duplicados o faltantes
- [ ] **Error Logs**:
  - [ ] Revisar PHP errors
  - [ ] Revisar server errors
  - [ ] Revisar console errors (JS)
- [ ] **Uptime**:
  - [ ] Sitio accesible desde múltiples regiones
  - [ ] Response time normal
  - [ ] SSL válido (0 warnings)

---

## ⚠️ COSAS A REMOVER ANTES DE GO-LIVE

- ❌ Debug toolbar (WP)
- ❌ Query monitor
- ❌ WP Mail SMTP testing
- ❌ Maintenance mode activado
- ❌ Staging header (si existe)
- ❌ Credenciales por defecto (admin/admin)
- ❌ Comentarios de debug en código
- ❌ Console.log() en JavaScript
- ❌ TODO / FIXME comentarios sensibles

---

## 📝 SIGN-OFF FINAL

- [ ] Tech Lead: ______________________
- [ ] QA: ______________________
- [ ] Project Manager: ______________________
- [ ] Client: ______________________
- [ ] Fecha: ______________________

**GO-LIVE AUTORIZADO: [ ] SÍ [ ] NO**
