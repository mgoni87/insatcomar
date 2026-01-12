# 📚 ÍNDICE DE DOCUMENTACIÓN — MIGRACIÓN BLOCKSY
**Proyecto:** INSAT WordPress Migration (Colibri → Blocksy + Gutenberg + Stackable)  
**Creado:** 8 enero 2026  
**Versión:** 1.0

---

## 📖 DOCUMENTOS CREADOS

### 1. 🔍 [FASE_0_DIAGNOSTICO_COMPLETO.md](FASE_0_DIAGNOSTICO_COMPLETO.md)
**Propósito:** Inventario técnico y análisis de riesgos  
**Contenido:**
- Estado actual del sitio (Colibri WP v1.0.144)
- Plugins detectados (Colibri Page Builder Pro, Smartcrawl SEO, etc)
- Identificación de páginas críticas
- Mapeo Colibri → Gutenberg + Stackable
- Riesgos identificados y mitigación
- Checklist de validación SEO
- BD configuration details

**Cuándo usarlo:** Primera consulta, comprensión general del proyecto

---

### 2. ✅ [FASE_1_VALIDACION_STAGING.md](FASE_1_VALIDACION_STAGING.md)
**Propósito:** Validación de infraestructura staging  
**Contenido:**
- Ubicación del staging (/staging-blocksy/)
- Validaciones completadas (BD, temas, plugins)
- Paso crítico: Instalación de Stackable
- Instrucciones de verificación visual
- Checklist de validación Fase 1

**Cuándo usarlo:** Después de conectar SSH, verificación infraestructura

---

### 3. 🚀 [PLAN_MAESTRO_MIGRACION_BLOCKSY.md](PLAN_MAESTRO_MIGRACION_BLOCKSY.md)
**Propósito:** Plan completo de ejecución de 3 semanas  
**Contenido:**
- Timeline detallado (Fase 1-5)
- Próximos pasos inmediatos
- Fase 3: Migración de contenido (Home, Planes, etc)
- Fase 4: Validación SEO + Performance
- Fase 5: Switch a producción
- Troubleshooting rápido
- Comandos WP-CLI de referencia

**Cuándo usarlo:** Guía principal de ejecución, consultarlo diariamente

---

### 4. 📊 [RESUMEN_EJECUTIVO_MIGRACION.md](RESUMEN_EJECUTIVO_MIGRACION.md)
**Propósito:** Resumen ejecutivo de alto nivel  
**Contenido:**
- Objetivo final (Blocksy + Gutenberg + Stackable)
- Lo que se ha hecho (Fase 0-1)
- Próximas acciones (ACCIÓN 1-4)
- Documentación creada
- Definición de éxito por fase
- Riesgos mitigados

**Cuándo usarlo:** Actualización rápida, reporte a stakeholders

---

### 5. ⚡ [QUICK_REFERENCE_CARD.md](QUICK_REFERENCE_CARD.md)
**Propósito:** Cheat sheet con comandos y URLs críticas  
**Contenido:**
- URLs críticas (prod vs staging)
- Comandos WP-CLI esenciales
- Blocksy customization paths
- Páginas críticas a migrar
- Shortcode mapping (Colibri → Gutenberg)
- Pre-flight checklist
- Performance tools
- SEO validation checklist

**Cuándo usarlo:** Consulta rápida mientras trabajas, tenerlo a mano

---

### 6. 📝 [scripts/migration-blocksy-phase2.sh](scripts/migration-blocksy-phase2.sh)
**Propósito:** Script bash automatizado para Fase 2  
**Contenido:**
- Instalación de Stackable
- Validación plugins
- Opcional: Blocksy Companion
- Verificación final

**Cuándo usarlo:** SSH, ejecutar: `bash scripts/migration-blocksy-phase2.sh`

---

## 🗺️ RECOMENDACIÓN DE LECTURA

### Si tienes 5 minutos:
→ Lee [RESUMEN_EJECUTIVO_MIGRACION.md](RESUMEN_EJECUTIVO_MIGRACION.md)

### Si tienes 30 minutos:
→ Lee [FASE_0_DIAGNOSTICO_COMPLETO.md](FASE_0_DIAGNOSTICO_COMPLETO.md) + [QUICK_REFERENCE_CARD.md](QUICK_REFERENCE_CARD.md)

### Si necesitas ejecutar algo:
→ Consulta [PLAN_MAESTRO_MIGRACION_BLOCKSY.md](PLAN_MAESTRO_MIGRACION_BLOCKSY.md) (secciones pertinentes)

### Si necesitas un comando rápido:
→ Abre [QUICK_REFERENCE_CARD.md](QUICK_REFERENCE_CARD.md) y busca en la sección relevante

### Si necesitas entender las fases:
→ Lee [PLAN_MAESTRO_MIGRACION_BLOCKSY.md](PLAN_MAESTRO_MIGRACION_BLOCKSY.md) secciones FASE 1-5

---

## 📋 TABLA DE CONTENIDOS VISUAL

```
MIGRACIÓN BLOCKSY
├── DIAGNÓSTICO (Fase 0)
│   └─ FASE_0_DIAGNOSTICO_COMPLETO.md
│      (Inventario, riesgos, mapeo)
│
├── VALIDACIÓN (Fase 1)
│   └─ FASE_1_VALIDACION_STAGING.md
│      (Infraestructura OK)
│
├── EJECUCIÓN (Fases 2-5)
│   ├─ PLAN_MAESTRO_MIGRACION_BLOCKSY.md
│   │  (Timeline 3 semanas)
│   │
│   ├─ RESUMEN_EJECUTIVO_MIGRACION.md
│   │  (Overview + acciones inmediatas)
│   │
│   └─ QUICK_REFERENCE_CARD.md
│      (Cheat sheet + comandos)
│
├── AUTOMATIZACIÓN
│   └─ scripts/migration-blocksy-phase2.sh
│      (Instalación Stackable)
│
└─ TÚ ESTÁS AQUÍ
   └─ INDICE_DOCUMENTACION.md (este archivo)
```

