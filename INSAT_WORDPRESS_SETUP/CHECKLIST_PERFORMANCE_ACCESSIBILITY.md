# ✅ CHECKLIST: PERFORMANCE + ACCESIBILIDAD

## 📊 CORE WEB VITALS (Lighthouse)

### LCP (Largest Contentful Paint) < 2.5s
- [ ] Optimizar imágenes (WebP + lazy load nativo)
- [ ] Preload recursos críticos (fonts, hero image)
- [ ] Eliminar render-blocking resources innecesarios
- [ ] Implementar caching HTTP (Browser + Server)
- [ ] Usar CDN si es posible

### FID (First Input Delay) < 100ms
- [ ] Minimizar JavaScript principal
- [ ] Usar defer/async en scripts
- [ ] Code splitting de bundles grandes
- [ ] Usar requestIdleCallback para tasks no críticas

### CLS (Cumulative Layout Shift) < 0.1
- [ ] Fijar dimensiones de imágenes (width/height)
- [ ] Evitar ads/overlays que aparecen sin espacio reservado
- [ ] Font loading con display: swap
- [ ] Fijar tamaños de iframes

---

## 🖼️ OPTIMIZACIÓN DE IMÁGENES

- [ ] Convertir a WebP + fallback JPG/PNG
  ```html
  <picture>
    <source srcset="image.webp" type="image/webp">
    <img src="image.jpg" alt="Descripción" loading="lazy">
  </picture>
  ```
- [ ] Lazy load nativo en todas las imágenes
  ```html
  <img src="..." loading="lazy">
  ```
- [ ] Responsive images con srcset
  ```html
  <img srcset="small.jpg 480w, medium.jpg 768w, large.jpg 1200w" />
  ```
- [ ] Compression: máximo 50kb para Hero, 30kb para thumbs
- [ ] Dimensiones correctas (no subir 2000x2000 si se muestra 400x400)

---

## 🔤 TIPOGRAFÍA & FONTS

- [ ] Usar fuentes locales (NO Google Fonts CDN)
  - Descargar Inter Regular, SemiBold, Bold
  - Incluir en `/assets/fonts/`
  - @font-face con font-display: swap
- [ ] Limitar a máximo 3 familias tipográficas
- [ ] Peso correcto: 400, 600, 700
- [ ] Evitar FOIT/FOUT:
  ```css
  @font-face {
    font-family: 'Inter';
    src: url('inter-regular.woff2') format('woff2');
    font-display: swap;
  }
  ```

---

## 💾 CACHING & PERFORMANCE

### Browser Cache
- [ ] Cache-Control headers para assets static:
  ```
  Cache-Control: public, max-age=31536000 (1 año)
  ```
- [ ] HTML no cachear o max-age: 3600 (1 hora)

### Server Cache
- [ ] Object caching: Redis o Memcached
- [ ] Page caching: WP Super Cache o LiteSpeed Cache
- [ ] Cache invalidation en post edits

### Database Optimization
- [ ] Revisar queries lentas (query monitor)
- [ ] Indexar taxonomías/post metas usadas frecuentemente
- [ ] Limpiar revisiones de posts antiguas

---

## 📦 CODE MINIFICATION

- [ ] CSS minificado
- [ ] JavaScript minificado
- [ ] HTML minificado (WP plugin)
- [ ] Eliminar CSS no usado (PurgeCSS)
- [ ] Eliminar JavaScript no usado

---

## ⚡ CRITICAL CSS

- [ ] Extraer CSS crítico para hero/fold
- [ ] Inline en <head>
- [ ] Defer resto de CSS

```html
<style>
  /* CRITICAL CSS inline */
  :root { --color-accent: #5F0ED5; }
  .hero { min-height: 100vh; }
</style>
<link rel="preload" href="main.css" as="style">
<noscript><link rel="stylesheet" href="main.css"></noscript>
```

---

## 🎯 ACCESIBILIDAD (WCAG 2.1 AA)

### Contraste
- [ ] Texto normal: 4.5:1 contraste mínimo
  - [ ] Validar con WebAIM Contrast Checker
  - [ ] #050505 en #FFFFFF = ✅ (21:1)
  - [ ] rgba(255,255,255,0.7) en #050505 = ✅ (8.5:1)
- [ ] Texto grande (18pt+): 3:1 mínimo
- [ ] Componentes gráficos: 3:1 mínimo (bordes, iconos)

### Foco Visible
- [ ] Todos los elementos interactivos tienen focus visible:
  ```css
  a:focus-visible {
    outline: 2px solid var(--color-accent);
    outline-offset: 2px;
  }
  ```
- [ ] Focus orden lógico (no modificar tabindex)
- [ ] No remover outline nativo sin reemplazo

### Navegación Teclado
- [ ] Accesible solo con teclado (Tab, Enter, Esc)
- [ ] Menús navegables con arrow keys
- [ ] Cerrar modales con Esc
- [ ] Skip to main content link:
  ```html
  <a href="#main" class="skip-to-main">Skip to main</a>
  ```

