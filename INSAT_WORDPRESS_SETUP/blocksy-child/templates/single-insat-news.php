<?php
/**
 * single-insat-news.php
 * Plantilla para vista individual de Novedad (CPT: insat-news)
 */
get_header();
?>

<article class="section">
    <div class="container container-sm">
        <!-- BREADCRUMB -->
        <nav class="breadcrumb" style="margin-bottom: 2rem;">
            <a href="/">Inicio</a>
            <span class="separator">/</span>
            <a href="/novedades/">Novedades</a>
            <span class="separator">/</span>
            <span><?php the_title(); ?></span>
        </nav>

        <!-- META INFO -->
        <div class="text-tertiary" style="font-size: 0.875rem; margin-bottom: 2rem;">
            Por <strong><?php the_author(); ?></strong> • <?php echo get_the_date('j \d\e F \d\e Y'); ?>
            <?php 
            $categories = get_the_category();
            if ($categories) {
                echo ' • <strong>Categoría:</strong> ' . implode(', ', wp_list_pluck($categories, 'name'));
            }
            ?>
        </div>

        <!-- FEATURED IMAGE -->
        <?php if (has_post_thumbnail()): ?>
            <figure style="margin: 2rem 0; border-radius: 1rem; overflow: hidden;">
                <?php the_post_thumbnail('large', ['loading' => 'lazy', 'style' => 'width: 100%; height: auto;']); ?>
                <?php 
                $caption = wp_get_attachment_caption(get_post_thumbnail_id());
                if ($caption) {
                    echo '<figcaption style="padding: 1rem; background-color: rgba(255,255,255,0.04); color: var(--color-text-secondary); font-size: 0.875rem; text-align: center;">' . esc_html($caption) . '</figcaption>';
                }
                ?>
            </figure>
        <?php endif; ?>

        <!-- TITLE -->
        <h1><?php the_title(); ?></h1>

        <!-- CONTENT -->
        <div class="post-content" style="margin: 2rem 0; line-height: 1.8; font-size: 1.1rem;">
            <?php the_content(); ?>
        </div>

        <!-- TAGS -->
        <?php 
        $tags = get_the_tags();
        if ($tags): 
        ?>
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border);">
                <strong>Tags:</strong>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php foreach ($tags as $tag): ?>
                        <span class="badge"><a href="<?php echo get_tag_link($tag); ?>"><?php echo $tag->name; ?></a></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- NAVIGATION -->
        <nav style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--color-border); display: flex; justify-content: space-between; gap: 1rem;">
            <div>
                <?php
                $prev = get_previous_post(true, '', 'insat-news-cat');
                if ($prev) {
                    echo '<a href="' . get_permalink($prev->ID) . '" class="btn btn-outline">← ' . esc_html($prev->post_title) . '</a>';
                }
                ?>
            </div>
            <div>
                <?php
                $next = get_next_post(true, '', 'insat-news-cat');
                if ($next) {
                    echo '<a href="' . get_permalink($next->ID) . '" class="btn btn-outline">' . esc_html($next->post_title) . ' →</a>';
                }
                ?>
            </div>
        </nav>

        <!-- RELATED POSTS -->
        <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--color-border);">
            <h3>Artículos Relacionados</h3>
            <?php
            $categories = wp_get_post_terms(get_the_ID(), 'insat-news-cat', ['fields' => 'ids']);
            $related = new WP_Query([
                'post_type' => 'insat-news',
                'posts_per_page' => 3,
                'orderby' => 'rand',
                'post__not_in' => [get_the_ID()],
                'tax_query' => $categories ? [[
                    'taxonomy' => 'insat-news-cat',
                    'terms' => $categories,
                ]] : [],
            ]);

            if ($related->have_posts()) {
                echo '<div class="grid grid-3" style="margin-top: 1.5rem;">';
                while ($related->have_posts()) {
                    $related->the_post();
                    ?>
                    <article class="card">
                        <?php if (has_post_thumbnail()): ?>
                            <div style="margin-bottom: 1rem; border-radius: 0.5rem; overflow: hidden;">
                                <?php the_post_thumbnail('medium', ['loading' => 'lazy', 'style' => 'width: 100%; height: 150px; object-fit: cover;']); ?>
                            </div>
                        <?php endif; ?>
                        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-small">Leer más</a>
                    </article>
                    <?php
                }
                echo '</div>';
            }
            wp_reset_postdata();
            ?>
        </div>

        <!-- COMMENTS -->
        <?php 
        if (comments_open() || get_comments_number()) {
            echo '<div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--color-border);">';
            comments_template();
            echo '</div>';
        }
        ?>
    </div>
</article>

<?php get_footer(); ?>
