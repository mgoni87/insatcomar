# PLAN: CREAR STAGING PARA BLOCKSY

## 1. OPCIONES DE STAGING PARA TI

### Opción 1: LOCAL STAGING (Recomendado para pruebas rápidas)
**Mejor para:** Desarrollo local, pruebas visuales, ajustes CSS

```
Tu Mac (actual):
/Users/mariano/Documents/GitHub/insatcomar  ← PRODUCCIÓN

Tu Mac (nuevo):
/Users/mariano/Documents/GitHub/insatcomar-staging  ← STAGING
```

✅ **Ventajas:**
- Setup rápido
- Sin costo
- Control total
- Fácil hacer rollback
- No afecta sitio en vivo

❌ **Desventajas:**
- Solo accesible localmente (127.0.0.1)
- No puedes compartir URL con otros

---

### Opción 2: SERVIDOR STAGING (Si quieres URL pública)
**Mejor para:** Testing con otros, demos, verificación en vivo

```
Si tu sitio está en servidor web:
insat.com.ar  ← PRODUCCIÓN
staging.insat.com.ar  ← STAGING (subdominio)
```

✅ **Ventajas:**
- Accesible vía URL pública
- Compartible con otros
- Más realista
- Puedes verificar DNS, SSL, etc

❌ **Desventajas:**
- Requiere acceso servidor
- Puede tener costo (subdominio)
- Setup más complejo

---

## 2. PLAN PROPUESTO: STAGING LOCAL + TESTING EN VIVO

### Fase 1: CREAR COPIA LOCAL (Staging Local)
```
PASO 1: Duplicar carpeta
$ cp -r /Users/mariano/Documents/GitHub/insatcomar \
       /Users/mariano/Documents/GitHub/insatcomar-staging

PASO 2: Instalar Blocksy en staging
$ cd insatcomar-staging
$ wp theme install blocksy

PASO 3: Crear child theme
$ mkdir -p wp-content/themes/blocksy-child

PASO 4: Configurar child theme
$ cat > wp-content/themes/blocksy-child/style.css << 'EOF'
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0
Text Domain: blocksy-child
Domain Path: /languages
*/

@import url('../blocksy/style.css');
EOF

PASO 5: Setup WordPress para staging
- Base de datos separada: insatcomar_staging
- Tabla prefix: stg_ (para evitar conflictos)
- Environment local
```

### Fase 2: ACTIVAR Y PRUEBAR EN STAGING LOCAL
```
PASO 1: Activar Blocksy
$ wp theme activate blocksy

PASO 2: Activar child theme
$ wp theme activate blocksy-child

PASO 3: Acceder local
http://localhost:8000/insatcomar-staging
http://127.0.0.1/insatcomar-staging

PASO 4: Empezar customizaciones
- Ir a Appearance → Customize
- Cambiar colores
- Ajustar layouts
- Probar responsivo
```

### Fase 3: CUSTOMIZAR EN STAGING SIN PRESIÓN
```
blocksy-child/
├─ functions.php          ← Funciones custom
├─ style.css              ← Estilos custom (overrides)
├─ assets/
│  ├─ css/
│  │  ├─ custom.css
│  │  ├─ header.css
│  │  ├─ colors.css
│  │  └─ layout.css
│  └─ js/
│     └─ custom.js
├─ template-parts/        ← Templates personalizados
└─ screenshot.png         ← Preview

Aquí cambias LIBREMENTE sin afectar producción
```

---

## 3. ESTRUCTURA ACTUAL Y CONSIDERACIONES

### 3.1 Lo que Encontré

```
Sitio actual:
/Users/mariano/Documents/GitHub/insatcomar/
├─ wp-content/themes/
│  ├─ colibri-wp/         ← Tema actual (ACTIVO)
│  └─ twentytwenty/       ← Tema fallback
├─ wp-content/plugins/
│  ├─ colibri-page-builder-pro/    ← CRÍTICO
│  ├─ smartcrawl-seo/
│  ├─ wp-smushit/
│  └─ ...
├─ wp-config-sample.php  ← Configuración template
└─ [posts, pages, uploads, etc]

NO ENCONTRÉ:
❌ wp-config.php (archivo real)
❌ .env (archivo de variables)
❌ docker-compose.yml
```

