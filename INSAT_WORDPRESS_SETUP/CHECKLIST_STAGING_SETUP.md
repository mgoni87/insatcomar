# ✅ CHECKLIST: SETUP STAGING INSAT

## 🔐 PASO 1: HTTP BASIC AUTH

- [ ] Acceder al servidor SSH: `ssh -p5156 root@149.50.143.84`
- [ ] Generar .htpasswd:
  ```bash
  htpasswd -c /home/insatcomar/.htpasswd admin
  # Ingresar password: admin
  chmod 644 /home/insatcomar/.htpasswd
  chown root:www-data /home/insatcomar/.htpasswd
  ```
- [ ] Copiar contenido de `htaccess-staging.txt` a `/home/insatcomar/public_html/cobertura/.htaccess`
- [ ] Test: Acceder a https://cobertura.insat.com.ar sin credenciales → debe pedir usuario/pass
- [ ] Test: Ingresar admin/admin → debe permitir acceso

## 🤖 PASO 2: ANTI-INDEXACIÓN

- [ ] Copiar `robots-staging.txt` a `/home/insatcomar/public_html/cobertura/robots.txt`
- [ ] Verificar que robots.txt devuelve correcto:
  ```bash
  curl -i https://cobertura.insat.com.ar/robots.txt
  ```
- [ ] En WordPress Admin → Configuración → Lectura:
  - [ ] Marcar "Disuadir a los motores de búsqueda de indexar este sitio"
  - [ ] Guardar

## 🔗 PASO 3: WP-CONFIG.PHP

- [ ] Agregar fragment de `wp-config-fragment.php` al final de wp-config.php en `/home/insatcomar/public_html/wp-config.php`
- [ ] Validar:
  ```bash
  wp --allow-root config get BLOG_PUBLIC
  # Debe devolver: 0
  ```

## 🎨 PASO 4: CHILD THEME INSTALLATION

- [ ] Descargar/copiar carpeta `blocksy-child` a:
  `/home/insatcomar/public_html/wp-content/themes/blocksy-child/`
- [ ] En WordPress Admin → Apariencia → Temas:
  - [ ] Activar "Blocksy Child - INSAT"
- [ ] Verificar que theme se activó correctamente

## 🧩 PASO 5: VALIDAR PATTERNS EN GUTENBERG

- [ ] Ir a WordPress Admin → Páginas
- [ ] Crear página de prueba
- [ ] Abrir Gutenberg
- [ ] En botón "+" → buscar "INSAT"
- [ ] Verificar que aparecen los 7 patterns:
  - [ ] Hero Fullscreen
  - [ ] Cards Planes (3 columnas)
  - [ ] Verificá Cobertura
  - [ ] Instalación en 3 Pasos
  - [ ] Qué Incluye el Kit
  - [ ] Editorial - Últimas Publicaciones
  - [ ] Footer con Newsletter

## 🚫 PASO 6: VERIFICAR NO-INDEXING

- [ ] Ir a https://cobertura.insat.com.ar (con admin/admin)
- [ ] Abrir DevTools → Pestaña "Network" → inspeccionar cualquier request HTML
  - [ ] Verificar header: `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex`
- [ ] View Page Source:
  - [ ] Verificar `<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">`
  - [ ] Verificar canonical apunta a `https://cobertura.insat.com.ar/...`

## 📊 PASO 7: VALIDAR CPTs FUNCIONAN

- [ ] En WordPress Admin:
  - [ ] Menú izquierdo debe mostrar:
    - [ ] Novedades
    - [ ] Tecnología
    - [ ] Historias
  - [ ] Crear un post en cada CPT (títuloy contenido básico)
  - [ ] Verificar que slugs son correctos:
    - [ ] Novedades: `/novedades/titulo-del-post/`
    - [ ] Tech: `/tecnologia/titulo-del-post/`
    - [ ] Historias: `/historias/titulo-del-post/`

## 🎯 PASO 8: MENUS

- [ ] En WordPress Admin → Apariencia → Menús:
  - [ ] Crear "Menú Principal"
  - [ ] Agregar items:
    - [ ] Hogares
    - [ ] Empresas
    - [ ] Cobertura
    - [ ] Especificaciones
    - [ ] Soporte
    - [ ] Novedades
    - [ ] Tecnología
    - [ ] Historias
  - [ ] Asignar a "Menú Principal"
  - [ ] Crear "Menú Utilitario"
  - [ ] Agregar: "Zona de Clientes" (link externo)
  - [ ] Asignar a "Menú Utilitario"

## 📧 PASO 9: FORMULARIOS (NO ENVIAR EMAILS)

- [ ] Test cualquier formulario en staging
- [ ] Verificar en `/wp-content/debug.log` que está logueado (no enviado)
- [ ] Confirmar que NO llegó email real a destinatario

## 🔍 PASO 10: VALIDACIÓN FINAL

- [ ] Test Mobile (responsive):
  - [ ] Ver en Chrome DevTools → Responsive Design Mode
  - [ ] Tamaños: 320px, 768px, 1024px
  - [ ] Verificar que se ve bien en todos
- [ ] Test Lighthouse (Chrome DevTools):
  - [ ] Performance: > 80
  - [ ] Accessibility: > 90
  - [ ] Best Practices: > 90
  - [ ] SEO: > 90
- [ ] Test sin autenticación (debe pedir user/pass):
  - [ ] Abrir navegador anónimo
  - [ ] Ir a https://cobertura.insat.com.ar
  - [ ] Debe pedir credenciales

## ✅ STAGING LISTO

Cuando todos los checks pasen, staging está LISTO para desarrollo de contenido y pruebas.

---

## 📝 NOTAS IMPORTANTES

1. **Credenciales Actuales**: admin/admin (CAMBIAR ANTES DE MOSTRAR A CLIENTE)
2. **No indexable**: Verificar regularmente con `curl -I https://cobertura.insat.com.ar/robots.txt`
3. **Emails**: Nungún email real debe salir desde staging (verificar logs)
4. **Analytics**: NO conectar GA a propiedad de producción
5. **Canónicos**: SIEMPRE apuntan a staging en staging
