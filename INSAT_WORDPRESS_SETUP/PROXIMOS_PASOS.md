# 🎯 PRÓXIMOS PASOS: INSAT WordPress Implementation

## 📋 TAREAS INMEDIATAS (Esta semana)

### Semana 1: SETUP & STAGING

#### Lunes
- [ ] Descargar/clonar carpeta `INSAT_WORDPRESS_SETUP` desde GitHub
- [ ] Revisar `README.md` + `INDICE_COMPLETO.md`
- [ ] Familiarizarse con estructura del theme

#### Martes
- [ ] **SSH al servidor**: `ssh -p5156 root@149.50.143.84`
- [ ] **Generar .htpasswd**: `htpasswd -c /home/insatcomar/.htpasswd admin`
- [ ] **Copiar files**:
  - [ ] `blocksy-child/` → `/wp-content/themes/`
  - [ ] `robots-staging.txt` → `/cobertura/robots.txt`
  - [ ] Contenido de `htaccess-staging.txt` → `/cobertura/.htaccess`
  - [ ] Contenido de `wp-config-fragment.php` → `/wp-config.php` (final)

#### Miércoles
- [ ] **WP Admin**: Activar theme "Blocksy Child - INSAT"
- [ ] **Configuración → Lectura**: Marcar "Disuadir buscadores"
- [ ] **Validar patterns** en Gutenberg (aparecen 7)
- [ ] **Test noindex**: Verificar headers, robots.txt, meta robots

#### Jueves
- [ ] **Crear contenido ejemplo**:
  - [ ] 2 posts en Novedades
  - [ ] 2 posts en Tecnología
  - [ ] 2 posts en Historias
- [ ] **Crear menús**:
  - [ ] Menú Principal (Hogares, Empresas, etc.)
  - [ ] Menú Utilitario (Zona de Clientes)

#### Viernes
- [ ] **Test completo**:
  - [ ] Lighthouse ≥ 90
  - [ ] Responsive en mobile
  - [ ] Accesibilidad (WAVE, axe)
  - [ ] Verificar HTTP Auth + noindex activos

---

## 🏗️ ARQUITECTURA PAGES (Semana 2-3)

### Páginas a Crear

#### HOME (/)
Usar patterns:
1. Hero Fullscreen
2. Cards Planes (3 opciones)
3. Verificá Cobertura
4. Instalación 3 Pasos
5. Qué Incluye Kit
6. Editorial (últimas 3)
7. Footer Newsletter

**Copy**: Redactar original INSAT (NO Starlink)

---

#### HOGARES
Estructura:
```
/hogares/
├── Hero: "Planes para tu Hogar"
├── Introducción al servicio
├── Grid 3 planes (hero + prices + CTAs)
├── Ventajas (4-5 items)
└── CTA final
```

Subpáginas:
- `/hogares/internet-ilimitado/` (Plan 50 Mbps)
- `/hogares/internet-ilimitado-tv/` (Plan 100 + TV)
- `/hogares/wifi-plus-mesh/` (Plan 150 + Mesh)

Cada una: Hero específico + especificaciones + FAQ

---

#### EMPRESAS
Estructura:
```
/empresas/
├── Hero: "Conectividad Empresarial"
├── Casos de uso (grid)
├── Planes B2B
├── Ventajas SLA
├── Contacto directo
```

---

#### COBERTURA
Estructura:
```
/cobertura/
├── Hero: "¿Dónde podés contratar?"
├── Mapa interactivo (Leaflet u OSM)
├── Formulario verificación:
   - Input: dirección
   - Input: código postal
   - Button: Verificar
├── Resultado en tiempo real
└── Lead capture (guardar en BD)
```

**MVP**: Formulario → Lead capture (nombre, email, dirección)

---

#### ESPECIFICACIONES
Estructura:
```
/especificaciones/
├── Tabla: Planes vs características
├── Specs técnicas (velocidad, latencia, etc.)
├── FAQ técnico
```

---

#### SOPORTE
Estructura:
```
/soporte/
├── Hero: "¿Necesitás ayuda?"
├── Opciones contacto (email, tel, chat)
├── Links a subpáginas
```

Subpáginas:
- `/soporte/preguntas-frecuentes/` → Accordion/collapse
- `/soporte/evita-estafas/` → Información de seguridad

---

#### LEGALES
- `/legal/terminos/` - T&C
- `/legal/privacidad/` - Privacy Policy
- `/legal/cookies/` - Cookie Policy

---

## 🎨 CONTENIDO & COPY

### Investigación Requerida
- [ ] Revisar sitio actual: insat.com.ar
- [ ] Entender propuesta de valor INSAT
- [ ] Velocidades reales, cobertura, pricing
- [ ] Diferenciadores vs competencia

### Copy Original
- [ ] **NO copiar** textos/imágenes de Starlink
- [ ] Redactar en **español Argentina**
- [ ] Tono: profesional + accesible
- [ ] Enfoque: "Internet Satelital Ilimitada + TV"
- [ ] CTA claro: "Verificar cobertura" → "Contratar"

### Assets
- [ ] Logo INSAT (blanco): Ya linqueado
- [ ] Favicon: Ya linqueado
- [ ] Imágenes producto (router, antena, etc.): Crear/obtener propias
- [ ] Iconos: Usar iconografía consistente (SF Symbols o Feather)
- [ ] Videos (opcional): Product demo

