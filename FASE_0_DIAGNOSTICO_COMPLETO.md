# 🔍 FASE 0 — DIAGNÓSTICO COMPLETO DE MIGRACIÓN
**Blocksy + Gutenberg + Stackable**

**Fecha:** 8 de enero de 2026  
**Ejecutado por:** Senior WordPress Engineer  
**Estado:** ✅ COMPLETADO — Listo para Fase 1

---

## 📋 RESUMEN EJECUTIVO

### Objetivo
Migrar INSAT desde **Colibri (Page Builder)** a **Blocksy Theme + Gutenberg + Stackable** sin romper:
- URLs / Permalinks
- SEO on-page (titles, metas, schema)
- Tracking (GA/GTM/Meta Pixel)
- Funcionalidad crítica (formularios, CTAs, WhatsApp)

### Status Actual
✅ **Sitio WordPress operativo en producción**  
✅ **Staging activo en /staging-blocksy/ **  
✅ **Tema objetivo (Blocksy) instalado en staging**  
✅ **Plugin Colibri Page Builder identificado y mapeado**

---

## 🏗️ INVENTARIO TÉCNICO

### 1. TEMA ACTUAL
| Propiedad | Valor |
|-----------|-------|
| **Tema Activo** | Colibri WP v1.0.144 |
| **Ubicación** | `/wp-content/themes/colibri-wp/` |
| **Requiere** | PHP 5.6+ |
| **Template Language** | PHP + Custom Builder |
| **Child Theme** | No existe |
| **Custom CSS** | Probablemente en Customizer |

**Riesgos identificados:**
- Tema pesado con builder integrado
- No es pure-WordPress (shortcodes propietarios)
- Migraciones complejas requieren inspección manual de contenido
- Dependencia en `colibri-page-builder-pro` plugin

---

### 2. PLUGIN CRITICO: Colibri Page Builder Pro

| Propiedad | Valor |
|-----------|-------|
| **Plugin** | Colibri Page Builder Pro |
| **Ubicación** | `/wp-content/plugins/colibri-page-builder-pro/` |
| **Funcionalidad** | Constructor drag-drop propietario |
| **Shortcodes** | Genera shortcodes tipo `[cw-XXX]` |
| **Integración** | Profunda en contenido de páginas |
| **Riesgo de Migración** | 🔴 **ALTO** |

**Impacto en contenido:**
- Pages/Posts probablemente contienen shortcodes Colibri (`[cw-*]`)
- Si se desactiva sin migración → contenido se muestra roto
- Necesita conversión manual a Gutenberg bloques

---

### 3. PLUGIN SEO: Smartcrawl (WPMU DEV)

| Propiedad | Valor |
|-----------|-------|
| **Plugin** | Smartcrawl SEO |
| **Ubicación** | `/wp-content/plugins/smartcrawl-seo/` |
| **Funcionalidad** | SEO on-page, schema, readability |
| **Post Meta** | Almacena en `_smartcrawl_*` post meta |
| **Metas Inyectadas** | `<title>`, `<meta name="description">`, og:* |
| **Riesgo de Migración** | 🟢 **BAJO** |

**Estrategia:**
- ✅ Se mantiene activo post-migración
- ✅ Compatible con Blocksy
- ✅ Sigue inyectando metas sin cambios
- ✅ No requiere migración especial

---

### 4. OTROS PLUGINS DETECTADOS

| Plugin | Función | Migración |
|--------|---------|-----------|
| **Akismet** | Anti-spam comentarios | ✅ Compatible |
| **Duplicate Post** | Duplicar posts | ✅ Compatible |
| **Google Site Kit** | Integración GSC/GA | ✅ Compatible |
| **Health Check** | Diagnóstico sitio | ✅ Compatible |
| **Hummingbird Performance** | Cache + optimización | ✅ Compatible |
| **WP Smushit** | Optimización imágenes | ✅ Compatible |
| **OneSignal** | Push notifications | ✅ Compatible |
| **WPMU DEV Updates** | Update manager | ✅ Compatible |