### 3.2 Lo que Necesito

Para crear staging perfecto, necesito:
1. ✅ Archivo wp-config.php actual (con credenciales DB)
2. ✅ Nombre de la base de datos actual
3. ✅ Credenciales de base de datos
4. ✅ URL del sitio actual (insat.com.ar, ¿verdad?)

---

## 4. PASOS PARA CREAR STAGING

### OPCIÓN A: Staging Local Rápido (15-30 minutos)

```bash
#!/bin/bash

# 1. Copiar proyecto
cp -r /Users/mariano/Documents/GitHub/insatcomar \
      /Users/mariano/Documents/GitHub/insatcomar-staging

cd /Users/mariano/Documents/GitHub/insatcomar-staging

# 2. Crear nueva base de datos staging
# (Esto requiere acceso a MySQL/MariaDB)
# Si usas MAMP, Valet, Docker, etc - instrucciones diferentes

# 3. Copiar base de datos
# mysqldump -u root -p original_db > backup.sql
# mysql -u root -p staging_db < backup.sql

# 4. Actualizar wp-config.php
# Cambiar:
# - DB_NAME: original → original_staging
# - DB_USER: (mismo)
# - DB_PASSWORD: (misma)
# - WP_HOME/WP_SITEURL: localhost o URL staging

# 5. Instalar Blocksy
wp theme install blocksy

# 6. Crear child theme
mkdir -p wp-content/themes/blocksy-child
cat > wp-content/themes/blocksy-child/style.css << 'EOF'
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0
*/
@import url('../blocksy/style.css');
EOF

cat > wp-content/themes/blocksy-child/functions.php << 'EOF'
<?php
/**
 * Blocksy Child Theme Functions
 */

// Enqueue parent theme styles
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'blocksy-parent', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'blocksy-child', get_stylesheet_directory_uri() . '/style.css', array( 'blocksy-parent' ) );
});

// Aquí van funciones custom
?>
EOF

# 7. Activar
wp theme activate blocksy-child

echo "✅ Staging setup complete!"
echo "Accede a: http://localhost:8000/insatcomar-staging"
```

---

## 5. CÓMO PROCEDEREMOS

### Mi Recomendación (Orden de Pasos):

```
PASO 1: Prepárame la Información
  ├─ Acceso a base de datos (credenciales)
  ├─ URL actual del sitio
  ├─ Nombre actual de BD
  └─ Versión de WordPress actual

PASO 2: Yo Creo la Estructura
  ├─ Scripts de setup automático
  ├─ Configurar child theme
  ├─ Crear estructura de assets

PASO 3: Tú Ejecutas en Tu Mac
  ├─ Ejecutar scripts
  ├─ Instalar Blocksy
  ├─ Activar child theme
  └─ Acceder http://localhost:...

PASO 4: Empezamos a Customizar
  ├─ Ajustar colores
  ├─ Configurar header/footer
  ├─ Crear pages básicas
  ├─ Testing responsivo
  └─ Tomar screenshots

PASO 5: Aprobación y Deploy
  ├─ Revisar en staging
  ├─ Hacer lista de "ready"
  ├─ Backup producción
  └─ Copiar staging → producción
```

---

## 6. PRÓXIMOS PASOS INMEDIATOS

Necesito que me des:

```
1. ¿Cómo está configurado tu WordPress actualmente?
   - ¿Usas MAMP? ¿Valet? ¿Docker? ¿Otro?
   - URL local o solo archivo?
   - Base de datos: ¿dónde? ¿credenciales?

2. ¿Está el sitio en un servidor web también?
   - Dominio: insat.com.ar (¿verdad?)
   - ¿Hosting compartido, VPS, dedicado?
   - ¿FTP/SSH acceso?

3. ¿Qué herramientas tienes disponibles?
   - Git (veo que sí, por .git)
   - Docker?
   - WP-CLI instalado?
   - MySQL/MariaDB local?

4. Objetivo del staging:
   - Solo ver cómo se vería visualmente?
   - Hacer testing completo (forms, etc)?
   - Que otros vean también?
```

Con esa info, te doy exactamente:
- Script de setup
- Estructura de child theme
- Guía paso a paso

¿Me das esos detalles?
