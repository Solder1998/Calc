<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/favicon.svg">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-link">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px;"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><line x1="8" y1="9" x2="16" y2="9"></line><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="10" y2="17"></line><line x1="14" y1="17" x2="16" y2="17"></line></svg>
            CalcOnlineHub
        </a>
        <button class="mobile-menu-toggle" id="mobileMenuBtn"><span></span><span></span><span></span></button>
        <nav class="main-navigation" id="mainNav">
            <?php 
            if (has_nav_menu('primary')) wp_nav_menu(array('theme_location' => 'primary', 'container' => false));
            else echo '<ul><li><a href="/">Каталог калькуляторов</a></li></ul>';
            ?>
        </nav>
    </div>
</header>