<?php get_header(); ?>

<div class="page-header-bg">
    <div class="container">
        <?php ch_breadcrumbs(); ?>
        <?php while ( have_posts() ) : the_post(); 
            $seo = get_post_meta(get_the_ID(), '_calc_seo_data', true);
            $display_h1 = (!empty($seo['h1'])) ? $seo['h1'] : get_the_title();
        ?>
        <h1 class="entry-title"><?php echo esc_html($display_h1); ?></h1>
        <?php endwhile; ?>
    </div>
</div>

<main class="container">
    <div class="content-sidebar-layout">
        <article class="calculator-content-wrapper">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            <?php endwhile; ?>
        </article>

        <aside class="sidebar">
            <div class="widget related-widget">
                <h3 class="widget-title">С этим также считают</h3>
                <ul class="widget-links">
                    <?php
                    $terms = get_the_terms(get_the_ID(), 'calc_category');
                    $term_ids = ($terms && !is_wp_error($terms)) ? wp_list_pluck($terms, 'term_id') : array();
                    
                    $related_args = array(
                        'post_type' => 'calculator',
                        'posts_per_page' => 5,
                        'post__not_in' => array(get_the_ID()),
                    );
                    if (!empty($term_ids)) {
                        $related_args['tax_query'] = array(array('taxonomy' => 'calc_category', 'field' => 'term_id', 'terms' => $term_ids));
                    }
                    $related_query = new WP_Query($related_args);

                    if ($related_query->have_posts()) {
                        while ($related_query->have_posts()) : $related_query->the_post();
                            echo '<li><a href="'.get_the_permalink().'">'.get_the_title().'</a></li>';
                        endwhile;
                        wp_reset_postdata();
                    } else {
                        echo '<li>Пока нет похожих калькуляторов</li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="widget promo-widget">
                <p>Сохраните страницу в закладки (Ctrl+D), чтобы не потерять инструмент! ⭐</p>
            </div>
        </aside>
    </div>
</main>

<?php get_footer(); ?>