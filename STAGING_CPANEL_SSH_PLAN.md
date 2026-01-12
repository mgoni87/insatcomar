# CREAR STAGING EN CPANEL + SSH - PLAN EJECUTABLE

## 1. INFORMACIÓN NECESARIA

Para proceder, necesito que confirmes:

```
1. ¿Cuál es la URL del servidor SSH?
   Ejemplo: ssh.tudominio.com o IP_ADDRESS
   
2. ¿Usuario SSH?
   Ejemplo: mariano o root o tu_usuario
   
3. ¿Puerto SSH?
   Ejemplo: 22 (default) u otro
   
4. ¿Dónde está WordPress en el servidor?
   Rutas típicas en cPanel:
   - /home/usuario/public_html/
   - /home/usuario/public_html/insatcomar/
   - /var/www/insat.com.ar/
   
5. ¿Qué URL usan ahora?
   - insat.com.ar
   - www.insat.com.ar
   - ip_address/insatcomar/
   
6. ¿Tienes acceso a cPanel mismo?
   - cPanel URL: ?
   - Usuario cPanel: ?
```

---

## 2. PLAN GENERAL (CON ACCESO SSH)

```
PRODUCCIÓN:
└─ /home/usuario/public_html/
   ├─ wp-config.php
   ├─ wp-content/themes/colibri-wp/
   ├─ wp-content/plugins/colibri-page-builder-pro/
   └─ ... sitio vivo

STAGING (lo que crearemos):
└─ /home/usuario/public_html/staging-blocksy/
   ├─ wp-config.php (modificado para staging)
   ├─ wp-content/themes/blocksy/
   ├─ wp-content/themes/blocksy-child/
   ├─ ... copia completa pero separada
   └─ Accesible en: staging-blocksy.insat.com.ar
      O simplemente: insat.com.ar/staging-blocksy/
```

---

## 3. GUÍA PASO A PASO (CON ACCESO SSH)

### PASO 1: Conectar por SSH

```bash
# En tu Mac, abre Terminal y conecta:
ssh usuario@servidor.com
# O: ssh usuario@ip_address

# Si pide contraseña, ingresa la de SSH
# Si tienes llave SSH (.pem), usa:
ssh -i /ruta/a/tu/llave.pem usuario@servidor.com
```

### PASO 2: Navegar a WordPress

```bash
# Típicamente:
cd ~/public_html

# O:
cd ~/public_html/insatcomar

# Verifica que estés en la carpeta correcta:
ls -la
# Deberías ver: wp-config.php, wp-content, wp-admin, etc
```

### PASO 3: Hacer Backup de Producción

```bash
# Crear carpeta de backups
mkdir -p backups

# Backup de base de datos
# Primero, necesitamos saber credenciales BD:
cat wp-config.php | grep -E "DB_NAME|DB_USER|DB_HOST|DB_PASSWORD"

# Esto te mostrará algo como:
# define( 'DB_NAME', 'insatcomar_db' );
# define( 'DB_USER', 'insatcomar_user' );
# define( 'DB_HOST', 'localhost' );
# define( 'DB_PASSWORD', 'xxxxx' );

# Luego hacer dump:
mysqldump -u DB_USER -p DB_NAME > backups/backup_$(date +%Y%m%d_%H%M%S).sql
# Te pedirá contraseña, usa lo que viste arriba

# Backup de archivos
tar -czf backups/backup_files_$(date +%Y%m%d_%H%M%S).tar.gz wp-content/themes/ wp-content/plugins/ uploads/
```

### PASO 4: Copiar Proyecto a Staging

```bash
# En el mismo public_html:
cp -r . staging-blocksy/

# Esto crea carpeta con copia completa
# Toma unos minutos...

# Verificar:
ls -la staging-blocksy/
# Deberías ver: wp-config.php, wp-content, etc
```

### PASO 5: Crear Base de Datos Staging

```bash
# En SSH, conectarse a MySQL:
mysql -u DB_USER -p

# Te pide contraseña (usa la que viste antes)

# Dentro de MySQL, ejecutar:
CREATE DATABASE insatcomar_staging;

# Salir:
exit

# Luego importar la BD actual en la staging:
mysqldump -u DB_USER -p DB_NAME | mysql -u DB_USER -p insatcomar_staging
# Te pide contraseña 2 veces
```

### PASO 6: Editar wp-config.php en Staging

```bash
# Editar archivo de staging:
nano staging-blocksy/wp-config.php

# Cambiar líneas:
# OLD: define( 'DB_NAME', 'insatcomar_db' );
# NEW: define( 'DB_NAME', 'insatcomar_staging' );

# Guardar: Ctrl+O, Enter, Ctrl+X
```

### PASO 7: Instalar Blocksy en Staging

```bash
# Ir a carpeta staging:
cd staging-blocksy

# Descargar Blocksy (con wget):
wget https://downloads.wordpress.org/theme/blocksy.latest-stable.zip

# Descomprimir:
unzip blocksy.latest-stable.zip -d wp-content/themes/

# Eliminar zip:
rm blocksy.latest-stable.zip

# Verificar:
ls wp-content/themes/
# Deberías ver: blocksy/, colibri-wp/, twentytwenty/
```

### PASO 8: Crear Child Theme de Blocksy

```bash
# Crear carpeta child:
mkdir -p wp-content/themes/blocksy-child

# Crear style.css:
cat > wp-content/themes/blocksy-child/style.css << 'EOF'
/*
Theme Name: Blocksy Child
Template: blocksy
Version: 1.0
Text Domain: blocksy-child
Domain Path: /languages
*/

@import url('../blocksy/style.css');
EOF

# Crear functions.php:
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

// Aquí puedes agregar funciones custom después

?>
EOF

# Crear screenshot.png (opcional):
# Puedes copiar del tema blocksy o dejar vacío
```

