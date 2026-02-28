<?php get_header(); ?>
<main class="container main-area">
    <?php ch_breadcrumbs(); ?>

    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article class="post-card">
                <h1><?php the_title(); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <article class="post-card">
            <h1>Калькуляторы не найдены</h1>
            <p>Добавьте первый калькулятор в админ-панели WordPress.</p>
        </article>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
