# INSAT Staging - Arquitectura Visual

## 🏗️ Estructura del Sitio

```
https://stag.insat.com.ar/
│
├── 🏠 HOMEPAGE (WordPress Home)
│
├── 📄 PAGES (Páginas Principales)
│   ├── /hogares/
│   ├── /internet-ilimitado/
│   ├── /tv-satelital/
│   ├── /wifi-hogar/
│   ├── /empresa/
│   ├── /soporte/
│   ├── /blog/
│   ├── /faq/
│   └── /hogares/internet-ilimitado/ (subpágina)
│
├── 📰 NOVEDADES (CPT)
│   ├── /novedades/ (archivo)
│   └── [posts individuales]
│
├── 🔬 TECNOLOGÍA (CPT)
│   ├── /tecnologia/ (archivo)
│   └── [posts individuales]
│
└── 📖 HISTORIAS (CPT)
    ├── /historias/ (archivo)
    └── [posts individuales]
```

---

## 🎨 Paleta de Colores

```
PRIMARY: #5F0ED5 (Púrpura INSAT) ← Botones, CTAs, highlights
BG DARK: #050505 (Casi Negro) ← Fondo principal
BG SEC:  #0B0B0B (Negro secundario) ← Cards, sections
TEXT:    #FFFFFF (Blanco puro) ← Texto principal
BORDER:  rgba(255,255,255,0.12) ← Divisores
```

---

## 🔌 Componentes CSS

### Hero Section
```
┌─────────────────────────────────┐
│  Título grande (3.5rem)         │
│  Subtítulo (1.75rem)            │
│  [VER PLANES] [CONTACTANOS]     │
└─────────────────────────────────┘
Background: Gradient #050505 → #0B0B0B
Min-height: 100vh
```

### Cards
```
┌─────────────────────┐
│  Título             │
│  Descripción        │
│  [BOTÓN PÚRPURA]    │
└─────────────────────┘
Border: 1px white 12%
Hover: Border → púrpura, +4px lift
```

### Grid System
```
.grid-2 → 2 columnas responsive
.grid-3 → 3 columnas responsive
.grid-4 → 4 columnas responsive
```

---

## 📊 Contenido

```
PÁGINAS: 11 total
├─ 8 páginas principales
└─ 3 subpáginas

CPT POSTS: 12 total
├─ Novedades: 4
├─ Tecnología: 4
└─ Historias: 4

TAXONOMIES: 2
├─ cpt-category
└─ cpt-tag
```

---

## 📁 Estructura de Archivos

```
/wp-content/themes/blocksy-child/
├── style.css ........... Header + CSS tokens + componentes
└── functions.php ....... CPTs + SEO safety

/wp-content/plugins/insat-patterns/
└── insat-patterns.php .. 6 Gutenberg patterns
```

---

## 🚀 Próximos Pasos

STAGING (actual) ✅
↓
FASE 2: Validación Visual 👀
↓
FASE 3: Producción 🚀
