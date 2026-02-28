<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<section class="page-header-bg">
    <div class="container">
        <?php
        $seo = ch_get_seo_data(get_the_ID());
        $default = ch_generate_seo_defaults(get_the_ID());
        ?>
        <?php ch_breadcrumbs(); ?>
        <h1 class="entry-title"><?php echo esc_html($seo['h1'] ?: get_the_title()); ?></h1>
        <p class="hero-desc"><?php echo esc_html($seo['desc'] ?: $default['desc']); ?></p>
    </div>
</section>

<main class="container content-sidebar-layout">
    <article class="calculator-content-wrapper">
        <div class="entry-content"><?php the_content(); ?></div>

        <section class="faq-box">
            <h2>FAQ</h2>
            <?php
            $faq_entities = ch_get_faq_entities($seo['faq'] ?: $default['faq']);
            if (!empty($faq_entities)) :
                foreach ($faq_entities as $faq) : ?>
                    <details>
                        <summary><?php echo esc_html($faq['name']); ?></summary>
                        <p><?php echo esc_html($faq['acceptedAnswer']['text']); ?></p>
                    </details>
                <?php endforeach;
            endif;
            ?>
        </section>

        <section>
            <h2>Рекомендуемые калькуляторы</h2>
            <?php echo do_shortcode('[calc_related count="6"]'); ?>
        </section>
    </article>

    <aside class="sidebar">
        <div class="widget related-widget">
            <h3>С этим также считают</h3>
            <ul class="widget-links">
                <?php foreach (ch_get_related_calculators(get_the_ID(), 8) as $item) : ?>
                    <li><a href="<?php echo esc_url(get_permalink($item->ID)); ?>"><?php echo esc_html(get_the_title($item->ID)); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="widget promo-widget">
            <h3>SEO-подсказка</h3>
            <p>Добавьте страницу в закладки и используйте XML-карту для ускоренной индексации.</p>
            <a href="<?php echo esc_url(home_url('/calculator-sitemap.xml')); ?>">Открыть карту сайта</a>
        </div>
    </aside>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>
