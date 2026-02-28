<?php
/**
 * CalcHub Ultimate SEO Theme
 * Версия: 5.0 (Silo, Auto-TOC, JSON-LD Schema, Related Interlinking)
 */

// 1. БАЗОВЫЕ НАСТРОЙКИ ТЕМЫ
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array('primary' => 'Главное меню', 'footer' => 'Подвал'));
});

// 2. РЕГИСТРАЦИЯ ТИПА ЗАПИСИ (КАЛЬКУЛЯТОРЫ) И РУБРИК (ТАКСОНОМИЯ)
add_action('init', function() {
    // Регистрируем Рубрики для калькуляторов (Silo-структура)
    register_taxonomy('calc_category', 'calculator', array(
        'labels' => array('name' => 'Рубрики калькуляторов', 'singular_name' => 'Рубрика'),
        'hierarchical' => true,
        'public' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'category'),
    ));

    // Регистрируем сами калькуляторы
    register_post_type('calculator', array(
        'labels'      => array('name' => 'Калькуляторы', 'singular_name' => 'Калькулятор'),
        'public'      => true,
        'has_archive' => true,
        'menu_icon'   => 'dashicons-calculator',
        'supports'    => array('title', 'editor', 'thumbnail'),
        'taxonomies'  => array('calc_category'),
        'rewrite'     => array('slug' => '/', 'with_front' => false), // Убираем префикс для SEO
    ));
});

// 3. META BOXES (СЕО И КОД)
add_action('add_meta_boxes', function() {
    add_meta_box('ch_calc_code_box', '🚀 JS/HTML КОД КАЛЬКУЛЯТОРА', 'ch_render_code_box', 'calculator', 'normal', 'high');
    add_meta_box('ch_calc_seo_box', '🔥 SEO НАСТРОЙКИ', 'ch_render_seo_box', 'calculator', 'normal', 'high');
});

function ch_render_code_box($post) {
    $code = get_post_meta($post->ID, '_calc_code_data', true);
    wp_nonce_field('save_calc_data', 'calc_nonce');
    echo '<textarea id="calc_code_editor" name="calc_code_contents" style="width:100%; height:300px; font-family:monospace; background:#1e1e1e; color:#d4d4d4; padding:15px;">' . esc_textarea($code) . '</textarea>';
}

function ch_render_seo_box($post) {
    $seo = get_post_meta($post->ID, '_calc_seo_data', true) ?: array('h1'=>'', 'title'=>'', 'desc'=>'');
    echo '<p><label><strong>Кастомный H1:</strong></label><input type="text" name="calc_seo[h1]" value="'.esc_attr($seo['h1']).'" style="width:100%; padding:8px;"></p>';
    echo '<p><label><strong>SEO Title:</strong></label><input type="text" name="calc_seo[title]" value="'.esc_attr($seo['title']).'" style="width:100%; padding:8px;"></p>';
    echo '<p><label><strong>Meta Description:</strong></label><textarea name="calc_seo[desc]" style="width:100%; height:70px; padding:8px;">'.esc_textarea($seo['desc']).'</textarea></p>';
}

add_action('save_post', function($post_id) {
    if (!isset($_POST['calc_nonce']) || !wp_verify_nonce($_POST['calc_nonce'], 'save_calc_data')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['calc_code_contents'])) update_post_meta($post_id, '_calc_code_data', $_POST['calc_code_contents']);
    if (isset($_POST['calc_seo'])) update_post_meta($post_id, '_calc_seo_data', $_POST['calc_seo']);
});

// 4. SEO ФИЛЬТРЫ (Подмена Title, H1, Meta Desc)
add_filter('document_title_parts', function($title) {
    if (is_singular('calculator')) {
        $seo = get_post_meta(get_the_ID(), '_calc_seo_data', true);
        if (!empty($seo['title'])) { $title['title'] = $seo['title']; unset($title['site'], $title['tagline']); }
    }
    return $title;
}, 999);
add_filter('document_title_separator', function($sep) { return is_singular('calculator') && !empty(get_post_meta(get_the_ID(), '_calc_seo_data', true)['title']) ? '' : $sep; });
add_filter('the_title', function($title, $id = null) {
    if (is_singular('calculator') && in_the_loop() && is_main_query() && $id === get_the_ID()) {
        $seo = get_post_meta($id, '_calc_seo_data', true);
        if (!empty($seo['h1'])) return $seo['h1'];
    }
    return $title;
}, 10, 2);

