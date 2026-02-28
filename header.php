<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link">CalcOnlineHub</a>
        <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Открыть меню"><span></span><span></span><span></span></button>
        <nav class="main-navigation" id="mainNav">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(['theme_location' => 'primary', 'container' => false]);
            } else {
                echo '<ul><li><a href="' . esc_url(get_post_type_archive_link('calculator')) . '">Каталог калькуляторов</a></li></ul>';
            }
            ?>
        </nav>
    </div>
</header>