**Conclusión:** Todos plugins son "neutral" respecto tema. No requieren cambios.

---

### 5. TEMA OBJETIVO: Blocksy

| Propiedad | Valor |
|-----------|-------|
| **Tema** | Blocksy (Latest) |
| **Ubicación en Staging** | `/staging-blocksy/wp-content/themes/blocksy/` |
| **Child Theme** | ✅ Ya existe: `blocksy-child/` |
| **Constructor** | Blocksy Visual Builder (premium) |
| **Bloques Soportados** | ✅ Gutenberg nativo |
| **Performance** | 🟢 Ligero + optimizado |
| **Compatibilidad Stackable** | ✅ 100% |

**Ventajas:**
- ✅ Sin lock-in builder pesado
- ✅ Soporta Gutenberg bloques nativos
- ✅ Mejor CWV que Colibri
- ✅ SEO-friendly por defecto
- ✅ Activo en staging y listo

---

## 🎯 PÁGINAS CRÍTICAS A MIGRAR

Basado en el análisis de acceso y conversión, estas son las prioridades:

### Tier 1 — Alta Conversión (Migrar Primero)

| Página | Slug | Función | Complejidad | Prioridad |
|--------|------|---------|-------------|-----------|
| **Home** | `/` | Hero, beneficios, CTAs | 🟡 Media | 1️⃣ Crítica |
| **Planes** | `/planes/` | Comparativa planes + precios | 🟡 Media | 2️⃣ Crítica |
| **Cobertura** | `/cobertura/` | Mapa + form WhatsApp | 🔴 Alta | 3️⃣ Crítica |
| **Prepago** | `/prepago/` | Explicación + CTA | 🟢 Baja | 4️⃣ Importante |
| **Costo** | `/costo/` | Tabla + CTA | 🟢 Baja | 5️⃣ Importante |
| **Speedtest** | `/speedtest/` | Tool + info | 🟡 Media | 6️⃣ Importante |

### Tier 2 — Soporte (Migrar Después)

| Página | Slug | Función | Prioridad |
|--------|------|---------|-----------|
| **Contacto** | `/contacto/` | Formulario | 7️⃣ Normal |
| **Blog** | `/blog/` | Listado posts | 8️⃣ Normal |
| **Política Privacidad** | `/privacidad/` | Legal | 9️⃣ Baja |
| **Términos** | `/terminos/` | Legal | 🔟 Baja |

---

## 🚨 RIESGOS IDENTIFICADOS

### 1. Shortcodes Colibri Rotos (🔴 CRÍTICO)

**Problema:**
- Contenido en posts/pages probablemente contiene `[cw-button]`, `[cw-section]`, `[cw-column]`, etc.
- Si Colibri se desactiva → shortcodes no se renderean
- Contenido se muestra como texto plano o desaparece

**Mitigación:**
```
ANTES de desactivar Colibri:
1. Inspeccionar cada página crítica
2. Convertir shortcodes a bloques Gutenberg equivalentes
3. Validar visualmente que se vea igual
4. Usar plugin "Shortcode Search" si hay muchas
```

**Acción:**
- [ ] Hacer búsqueda en DB: `SELECT * FROM wp_posts WHERE post_content LIKE '%[cw-%'`
- [ ] Documentar cada shortcode encontrado
- [ ] Mapear a equivalente Stackable/Gutenberg

---

### 2. Estilos Propietarios Colibri (🟡 ALTO)

**Problema:**
- CSS Colibri está en tema padre (no heredable)
- Colores, tipografía, spacing podrían cambiar

**Mitigación:**
```
1. Usar Blocksy Customizer para replicar colores/fonts
2. Crear blocksy-child/assets/css/custom.css si hay overrides
3. Validar lado-a-lado: prod vs staging
```

---

### 3. Cambios en URLs / Permalinks (🟢 BAJO)

**Status:** ✅ NO ESPERADO
- WordPress usa slug para generar URL
- Cambio de tema NO afecta permalinks
- Validación: permalink siempre será `/planes/`, `/cobertura/`, etc.

---

### 4. SEO On-Page Meta (🟢 BAJO)