### Semántica HTML
- [ ] `<nav>` para navegación
- [ ] `<main>` para contenido principal
- [ ] `<header>`, `<footer>`, `<article>`, `<section>`
- [ ] `<button>` para acciones
- [ ] `<a>` para navegación
- [ ] Headings jerárquicos: h1 → h6 (NO saltar niveles)
- [ ] `<img alt="descripción">` en todas las imágenes
- [ ] Atributos `for` en `<label>`

### ARIA (solo si es necesario)
- [ ] `aria-label` para iconos sin texto
- [ ] `aria-describedby` para instrucciones de form
- [ ] `aria-required="true"` en campos requeridos
- [ ] `role` solo cuando HTML5 semántico no es suficiente

### Imágenes
- [ ] Alt text descriptivo (no vacío, no "image.jpg")
  ```html
  <img alt="Router WiFi 6 blanco incluido en el kit" src="router.jpg">
  ```
- [ ] `<figure>` + `<figcaption>` para imágenes con leyenda
- [ ] Decorativas: `alt=""` + `aria-hidden="true"`

### Formularios
- [ ] `<label>` asociada a input con `for="id"`
  ```html
  <label for="email">Email:</label>
  <input id="email" type="email" />
  ```
- [ ] Campos requeridos: `required` + `aria-required="true"`
- [ ] Errores claros y visibles
  ```html
  <span id="error-email" role="alert">Email inválido</span>
  <input aria-invalid="true" aria-describedby="error-email">
  ```
- [ ] Placeholders NO reemplazan labels
- [ ] Error messages en color + icono (no solo color)

### Color
- [ ] No depender únicamente de color
- [ ] Usar iconos + etiquetas + color
- [ ] Suficiente contraste (test: WebAIM)

### Animaciones
- [ ] Respetar `prefers-reduced-motion`
  ```css
  @media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important; }
  }
  ```
- [ ] Evitar parpadeos > 3/segundo
- [ ] Animaciones no autoplay (a no ser que sea crítica)

### Video & Audio
- [ ] Video: subtítulos (CC) + descripción de audio
- [ ] Audio: transcripción
- [ ] Controles accesibles (play/pause con keyboard)

### Touch & Mobile
- [ ] Tamaño mínimo de botones: 44x44px
- [ ] Espaciado suficiente entre elementos clickeables
- [ ] Viewport meta configurado: `<meta name="viewport" content="width=device-width, initial-scale=1">`
- [ ] Funciona sin zoom (pinch-zoom permitido)

---

## 🧪 HERRAMIENTAS DE TEST

### Lighthouse (Chrome DevTools)
- [ ] Performance: > 90
- [ ] Accessibility: > 90
- [ ] Best Practices: > 90
- [ ] SEO: > 90

### WAVE (WebAIM)
- [ ] Instalar extensión Chrome
- [ ] 0 errores críticos
- [ ] Revisar warnings

### axe DevTools
- [ ] Instalar extensión Chrome
- [ ] 0 violations críticas
- [ ] Revisar best-practices

### Manual Testing
- [ ] [ ] Keyboard navigation completa (Tab, Enter, Esc)
- [ ] [ ] Screen reader (VoiceOver en Mac, NVDA en Windows)
  - [ ] Test orden de lectura
  - [ ] Test labels en inputs
  - [ ] Test headings jerarquía
- [ ] [ ] Zoom al 200% (text/layout responsive)
- [ ] [ ] High contrast mode (Windows)
- [ ] [ ] Retirar imágenes (funciona?)
- [ ] [ ] Retirar CSS (contenido legible?)

### Contrast Checkers
- [ ] WebAIM Color Contrast Checker
- [ ] Stark plugin
- [ ] Accessible Colors

### Mobile & Responsive
- [ ] Chrome DevTools: Responsive Design Mode
  - [ ] 320px (mobile)
  - [ ] 768px (tablet)
  - [ ] 1024px (desktop)
  - [ ] 1440px (wide)
- [ ] Test en devices reales si es posible

---

## 📋 RESULTADO ESPERADO

```
Lighthouse Scores:
✅ Performance:     > 90
✅ Accessibility:   > 90
✅ Best Practices:  > 90
✅ SEO:            > 90

Manual Tests:
✅ Teclado: Navega completamente
✅ Screen Reader: Lee correctamente
✅ Contraste: > 4.5:1 (normal), > 3:1 (componentes)
✅ Responsive: OK en 320px, 768px, 1024px, 1440px
```

---

## 🚨 ERRORES COMUNES A EVITAR

❌ Remover outline sin reemplazo
❌ Usar tabindex > 0
❌ Color únicamente para diferenciar info
❌ Alt text vacío en imágenes importantes
❌ Saltar niveles de headings
❌ Inputs sin labels
❌ Scripts que bloquean rendering
❌ Imágenes sin dimensiones (layout shift)
❌ Fuentes de CDN sin display: swap
