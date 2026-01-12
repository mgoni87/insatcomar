<?php
/**
 * archive-insat-news.php
 * Plantilla para listado de Novedades (CPT: insat-news)
 */
get_header();
?>

<div class="section">
    <div class="container container-lg">
        <h1>Novedades</h1>
        <p class="text-secondary">Últimas actualizaciones sobre nuestros servicios e infraestructura</p>

        <?php
        if (have_posts()) {
            echo '<div class="grid grid-3" style="margin-top: 2rem;">';

            while (have_posts()) {
                the_post();
                ?>
                <article class="card">
                    <?php if (has_post_thumbnail()): ?>
                        <div style="margin-bottom: 1rem; border-radius: 0.5rem; overflow: hidden;">
                            <?php the_post_thumbnail('medium', ['loading' => 'lazy', 'style' => 'width: 100%; height: 200px; object-fit: cover;']); ?>
                        </div>
                    <?php endif; ?>

                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="text-secondary"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                    
                    <div class="text-tertiary" style="font-size: 0.875rem; margin-bottom: 1rem;">
                        Por <?php the_author(); ?> • <?php echo get_the_date('j \d\e F'); ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-small">Leer más →</a>
                </article>
                <?php
            }
            echo '</div>';

            // Pagination
            the_posts_pagination([
                'prev_text' => '← Anterior',
                'next_text' => 'Siguiente →',
                'type' => 'list',
            ]);
        } else {
            echo '<p>No hay novedades en este momento.</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