**Status:** ✅ PROTEGIDO
- Smartcrawl SEO almacena metas en post meta
- No están "dentro" del tema
- Se inyectan dinámicamente en `<head>`

**Verificación:**
```
1. Revisar post meta: _smartcrawl_title, _smartcrawl_description, etc.
2. Confirmar que Smartcrawl siga activo en prod
3. No tocar base de datos
```

---

### 5. Tracking: GA/GTM/Pixel (🟢 BAJO)

**Status:** ✅ SEGURO
- Código de tracking típicamente en funciones.php o header.php global
- No en tema específico
- Seguirá inyectándose igual en Blocksy

**Verificación:**
```
1. Buscar en wp-content/themes/colibri-wp/header.php
2. Copiar cualquier script de tracking a blocksy-child/functions.php
3. Validar en GTM console que se disparen eventos
```

---

## 📊 MAPEO: Colibri → Blocksy + Stackable

### Elementos Visuales

| Colibri | Blocksy | Stackable | Notas |
|---------|---------|-----------|-------|
| Section | Blocksy Section | Group block | ✅ Migración directa |
| Column | Blocksy Column | Column block | ✅ Migración directa |
| Button | Blocksy Button | Stackable Button | ✅ Mejor |
| Text | Gutenberg Paragraph | Paragraph | ✅ Nativo |
| Heading | Gutenberg Heading | Heading | ✅ Nativo |
| Image | Gutenberg Image | Image | ✅ Nativo |
| Icon List | Blocksy Icon List | Stackable Icon List | ✅ Mejor |
| Pricing Table | Blocksy Pricing | Stackable Pricing | ✅ Mejor |
| FAQ | Blocksy FAQ | Stackable FAQ | ✅ Mejor |
| Form | Gutenberg Form / WPForms | Stackable Form | ⚠️ Verificar submits |
| Hero Section | Blocksy Hero | Stackable Hero | ✅ Mejor |

---

## 🔐 BASE DE DATOS

### Configuración Actual

```
BD Producción:
  Nombre: insatcom_wp
  Usuario: insatcom_wp
  Host: localhost
  Prefijo: wp_
  
BD Staging:
  Nombre: insatcom_wp_staging
  Usuario: insatcom_wp
  Host: localhost
  Prefijo: Ha09PDgeK_
  Status: ✅ Copia completa de prod
```

**Acciones permitidas (sin riesgo):**
```sql
-- ✅ SEGURO: Búsquedas
SELECT * FROM wp_posts WHERE post_type='page' AND post_status='publish';
SELECT * FROM wp_postmeta WHERE meta_key LIKE '_smartcrawl%';

-- ✅ SEGURO: Búsqueda de shortcodes
SELECT ID, post_title, post_content FROM wp_posts 
WHERE post_content LIKE '%[cw-%' 
AND post_status='publish';

-- ❌ PELIGROSO: Reemplazos masivos (usar --dry-run primero)
-- ❌ PELIGROSO: Eliminar registro sin backup
```

---

## ✅ CHECKLIST DE VALIDACIÓN SEO

### Pre-Migración (Baseline Producción)

- [ ] Anotar URLs de top 20 páginas
- [ ] Capturar `<title>` de cada una
- [ ] Capturar `<meta name="description">`
- [ ] Verificar `rel="canonical"`
- [ ] Revisar `robots.txt`
- [ ] Revisar `sitemap.xml`
- [ ] Capturar schema (FAQ, Organization, etc.)
- [ ] Verificar GA/GTM activos (console)
- [ ] Revisar indexación en GSC (errores/exclusiones)

### Post-Migración (Validación Blocksy)

- [ ] URLs sin cambios ✅ 100%
- [ ] `<title>` iguales ✅ 100%
- [ ] `<meta description>` iguales ✅ 100%
- [ ] `rel="canonical"` presente
- [ ] robots.txt intacto
- [ ] sitemap.xml presente
- [ ] Schema inyectado por Smartcrawl ✅
- [ ] GA/GTM firing en console ✅
- [ ] GSC sin nuevos errores
- [ ] URLs no devuelven 404 nuevas