---

## 🎯 PRÓXIMAS FASES (Documentación a Crear)

### Fase 3 — Migración Visual (Próxima semana)
```
FASE_3_MIGRACION_VISUAL.md
├── Header/Footer conversion
├── Home page conversion
├── Planes → Speedtest conversion
├── Shortcode cleanup
└── Screenshots & validation
```

### Fase 4 — Validación SEO + Performance (Semana 2)
```
FASE_4_VALIDACION_SEO_PERFORMANCE.md
├── SEO checklist (URLs, metas, schema)
├── Performance audit (CWV, metrics)
├── Tracking verification (GA/GTM/Pixel)
└── Remediation if needed
```

### Fase 5 — Producción (Semana 3)
```
FASE_5_SWITCH_PRODUCCION.md
├── Pre-switch backup procedures
├── Theme activation commands
├── Post-switch validation
├── Rollback procedure
└── Colibri deinstallation
```

---

## 📌 INFORMACIÓN CRÍTICA (Resumen Rápido)

### Ubicaciones Críticas
```
Servidor: 149.50.143.84 (SSH puerto 5156)
Producción: /home/insatcomar/public_html/
Staging: /home/insatcomar/public_html/staging-blocksy/
```

### Bases de Datos
```
BD Producción: insatcom_wp
BD Staging: insatcom_wp_staging (copia independiente)
Usuario: insatcom_wp
```

### Temas
```
Actual (producción): colibri-wp (v1.0.144)
Objetivo (staging): blocksy-child (activo), blocksy (padre)
Backup: colibri-wp aún disponible en staging
```

### Plugins Importantes
```
✅ MANTENER: smartcrawl-seo (SEO on-page)
✅ MANTENER: hummingbird-performance (cache)
✅ INSTALAR: stackable-ultimate-gutenberg-blocks
⚠️ DESACTIVAR: colibri-page-builder-pro (antes de desinstalar)
```

---

## 🚀 CHECKLIST: Documentación Completa

- [x] Fase 0 — Diagnóstico (COMPLETO)
- [x] Fase 1 — Validación Staging (COMPLETO)
- [x] Plan Maestro (COMPLETO)
- [x] Resumen Ejecutivo (COMPLETO)
- [x] Quick Reference (COMPLETO)
- [ ] Fase 3 — Migración Visual (A crear próxima semana)
- [ ] Fase 4 — SEO + Performance (A crear próxima semana)
- [ ] Fase 5 — Producción (A crear próxima semana)

---

## 📞 CÓMO USAR ESTA DOCUMENTACIÓN

### Para Un Ingeniero Junior
1. Lee RESUMEN_EJECUTIVO_MIGRACION.md
2. Lee FASE_0_DIAGNOSTICO_COMPLETO.md (secciones de riesgos)
3. Consulta QUICK_REFERENCE_CARD.md para comandos
4. Sigue PLAN_MAESTRO_MIGRACION_BLOCKSY.md

### Para Un QA / Tester
1. Consulta QUICK_REFERENCE_CARD.md (páginas críticas)
2. Usa checklist visual en FASE_0_DIAGNOSTICO_COMPLETO.md
3. Compara prod vs staging lado-a-lado
4. Documenta diferencias en screenshots

### Para Un Manager / Stakeholder
1. Lee RESUMEN_EJECUTIVO_MIGRACION.md
2. Revisa timeline en PLAN_MAESTRO_MIGRACION_BLOCKSY.md
3. Consulta definiciones de éxito por fase
4. Pide reportes semanales usando template en QUICK_REFERENCE_CARD.md

### Para El Dueño del Proyecto
1. RESUMEN_EJECUTIVO_MIGRACION.md (overview)
2. Seguimiento con PLAN_MAESTRO_MIGRACION_BLOCKSY.md (timeline)
3. Validación final en Fase 5 checklist

---

## 💾 UBICACIÓN DE ARCHIVOS

```
/Users/mariano/Documents/GitHub/insatcomar/

├── FASE_0_DIAGNOSTICO_COMPLETO.md          ✅
├── FASE_1_VALIDACION_STAGING.md            ✅
├── PLAN_MAESTRO_MIGRACION_BLOCKSY.md       ✅
├── RESUMEN_EJECUTIVO_MIGRACION.md          ✅
├── QUICK_REFERENCE_CARD.md                 ✅
├── INDICE_DOCUMENTACION.md                 ✅ (este archivo)
│
└── scripts/
    └── migration-blocksy-phase2.sh          ✅
```

---

## 📈 VERSIÓN Y CONTROL DE CAMBIOS

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 8 ene 2026 | Creación inicial con Fase 0-1 + Plan Maestro |
| 1.1 | [TBD] | Fase 3 documentation |
| 1.2 | [TBD] | Fase 4 documentation |
| 1.3 | [TBD] | Fase 5 documentation |

---

## 🎯 SIGUIENTE ACCIÓN

**Ejecuta ahora:**

```bash
# 1. Conectar SSH
ssh -p5156 root@149.50.143.84

# 2. Ir a staging
cd /home/insatcomar/public_html/staging-blocksy/

# 3. Instalar Stackable
wp plugin install stackable-ultimate-gutenberg-blocks --activate

# 4. Verificar
wp plugin list | grep stackable
```

**Luego:** Reportar resultado (✅ / ❌)

---

**Documentación versión:** 1.0  
**Creada:** 8 enero 2026  
**Próxima actualización:** Post-Fase 3

