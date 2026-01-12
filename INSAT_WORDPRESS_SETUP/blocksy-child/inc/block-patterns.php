<?php
/**
 * Gutenberg Block Patterns reutilizables para INSAT
 */

add_action('init', function() {
    register_block_pattern_category('insat', ['label' => 'INSAT Patterns']);

    // ========== 1. HERO FULLSCREEN ==========
    register_block_pattern(
        'insat/hero-main',
        [
            'title' => 'Hero Fullscreen',
            'description' => 'Hero section fullscreen con overlay, H1 grande y CTAs',
            'content' => '<!-- wp:group {"layout":{"type":"constrained"},"className":"hero"} -->
<div class="wp-block-group hero">
  <!-- wp:heading {"level":1,"align":"center","className":"wp-block-heading"} -->
  <h1 class="wp-block-heading has-text-align-center">Internet Satelital Sin Límites</h1>
  <!-- /wp:heading -->

  <!-- wp:paragraph {"align":"center"} -->
  <p class="has-text-align-center">Velocidad, cobertura y conexión estable en todo el país. Ahora con TV satelital incluida.</p>
  <!-- /wp:paragraph -->

  <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
  <div class="wp-block-buttons">
    <!-- wp:button -->
    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Verificá Cobertura</a></div>
    <!-- /wp:button -->

    <!-- wp:button {"className":"btn-outline"} -->
    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button btn-outline">Planes y Precios</a></div>
    <!-- /wp:button -->
  </div>
  <!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['hero', 'fullscreen', 'intro'],
        ]
    );

    // ========== 2. CARDS PLANES ==========
    register_block_pattern(
        'insat/cards-plans',
        [
            'title' => 'Cards Planes (3 columnas)',
            'description' => '3 tarjetas de planes con precio "desde"',
            'content' => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:heading {"textAlign":"center"} -->
  <h2 class="wp-block-heading has-text-align-center">Planes y Precios</h2>
  <!-- /wp:heading -->

  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"card"} -->
      <div class="wp-block-group card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">Internet 50 Mbps</h3>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Desde <strong>$7.990/mes</strong></p>
        <!-- /wp:paragraph -->

        <!-- wp:list -->
        <ul>
          <li>50 Mbps de velocidad</li>
          <li>Descarga ilimitada</li>
          <li>Soporte 24/7</li>
        </ul>
        <!-- /wp:list -->

        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Contratar</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"card"} -->
      <div class="wp-block-group card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">Internet 100 Mbps + TV</h3>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Desde <strong>$12.990/mes</strong></p>
        <!-- /wp:paragraph -->

        <!-- wp:list -->
        <ul>
          <li>100 Mbps de velocidad</li>
          <li>+50 canales TV</li>
          <li>WiFi Plus incluído</li>
        </ul>
        <!-- /wp:list -->

        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Contratar</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"card"} -->
      <div class="wp-block-group card">
        <!-- wp:heading {"level":3} -->
        <h3 class="wp-block-heading">Internet 150 Mbps + TV</h3>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Desde <strong>$16.990/mes</strong></p>
        <!-- /wp:paragraph -->

        <!-- wp:list -->
        <ul>
          <li>150 Mbps de velocidad</li>
          <li>+100 canales TV</li>
          <li>Mesh WiFi incluído</li>
        </ul>
        <!-- /wp:list -->

        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Contratar</a></div>
        <!-- /wp:button -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['planes', 'precios', 'cards'],
        ]
    );

    // ========== 3. VERIFICÁ COBERTURA ==========
    register_block_pattern(
        'insat/coverage-check',
        [
            'title' => 'Verificá Cobertura (Formulario)',
            'description' => 'Formulario de verificación por dirección/CP - MVP lead capture',
            'content' => '<!-- wp:group {"backgroundColor":"rgba(255, 255, 255, 0.04)","className":"section-alt"} -->
<div class="wp-block-group section-alt" style="background-color:rgba(255, 255, 255, 0.04)">
  <!-- wp:heading {"textAlign":"center"} -->
  <h2 class="wp-block-heading has-text-align-center">¿Tenemos cobertura en tu zona?</h2>
  <!-- /wp:heading -->

  <!-- wp:paragraph {"align":"center"} -->
  <p class="has-text-align-center">Ingresá tu dirección o código postal para verificar disponibilidad</p>
  <!-- /wp:paragraph -->

  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <input type="text" placeholder="Dirección, calle y número" />
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <input type="text" placeholder="Código Postal" />
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:button -->
      <div class="wp-block-button wp-block-button-full"><a class="wp-block-button__link wp-element-button">Verificar</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['cobertura', 'verificación', 'formulario'],
        ]
    );

    // ========== 4. INSTALACIÓN EN 3 PASOS ==========
    register_block_pattern(
        'insat/installation-steps',
        [
            'title' => 'Instalación en 3 Pasos',
            'description' => 'Bloque de pasos numerados para proceso de instalación',
            'content' => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:heading {"textAlign":"center"} -->
  <h2 class="wp-block-heading has-text-align-center">Instalación Fácil y Rápida</h2>
  <!-- /wp:heading -->

  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"step-card"} -->
      <div class="wp-block-group step-card">
        <div class="step-number">1</div>
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Confirmamos tu zona</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Verificamos cobertura y confirmamos disponibilidad en 24 horas</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"step-card"} -->
      <div class="wp-block-group step-card">
        <div class="step-number">2</div>
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Instalamos el kit</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Nuestro técnico lleva e instala antena + router en tu hogar</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"step-card"} -->
      <div class="wp-block-group step-card">
        <div class="step-number">3</div>
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">¡Conectado!</h4>
        <!-- /wp:heading -->

        <!-- wp:paragraph -->
        <p>Disfruta de internet ilimitado de alta velocidad</p>
        <!-- /wp:paragraph -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['pasos', 'instalación', 'proceso'],
        ]
    );

    // ========== 5. QUÉ INCLUYE EL KIT ==========
    register_block_pattern(
        'insat/kit-includes',
        [
            'title' => 'Qué Incluye el Kit',
            'description' => 'Grid de 4 items mostrando componentes del kit',
            'content' => '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:heading {"textAlign":"center"} -->
  <h2 class="wp-block-heading has-text-align-center">Kit Completo Incluido</h2>
  <!-- /wp:heading -->

  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"kit-item"} -->
      <div class="wp-block-group kit-item">
        <img src="https://insat.com.ar/wp-content/uploads/2021/02/cropped-insat-blanco-transp-editable-1.png" alt="Antena Satelital" loading="lazy" width="80" height="80" />
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Antena Satelital</h4>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"kit-item"} -->
      <div class="wp-block-group kit-item">
        <img src="https://insat.com.ar/wp-content/uploads/2021/02/cropped-insat-blanco-transp-editable-1.png" alt="Router WiFi 6" loading="lazy" width="80" height="80" />
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Router WiFi 6</h4>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"kit-item"} -->
      <div class="wp-block-group kit-item">
        <img src="https://insat.com.ar/wp-content/uploads/2021/02/cropped-insat-blanco-transp-editable-1.png" alt="Cableado e Instalación" loading="lazy" width="80" height="80" />
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Cableado + Instalación</h4>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:group {"className":"kit-item"} -->
      <div class="wp-block-group kit-item">
        <img src="https://insat.com.ar/wp-content/uploads/2021/02/cropped-insat-blanco-transp-editable-1.png" alt="Soporte Técnico 24/7" loading="lazy" width="80" height="80" />
        <!-- wp:heading {"level":4} -->
        <h4 class="wp-block-heading">Soporte Técnico 24/7</h4>
        <!-- /wp:heading -->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['kit', 'incluye', 'componentes'],
        ]
    );

    // ========== 6. EDITORIAL (Últimas 3) ==========
    register_block_pattern(
        'insat/editorial-latest',
        [
            'title' => 'Editorial - Últimas Publicaciones',
            'description' => 'Muestra últimas 3 novedades + tech + historias',
            'content' => '<!-- wp:group {"layout":{"type":"constrained"},"className":"section"} -->
<div class="wp-block-group section">
  <!-- wp:heading {"textAlign":"center"} -->
  <h2 class="wp-block-heading has-text-align-center">Últimas Noticias y Artículos</h2>
  <!-- /wp:heading -->

  <!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","orderBy":"date","order":"desc"},"displayLayout":{"type":"list"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template -->
    <div class="wp-block-post-template">
      <!-- wp:group {"className":"card","layout":{"type":"constrained"}} -->
      <div class="wp-block-group card">
        <!-- wp:post-title {"level":3,"isLink":true} /-->
        <!-- wp:post-excerpt /-->
        <!-- wp:group {"layout":{"type":"flex"}} -->
        <div class="wp-block-group">
          <!-- wp:post-author /-->
          <!-- wp:post-date /-->
        </div>
        <!-- /wp:group -->
        <!-- wp:read-more /-->
      </div>
      <!-- /wp:group -->
    </div>
    <!-- /wp:post-template -->
  </div>
  <!-- /wp:query -->

  <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
  <div class="wp-block-buttons">
    <!-- wp:button -->
    <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/novedades/">Ver todas las noticias</a></div>
    <!-- /wp:button -->
  </div>
  <!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['editorial', 'noticias', 'blog'],
        ]
    );

    // ========== 7. FOOTER + NEWSLETTER ==========
    register_block_pattern(
        'insat/footer-newsletter',
        [
            'title' => 'Footer con Newsletter',
            'description' => 'Footer minimal con formulario de newsletter',
            'content' => '<!-- wp:group {"className":"section section-alt","layout":{"type":"constrained"}} -->
<div class="wp-block-group section section-alt">
  <!-- wp:columns -->
  <div class="wp-block-columns">
    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:heading {"level":4} -->
      <h4 class="wp-block-heading">Mantente Actualizado</h4>
      <!-- /wp:heading -->

      <!-- wp:paragraph -->
      <p>Suscribite a nuestro newsletter</p>
      <!-- /wp:paragraph -->

      <!-- wp:form-submission-form -->
      <form method="post">
        <!-- wp:form-submission-form-input {"inputType":"email","label":"Email"} -->
        <input type="email" name="email" placeholder="tu@email.com" required />
        <!-- /wp:form-submission-form-input -->

        <!-- wp:button -->
        <div class="wp-block-button"><button type="submit" class="wp-block-button__link wp-element-button">Suscribirse</button></div>
        <!-- /wp:button -->
      </form>
      <!-- /wp:form-submission-form -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:heading {"level":4} -->
      <h4 class="wp-block-heading">Menú</h4>
      <!-- /wp:heading -->

      <!-- wp:list -->
      <ul>
        <li><a href="/hogares/">Hogares</a></li>
        <li><a href="/empresas/">Empresas</a></li>
        <li><a href="/cobertura/">Cobertura</a></li>
        <li><a href="/soporte/">Soporte</a></li>
      </ul>
      <!-- /wp:list -->
    </div>
    <!-- /wp:column -->

    <!-- wp:column -->
    <div class="wp-block-column">
      <!-- wp:heading {"level":4} -->
      <h4 class="wp-block-heading">Legal</h4>
      <!-- /wp:heading -->

      <!-- wp:list -->
      <ul>
        <li><a href="/legal/terminos/">Términos y Condiciones</a></li>
        <li><a href="/legal/privacidad/">Política de Privacidad</a></li>
        <li><a href="/legal/cookies/">Cookies</a></li>
      </ul>
      <!-- /wp:list -->
    </div>
    <!-- /wp:column -->
  </div>
  <!-- /wp:columns -->

  <!-- wp:paragraph {"align":"center","className":"text-tertiary","style":{"marginTop":"2rem"}} -->
  <p class="has-text-align-center text-tertiary" style="margin-top:2rem">© 2026 INSAT. Todos los derechos reservados.</p>
  <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
            'categories' => ['insat'],
            'keywords' => ['footer', 'newsletter', 'suscripción'],
        ]
    );
});