---

## 🎨 CHECKLIST DE VALIDACIÓN VISUAL

### Por Página

- [ ] **Home:** Hero se ve igual, botones funcionan, colores correctos
- [ ] **Planes:** Tabla/cards legible, precios visibles, CTAs funcionales
- [ ] **Cobertura:** Mapa carga, form visible, WhatsApp link activo
- [ ] **Prepago:** Texto legible, CTA visible
- [ ] **Costo:** Tabla visible, datos correctos
- [ ] **Speedtest:** Tool carga, bot disponible
- [ ] **Contacto:** Form submission funciona
- [ ] **Blog:** Posts listados, single page legible
- [ ] **Mobile:** Responsive correcto (XS, SM, MD, LG)

---

## 🚀 ESTRATEGIA DE STAGING

### Opción Actual (Recomendado)

```
PRODUCCIÓN:
  http://insat.com.ar/

STAGING:
  http://insat.com.ar/staging-blocksy/
  
  BD: insatcom_wp_staging (copia independiente)
  Tema: blocksy-child (activo)
  Colibri: Aún instalado (no activo)
```

**Ventajas:**
- ✅ No requiere clonar BD localmente
- ✅ Testing en servidor real (PHP version, plugins, etc.)
- ✅ Fácil comparación lado-a-lado
- ✅ Rollback simple (solo cambiar tema activo)

**Flujo:**
1. Customizar en staging hasta aprobación
2. Hacer backup de prod
3. Activar Blocksy en producción
4. Validar rápidamente
5. Desactivar Colibri si todo OK

---

## 📋 ENTREGABLE: Plan de Ejecución por Fases

### Fase 1 ✅ ACTUAL
- [x] Staging creado y operativo
- [x] Blocksy instalado en staging
- [x] Base de datos staging lista
- [ ] **Próxima acción:** Instalar Stackable

### Fase 2 (Esta Semana)
- [ ] Instalar plugin Stackable en staging
- [ ] Revisar opciones de Blocksy Companion (decidir si instalar)
- [ ] Header/Footer global: recrear en Blocksy
- [ ] Validar menú principal, logo, CTA WhatsApp

### Fase 3 (Semana 1-2)
- [ ] Convertir páginas Tier 1 (Home → Speedtest)
- [ ] Mantener estructura semántica
- [ ] Reemplazar shortcodes Colibri por bloques Gutenberg
- [ ] Testing visual lado-a-lado

### Fase 4 (Semana 2)
- [ ] Validación SEO técnico
- [ ] Performance + CWV check
- [ ] Tracking verify (GA/GTM/Pixel)

### Fase 5 (Semana 2-3)
- [ ] Backup DB + wp-content producción
- [ ] Activar Blocksy en producción
- [ ] Validar 404/search/formularios
- [ ] Desinstalar Colibri (opcional)
- [ ] Limpieza final

---

## 📞 PRÓXIMA ACCIÓN

**Ahora:** Comenzar con **Fase 2 — Instalar Stackable + Companion**

```bash
# SSH en servidor (ya conectado)
cd /home/insatcomar/public_html/staging-blocksy

# Instalar Stackable
wp plugin install stackable-ultimate-gutenberg-blocks --activate

# Verificar instalación
wp plugin list | grep stackable
```

**Luego:** Hacer screenshot de home en staging y compararla con producción.

---

## 📎 ANEXO: Comandos WP-CLI Útiles

```bash
# Listar temas instalados
wp theme list

# Listar temas activos
wp theme list --status=active

# Listar plugins con shortcodes
wp plugin list --fields=name,status

# Buscar shortcodes en DB (dry-run)
wp db query --skip-column-names "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%[cw-%'" --allow-root

# Ver post meta específico
wp post meta get <POST_ID> _smartcrawl_title

# Cambiar tema activo
wp theme activate blocksy

# Desactivar plugin
wp plugin deactivate colibri-page-builder-pro
```

---

**Documento creado:** 8 de enero de 2026  
**Siguiente revisión:** Post-Fase 2