### PASO 9: Cambiar Tema Activo en BD

```bash
# Conectar a MySQL:
mysql -u DB_USER -p insatcomar_staging

# Ver tema activo actual:
SELECT option_name, option_value FROM wp_options WHERE option_name = 'template' OR option_name = 'stylesheet' LIMIT 5;

# Cambiar a Blocksy (parent):
UPDATE wp_options SET option_value = 'blocksy' WHERE option_name = 'template';

# Cambiar a Child (visible):
UPDATE wp_options SET option_value = 'blocksy-child' WHERE option_name = 'stylesheet';

# Salir:
exit
```

### PASO 10: Configurar Subdominio Staging (en cPanel)

```bash
# Opción A: Si usas cPanel por web
1. Ir a: cPanel → Addon Domains O Parked Domains
2. Crear: staging-blocksy.insat.com.ar
3. Apuntarlo a: /home/usuario/public_html/staging-blocksy/

# Opción B: Si prefieres URL sencilla
# Simplemente acceder a: insat.com.ar/staging-blocksy/
# (Sin necesidad de crear subdominio)
```

### PASO 11: Verificar Staging Funciona

```bash
# En tu navegador (desde tu Mac):
http://staging-blocksy.insat.com.ar
O
http://insat.com.ar/staging-blocksy/

# Deberías ver:
- Sitio similar al actual
- Pero con tema Blocksy (ligeramente diferente visualmente)
- Panel de admin en /wp-admin/
```

---

## 4. SCRIPT COMPLETO AUTOMATIZADO

Si prefieres, puedo crear un script `.sh` que hace TODO automáticamente.

Simplemente ejecutas:

```bash
# Descargar script:
curl https://ejemplo.com/setup-staging-blocksy.sh > setup.sh

# Ejecutar:
bash setup.sh

# Y listo, staging creado!
```

¿Quieres que lo prepare?

---

## 5. PRÓXIMOS PASOS DESPUÉS DE STAGING

Una vez que tengas staging funcionando:

```
FASE 1: Revisión Visual
├─ Acceder a staging
├─ Verificar que se ve bien
├─ Tomar screenshots
└─ Comparar con producción

FASE 2: Customización
├─ Cambiar colores (Appearance → Customize)
├─ Ajustar header/footer
├─ Recrear páginas clave
└─ Testing responsivo

FASE 3: Testing Completo
├─ Verificar formularios
├─ Testing en móvil
├─ Performance (PageSpeed)
├─ SEO checks
└─ Compatibilidad navegadores

FASE 4: Aprobación
├─ Equipo revisa staging
├─ Hacer lista de "ready"
├─ Documentar cambios
└─ Preparar rollback plan

FASE 5: Deploy a Producción
├─ Backup final de prod
├─ Copiar staging → prod
├─ Verificar en vivo
├─ Monitoreo 24-48h
└─ Comunicar cambios
```

---

## 6. INFORMACIÓN QUE NECESITO DE TI

Responde esto para que automarice todo:

```
1. URL SSH:
   
2. Usuario SSH:
   
3. Puerto SSH (default 22):
   
4. Ruta de WordPress:
   
5. ¿Tienes acceso cPanel? (usuario/pass o URL):
   
6. Nombre actual de la BD:
   
7. Usuario de BD:
   
8. Host de BD (localhost o diferente):
```

---

## 7. SCRIPT AUTOMÁTICO (Te lo preparo cuando des info)

```bash
#!/bin/bash
# setup-staging-blocksy.sh

# Variables (reemplazar con tus datos)
SSH_USER="tu_usuario"
SSH_HOST="tu_servidor"
SSH_PORT="22"
WP_PATH="/home/usuario/public_html"
DB_NAME="insatcomar_db"
DB_USER="insatcomar_user"
DB_PASS="tu_password"
DB_HOST="localhost"

# PASO 1: Conectar por SSH y ejecutar todo
ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << 'REMOTESCRIPT'

cd $WP_PATH

# Backup
mkdir -p backups
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Copiar proyecto
cp -r . staging-blocksy/

# BD staging
mysql -u $DB_USER -p$DB_PASS << MYSQL_SCRIPT
CREATE DATABASE ${DB_NAME}_staging;
MYSQL_SCRIPT

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | mysql -u $DB_USER -p$DB_PASS ${DB_NAME}_staging

# Instalar Blocksy
cd staging-blocksy
wget https://downloads.wordpress.org/theme/blocksy.latest-stable.zip
unzip blocksy.latest-stable.zip -d wp-content/themes/
rm blocksy.latest-stable.zip

# Child theme
mkdir -p wp-content/themes/blocksy-child
# ... (crear archivos)

echo "✅ Staging setup complete!"
echo "Accede a: http://staging-blocksy.insat.com.ar"

REMOTESCRIPT
```

---

## AHORA DIME

¿Necesitas que:

1. **Te dé instrucciones paso a paso** (lo que hiciste arriba)
   → Tú ejecutas cada comando manualmente en SSH

2. **Cree script automático** (setup.sh)
   → Tú ejecutas 1 comando, él hace todo

3. **Ambas**
   → Te doy script + instrucciones por si algo falla

¿Cuál prefieres? Y por favor, dame esos 8 datos que pedí arriba para configurar todo exacto.
