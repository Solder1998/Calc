<?php get_header(); ?>
<section class="page-header-bg">
    <div class="container">
        <h1>Каталог онлайн-калькуляторов</h1>
        <p>Умные фильтры, автоматическая SEO-структура, быстрая навигация по разделам.</p>
        <form class="catalog-search" method="get" action="<?php echo esc_url(get_post_type_archive_link('calculator')); ?>">
            <input type="text" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Найти калькулятор по названию или задаче">
            <button type="submit">Найти</button>
        </form>
    </div>
</section>

<main class="container main-area">
    <?php
    $categories = get_terms([
        'taxonomy' => 'calc_category',
        'hide_empty' => false,
    ]);

    if (!empty($categories) && !is_wp_error($categories)) :
        foreach ($categories as $category) :
            $query = new WP_Query([
                'post_type' => 'calculator',
                'posts_per_page' => 8,
                'tax_query' => [[
                    'taxonomy' => 'calc_category',
                    'field' => 'term_id',
                    'terms' => $category->term_id,
                ]],
                's' => get_search_query(),
            ]);

            if (!$query->have_posts()) {
                continue;
            }
            ?>
            <section class="silo-section">
                <h2 class="silo-title"><?php echo esc_html($category->name); ?></h2>
                <div class="calc-cards-grid">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <a class="calc-card" href="<?php the_permalink(); ?>">
                            <span class="calc-card-icon">🧮</span>
                            <span class="calc-card-content">
                                <strong><?php the_title(); ?></strong>
                                <small>Перейти к расчету →</small>
                            </span>
                        </a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endforeach;
    endif;
    ?>
</main>

<?php get_footer(); ?>