---

## 🗺️ ESTRUCTURA VISUAL (DESIGN SYSTEM)

### Paleta
```
Fondo:    #050505 (dark matter)
Texto:    #FFFFFF (light)
Acento:   #5F0ED5 (purple)
Hover:    #671AD6 (purple darker)
Border:   rgba(255,255,255,.12) (subtle)
```

### Tipografía
- **Headings**: Inter SemiBold
- **Body**: Inter Regular
- **Código**: Courier New Monospace

### Spacing
- xs: 0.25rem
- sm: 0.5rem
- md: 1rem
- lg: 1.5rem
- xl: 2rem
- 2xl: 3rem

---

## ✅ VALIDACIONES CLAVE

### Antes de Presentar
- [ ] Lighthouse: ≥ 90 (todas las métricas)
- [ ] Accesibilidad: ≥ 90 (WCAG 2.1 AA)
- [ ] Responsive: OK en 320px, 768px, 1024px, 1440px
- [ ] HTTP Auth activo (admin/admin)
- [ ] NOINDEX confirmado:
  - [ ] Meta robots
  - [ ] X-Robots-Tag header
  - [ ] robots.txt
  - [ ] WP Settings
- [ ] Canónicos → cobertura.insat.com.ar
- [ ] Emails NO se envían (loguean)
- [ ] Copy sin errores ortográficos
- [ ] Links internos funcionan
- [ ] Mobile touch-friendly

---

## 📊 PERFORMANCE OPTIMIZATION

### Imágenes
- [ ] Convertir a WebP + fallback
- [ ] Lazy load nativo (`loading="lazy"`)
- [ ] Responsive srcset
- [ ] Comprimir (< 50kb hero, < 30kb thumbs)

### Code
- [ ] Minificar CSS/JS
- [ ] Async/defer en scripts
- [ ] Critical CSS inline
- [ ] Eliminar unused CSS

### Caching
- [ ] Browser: 1 año para assets estáticos
- [ ] Server: 1 hora para HTML
- [ ] Database queries optimizadas

### Fuentes
- [ ] Inter: descargar local (NO CDN)
- [ ] @font-display: swap
- [ ] Solo pesos 400, 600, 700

---

## 🔒 SEGURIDAD ANTES DE PRODUCCIÓN

- [ ] Cambiar admin/admin → credencial fuerte
- [ ] Cambiar contraseña BD
- [ ] HTTPS activo + SSL válido
- [ ] Firewall/ModSecurity configurado
- [ ] WP_DEBUG = false
- [ ] Backups automáticos activos

---

## 🚀 PLAN MIGRACION A PRODUCCION (Semana 4)

1. **Pre-launch (Día 1)**
   - [ ] Todos los checks verdes
   - [ ] Backups listos
   - [ ] Rollback plan documentado

2. **Launch (Día 1 tarde)**
   - [ ] Retirar HTTP Basic Auth
   - [ ] Activar NOINDEX → false en WP Settings
   - [ ] Canónicos → insat.com.ar
   - [ ] Robots.txt → permitir indexación
   - [ ] Generar sitemap
   - [ ] Conectar GA4 real
   - [ ] Conectar GTM real

3. **Post-launch (48hs)**
   - [ ] Monitoreo 24/7
   - [ ] Google Search Console: Submit sitemap
   - [ ] Verificar indexación
   - [ ] Analytics: datos llegando
   - [ ] Error logs: 0 críticos

---

## 📞 CONTACTOS CLAVE

| Rol | Contacto | Responsabilidad |
|-----|----------|-----------------|
| Servidor | root@149.50.143.84:5156 | SSH, ficheros |
| Domain | insat.com.ar | DNS, SSL |
| Staging | cobertura.insat.com.ar (auth) | Tests, desarrollo |
| Producción | insat.com.ar | Live |

---

## 📚 RECURSOS INTERNOS

| Documento | Propósito |
|-----------|-----------|
| README.md | Guía rápida 10 pasos |
| INDICE_COMPLETO.md | Inventario completo |
| CHECKLIST_STAGING_SETUP.md | Setup paso a paso |
| CHECKLIST_PERFORMANCE_ACCESSIBILITY.md | Validación técnica |
| CHECKLIST_PRODUCCION.md | Pre-launch |

---

## 🎯 TIMELINE ESTIMADO

| Semana | Tarea | Duración |
|--------|-------|----------|
| 1 | Setup staging + theme | 4-5 días |
| 2-3 | Arquitectura + páginas | 10-12 días |
| 3-4 | Contenido + copy | 3-5 días |
| 4 | Testing + optimización | 3-4 días |
| 4 | Migracion a producción | 1 día |
| **Total** | | **~3-4 semanas** |

---

## ✨ LISTO PARA COMENZAR

**Próximo paso**: Ejecutar [CHECKLIST_STAGING_SETUP.md](CHECKLIST_STAGING_SETUP.md) paso 1-10

💡 **Soporte**: Revisar documentación en caso de dudas. Todos los archivos están listos para copy-paste.

---

**Fecha inicio estimada**: 11 de enero, 2026
**Go-live target**: Finales de enero / principios de febrero 2026
