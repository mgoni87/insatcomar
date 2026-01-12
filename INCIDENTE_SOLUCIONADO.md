# 🚨 INCIDENTE SOLUCIONADO - STAGING RESTAURADO

**Fecha:** 11 de Enero de 2026 - 23:00 GMT-3
**Estado:** ✅ RESUELTO
**Tiempo de recuperación:** ~5 minutos

---

## ¿QUÉ PASÓ?

Durante la migración a producción, la carpeta **`/cobertura.insat.com.ar`** se quedó vacía, causando errores HTTP 403 Forbidden en el staging.

### Cronología:
1. **23:00** - Deployment completado en producción (insat.com.ar) ✅
2. **23:15** - Usuario reporta staging vacío (cobertura.insat.com.ar) 🔴
3. **23:20** - Diagnóstico: carpeta cobertura.insat.com.ar estaba sin contenido
4. **23:25** - Ejecución de rsync para sincronizar desde staging-blocksy-full
5. **23:30** - Ambos sitios confirmados operativos ✅

---

## 🔍 CAUSA RAÍZ

La estructura del servidor tiene múltiples directorios:
- `/public_html/` - Dominio principal insat.com.ar
- `/public_html/staging-blocksy-full/` - Backup/staging de referencia
- `/public_html/cobertura.insat.com.ar/` - **Carpeta apuntada por VirtualHost de staging**

Durante deployment, **SOLO copiamos archivos a insat.com.ar**, sin verificar que cobertura.insat.com.ar también necesitaba contenido.

---

## ✅ SOLUCIÓN APLICADA

**Comando ejecutado:**
```bash
rsync -av --delete \
  /home/insatcomar/public_html/staging-blocksy-full/ \
  /home/insatcomar/public_html/cobertura.insat.com.ar/
```

**Resultado:**
- Sincronizados 1,347 archivos (299 MB)
- Velocidad de transferencia: 120 MB/s
- Status: Completado exitosamente

---

## 📋 VERIFICACIÓN POST-RECUPERACIÓN

### ✅ PRODUCCIÓN
```
URL: https://insat.com.ar
Status: HTTP 200 OK
Title: Internet Satelital en Argentina al Mejor Precio ▶ INSAT® Te llega
Theme: blocksy-child ✅
CSS: https://insat.com.ar/wp-content/themes/blocksy-child/style.css (5.9KB) ✅
robots.txt: Allow: / (indexación habilitada) ✅
```

### ✅ STAGING
```
URL: https://cobertura.insat.com.ar
Status: HTTP 200 OK
Title: INSAT Staging
Theme: blocksy (parent, correcto para staging) ✅
Auth: HTTP Basic (admin/admin) ✅
NOINDEX: Activo ✅
```

---

## 📊 IMPACTO

| Aspecto | Antes | Después |
|---------|-------|---------|
| Prod Status | 200 OK | 200 OK ✅ |
| Staging Status | 403 Forbidden ❌ | 200 OK ✅ |
| Theme CSS | Activo | Activo ✅ |
| Contenido | - | Restaurado ✅ |
| NOINDEX Staging | - | Confirmado ✅ |

---

## 🛡️ MEDIDAS PREVENTIVAS PARA FUTURO

1. **Crear script de verificación de ambos sitios:**
   ```bash
   #!/bin/bash
   curl -s -I https://insat.com.ar | grep "200\|301\|302" || echo "PROD DOWN"
   curl -s -I https://cobertura.insat.com.ar | grep "200\|301\|302" || echo "STAGING DOWN"
   ```

2. **Documentar estructura VirtualHost:**
   - Crear mapa de todos los subdomios y sus carpetas asignadas
   - Mantener checklist de sincronización

3. **Backup automático:**
   - Configurar daily backup de ambos ambientes
   - rsync scheduled cada 6 horas

4. **Monitoreo:**
   - Alertas HTTP para 403+ en ambos dominios
   - Verificación de tamaño de carpetas (alerta si <100MB)

---

## 🎯 ESTADO ACTUAL

```
✅ Producción: Operativa
   - Sitio principal: LIVE
   - Theme custom: blocksy-child
   - Indexación: Habilitada
   - Performance: Normal

✅ Staging: Restaurado
   - Disponible para testing
   - Contenido: Sincronizado
   - NOINDEX: Protegido
   - Auth: admin/admin
```

---

## 📝 PRÓXIMOS PASOS

1. ✅ **COMPLETADO**: Restaurar staging
2. ⏳ **PENDIENTE**: Cambiar credenciales admin (security)
3. ⏳ **PENDIENTE**: Generar sitemap XML
4. ⏳ **PENDIENTE**: Enviar a Google Search Console
5. ⏳ **PENDIENTE**: Test Lighthouse en producción

---

**Resumen:** El incidente fue aislado al staging. La producción nunca estuvo en riesgo. Ambos sitios están ahora operativos y sincronizados. ✅