add_action('wp_head', function() {
    if (is_singular('calculator')) {
        $seo = get_post_meta(get_the_ID(), '_calc_seo_data', true);
        if (!empty($seo['desc'])) echo '<meta name="description" content="'.esc_attr($seo['desc']).'">' . "\n";
    }
}, 1);

// 5. АВТО-ОГЛАВЛЕНИЕ (TOC) И ЯКОРЯ ДЛЯ SEO
add_filter('the_content', function($content) {
    if (!is_singular('calculator') || !in_the_loop() || !is_main_query()) return $content;

    // Регулярка ищет все H2 и H3
    preg_match_all('/<h([2-3])>(.*?)<\/h[2-3]>/i', $content, $matches, PREG_SET_ORDER);
    if (count($matches) < 2) return $content; // Не выводим, если заголовков мало

    $toc = '<div class="auto-toc"><div class="toc-title">Содержание статьи</div><ul class="toc-list">';
    $counter = 1;
    
    foreach ($matches as $val) {
        $level = $val[1]; // 2 или 3
        $title = strip_tags($val[2]);
        $slug = 'section-' . $counter; // Создаем ID
        
        // Добавляем ID в контент
        $content = str_replace($val[0], "<h{$level} id=\"{$slug}\">{$title}</h{$level}>", $content);
        
        // Добавляем ссылку в оглавление
        $class = ($level == 3) ? 'toc-sub' : 'toc-main';
        $toc .= "<li class=\"{$class}\"><a href=\"#{$slug}\">{$title}</a></li>";
        $counter++;
    }
    $toc .= '</ul></div>';

    // Вставляем TOC сразу после шорткода калькулятора (после расчета)
    return preg_replace('/(\[insert_calc\])/i', '$1' . $toc, $content, 1);
}, 20);

// 6. MICRODATA (JSON-LD SCHEMA)
add_action('wp_footer', function() {
    if (!is_singular('calculator')) return;
    $seo = get_post_meta(get_the_ID(), '_calc_seo_data', true);
    
    $schema = array(
        "@context" => "https://schema.org",
        "@type" => "SoftwareApplication",
        "name" => !empty($seo['h1']) ? $seo['h1'] : get_the_title(),
        "operatingSystem" => "Any",
        "applicationCategory" => "CalculatorApplication",
        "offers" => array("@type" => "Offer", "price" => "0", "priceCurrency" => "USD"),
        "description" => !empty($seo['desc']) ? $seo['desc'] : get_the_excerpt()
    );
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE) . '</script>';
});

// 7. ВЫВОД ШОРТКОДА И СКРИПТОВ
add_shortcode('insert_calc', function() {
    return '<div class="custom-calculator-holder">' . get_post_meta(get_the_ID(), '_calc_code_data', true) . '</div>';
});
add_action('wp_enqueue_scripts', function() { wp_enqueue_style('main-style', get_stylesheet_uri(), array(), '3.0'); });
add_filter('the_content', 'do_shortcode', 10);

// Ядро JS
add_action('wp_footer', function() {
    ?>
    <script>
    function chFormatNum(num, precision = 2) {
        let rounded = Number(num).toFixed(precision);
        let parts = rounded.toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        return parts.join('.');
    }
    function chCopyText(textToCopy) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            let btn = document.getElementById('calc-copy-btn');
            if(btn) {
                let originalText = btn.innerHTML;
                btn.innerHTML = "✓ Скопировано!";
                btn.classList.add('btn-success');
                setTimeout(() => { btn.innerHTML = originalText; btn.classList.remove('btn-success'); }, 2000);
            }
        });
    }
    // Плавный скролл для TOC
    document.querySelectorAll('.auto-toc a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });
    </script>
    <?php
});

// 8. ХЛЕБНЫЕ КРОШКИ
function ch_breadcrumbs() {
    if (is_front_page() || is_home()) return;
    echo '<nav class="ch-breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">';
    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.home_url().'"><span itemprop="name">Главная</span></a><meta itemprop="position" content="1" /></span>';
    
    if (is_singular('calculator')) {
        echo '<span class="sep">/</span> ';
        $terms = get_the_terms(get_the_ID(), 'calc_category');
        if ($terms && !is_wp_error($terms)) {
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="'.get_term_link($terms[0]).'"><span itemprop="name">'.$terms[0]->name.'</span></a><meta itemprop="position" content="2" /></span><span class="sep">/</span> ';
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">'.get_the_title().'</span><meta itemprop="position" content="3" /></span>';
        } else {
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span itemprop="name">'.get_the_title().'</span><meta itemprop="position" content="2" /></span>';
        }
    }
    echo '</nav>';
}