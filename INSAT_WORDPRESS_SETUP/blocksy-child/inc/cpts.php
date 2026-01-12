<?php
/**
 * Registrar Custom Post Types para INSAT
 * - News (Novedades)
 * - Tech (Tecnología)
 * - Stories (Historias)
 */

add_action('init', function() {
    // ========== CPT: NOVEDADES ==========
    register_post_type('insat-news', [
        'label' => 'Novedades',
        'singular_name' => 'Novedad',
        'public' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions'],
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'novedades'],
        'menu_icon' => 'dashicons-admin-post',
        'taxonomies' => ['category', 'post_tag'],
    ]);

    // ========== CPT: TECNOLOGÍA ==========
    register_post_type('insat-tech', [
        'label' => 'Tecnología',
        'singular_name' => 'Artículo Tech',
        'public' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions'],
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tecnologia'],
        'menu_icon' => 'dashicons-lightbulb',
        'taxonomies' => ['category', 'post_tag'],
    ]);

    // ========== CPT: HISTORIAS ==========
    register_post_type('insat-stories', [
        'label' => 'Historias',
        'singular_name' => 'Historia',
        'public' => true,
        'has_archive' => true,
        'hierarchical' => false,
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions'],
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'historias'],
        'menu_icon' => 'dashicons-format-quote',
        'taxonomies' => ['category', 'post_tag'],
    ]);

    // ========== TAXONOMÍAS ==========
    register_taxonomy('insat-news-cat', 'insat-news', [
        'label' => 'Categoría',
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'novedades-categoria'],
    ]);

    register_taxonomy('insat-tech-cat', 'insat-tech', [
        'label' => 'Categoría',
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tech-categoria'],
    ]);

    register_taxonomy('insat-stories-cat', 'insat-stories', [
        'label' => 'Categoría',
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'historias-categoria'],
    ]);
});

// ========== METABOXES (SEO BÁSICO) ==========
add_action('add_meta_boxes', function() {
    add_meta_box(
        'insat-seo',
        'SEO',
        function($post) {
            wp_nonce_field('insat-seo-nonce', 'insat_seo_nonce');
            $meta = get_post_meta($post->ID);
            ?>
            <div class="form-group">
                <label for="insat_meta_description"><strong>Meta Descripción (160 caracteres):</strong></label><br>
                <textarea id="insat_meta_description" name="insat_meta_description" 
                    style="width: 100%; height: 60px; margin-top: 5px; font-family: monospace;" 
                    maxlength="160" placeholder="Descripción que aparecerá en buscadores..."
                ><?php echo esc_attr($meta['insat_meta_description'][0] ?? ''); ?></textarea>
                <small style="display: block; color: #666; margin-top: 5px;">
                    <span id="char-count">0</span>/160 caracteres
                </small>
            </div>

            <div class="form-group">
                <label for="insat_custom_slug"><strong>Slug Personalizado:</strong></label><br>
                <input type="text" id="insat_custom_slug" name="insat_custom_slug" 
                    style="width: 100%; margin-top: 5px; padding: 5px;" 
                    value="<?php echo esc_attr($meta['insat_custom_slug'][0] ?? ''); ?>" 
                    placeholder="slug-personalizado">
                <small style="display: block; color: #666; margin-top: 5px;">
                    URL será: /<?php echo $post->post_type === 'insat-news' ? 'novedades' : ($post->post_type === 'insat-tech' ? 'tecnologia' : 'historias'); ?>/<strong id="slug-preview"><?php echo esc_attr($meta['insat_custom_slug'][0] ?? ''); ?></strong>/
                </small>
            </div>

            <script>
            document.getElementById('insat_meta_description').addEventListener('input', function() {
                document.getElementById('char-count').textContent = this.value.length;
            });
            document.getElementById('insat_custom_slug').addEventListener('input', function() {
                document.getElementById('slug-preview').textContent = this.value;
            });
            </script>
            <?php
        },
        ['insat-news', 'insat-tech', 'insat-stories']
    );
});

add_action('save_post', function($post_id) {
    if (!isset($_POST['insat_seo_nonce']) || !wp_verify_nonce($_POST['insat_seo_nonce'], 'insat-seo-nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['insat_meta_description'])) {
        $meta_desc = sanitize_text_field($_POST['insat_meta_description']);
        if (strlen($meta_desc) > 160) {
            $meta_desc = substr($meta_desc, 0, 160);
        }
        update_post_meta($post_id, 'insat_meta_description', $meta_desc);
    }

    if (isset($_POST['insat_custom_slug'])) {
        $slug = sanitize_title($_POST['insat_custom_slug']);
        update_post_meta($post_id, 'insat_custom_slug', $slug);
    }
});

// ========== MOSTRAR META DESCRIPTION EN FRONTED ==========
add_action('wp_head', function() {
    if (is_singular(['insat-news', 'insat-tech', 'insat-stories'])) {
        $meta_desc = get_post_meta(get_the_ID(), 'insat_meta_description', true);
        if ($meta_desc) {
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
        }
    }
});
