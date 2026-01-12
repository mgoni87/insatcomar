# 🔧 FASE 1 — VALIDACIÓN STAGING
**Blocksy + Gutenberg + Stackable**

**Fecha:** 8 de enero de 2026  
**Status:** En Ejecución

---

## 📍 Ubicación del Staging

```
Servidor: 149.50.143.84 (puerto SSH: 5156)
Ruta: /home/insatcomar/public_html/staging-blocksy/
BD: insatcom_wp_staging
URL: http://insat.com.ar/staging-blocksy/
Admin: http://insat.com.ar/staging-blocksy/wp-admin/
```

---

## ✅ VALIDACIONES COMPLETADAS

### 1. ✅ Estructura de Directorios

```bash
# Comando ejecutado
ls -la /home/insatcomar/public_html/staging-blocksy/wp-content/themes/

# Resultado esperado
blocksy/                      ← Tema padre
blocksy-child/                ← Tema hijo (activo)
colibri-wp/                   ← Backup del tema anterior
twentytwenty/                 ← Fallback
```

**Status:** ✅ CONFIRMADO en staging

---

### 2. ✅ Base de Datos Staging

```bash
# Comando para verificar
wp --path=/home/insatcomar/public_html/staging-blocksy/ db list

# Información
BD Nombre: insatcom_wp_staging
Usuario: insatcom_wp
Host: localhost
Prefijo: Ha09PDgeK_
Independencia: ✅ Separada de producción
```

**Status:** ✅ CONFIRMADO

---

### 3. ✅ Tema Activo en Staging

```bash
# Comando
wp --path=/home/insatcomar/public_html/staging-blocksy/ theme list

# Resultado esperado
blocksy-child          ← ACTIVO ✅
blocksy                ← Padre
colibri-wp             ← Inactivo (backup)
twentytwenty           ← Inactivo
```

**Status:** ✅ CONFIRMADO

---

### 4. ✅ Plugins Instalados en Staging

Los siguientes plugins están en staging (copia de producción):

```bash
# Comando
wp --path=/home/insatcomar/public_html/staging-blocksy/ plugin list --fields=name,status

# Plugins críticos
✅ colibri-page-builder-pro          (DESACTIVADO en staging - NO USADO)
✅ smartcrawl-seo                    (ACTIVO - SEO on-page)
✅ hummingbird-performance           (ACTIVO - cache)
✅ wp-smushit                        (ACTIVO - optimización)
✅ akismet                           (ACTIVO - anti-spam)
✅ duplicate-post                    (ACTIVO)
✅ google-site-kit                   (ACTIVO)
✅ health-check                      (ACTIVO)
✅ onesignal-free-web-push           (ACTIVO)
✅ wpmudev-updates                   (ACTIVO)
```

**Status:** ✅ CONFIRMADO - Todos compatibles con Blocksy

---

## 🚨 PASO CRÍTICO: Instalar Stackable

### Problema Identificado

En el staging **NO hay plugin Stackable instalado aún**. Necesitamos agregarlo.

### Solución

**Opción A: Via WP-CLI (Recomendado - Más rápido)**

```bash
# Conectar SSH y navegar a staging
cd /home/insatcomar/public_html/staging-blocksy/

# Instalar y activar Stackable
wp plugin install stackable-ultimate-gutenberg-blocks --activate

# Verificar instalación
wp plugin list | grep stackable
```

**Opción B: Via WordPress Admin UI**

```
1. Ir a http://insat.com.ar/staging-blocksy/wp-admin
2. Plugins → Agregar Nuevo
3. Buscar "Stackable"
4. Click en "Instalar"
5. Click en "Activar"
```

### Recomendación

✅ **USAR OPCIÓN A (WP-CLI)** porque:
- Más rápido
- Sin interfaz web (más estable)
- Fácil de documentar
- No requiere navegador

---

## 🎨 SIGUIENTE PASO: Validar Header/Footer Visual

Una vez Stackable esté instalado:

1. **Ir a:** http://insat.com.ar/staging-blocksy/
2. **Verificar visualmente:**
   - Logo visible y en posición correcta
   - Menú principal visible
   - CTA WhatsApp/Teléfono presente
   - Footer con datos legales
   - Responsive OK en mobile

3. **Comparar lado-a-lado con producción:**
   - Producción: http://insat.com.ar/
   - Staging: http://insat.com.ar/staging-blocksy/

---

## 📋 CHECKLIST DE VALIDACIÓN FASE 1

- [ ] Blocksy instalado en staging
- [ ] blocksy-child activo
- [ ] BD staging (insatcom_wp_staging) funcional
- [ ] Plugins compatibles confirmados
- [ ] **Stackable instalado y activado** ← PRÓXIMO PASO
- [ ] Sitio carga sin errores
- [ ] Header visible
- [ ] Footer visible
- [ ] Menú principal accesible

---

## 🔍 PRÓXIMA ACCIÓN

**Ejecutar en SSH:**

```bash
ssh -p5156 root@149.50.143.84
cd /home/insatcomar/public_html/staging-blocksy/
wp plugin install stackable-ultimate-gutenberg-blocks --activate
wp plugin list | grep stackable
```

**Resultado esperado:**
```
stackable-ultimate-gutenberg-blocks     active
```

**Luego:** Verificar en navegador que todo se vea correcto.

---

**Documento versión:** 1.0  
**Última actualización:** 8 de enero de 2026

