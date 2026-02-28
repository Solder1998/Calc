<?php
/**
 * Theme Name: CalcOnlineHub
 * Description: Многофункциональная SEO-тема для калькуляторов с автоматизацией контента, перелинковки и микроразметки.
 * Version: 8.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const CH_SEO_META_KEY = '_calc_seo_data';
const CH_CODE_META_KEY = '_calc_code_data';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus([
        'primary' => 'Главное меню',
        'footer' => 'Меню в подвале',
    ]);
});

add_action('init', function () {
    register_taxonomy('calc_category', 'calculator', [
        'labels' => [
            'name' => 'Категории калькуляторов',
            'singular_name' => 'Категория калькулятора',
        ],
        'hierarchical' => true,
        'public' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'calculators'],
        'show_in_rest' => true,
    ]);

    register_post_type('calculator', [
        'labels' => [
            'name' => 'Калькуляторы',
            'singular_name' => 'Калькулятор',
            'add_new_item' => 'Добавить калькулятор',
            'edit_item' => 'Редактировать калькулятор',
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-calculator',
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite' => ['slug' => 'calculator', 'with_front' => false],
        'taxonomies' => ['calc_category'],
    ]);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('calc-hub-style', get_stylesheet_uri(), [], '8.0.0');
});

add_action('add_meta_boxes', function () {
    add_meta_box('ch_calc_code_box', 'Код калькулятора (HTML/JS)', 'ch_render_code_box', 'calculator', 'normal', 'high');
    add_meta_box('ch_calc_seo_box', 'SEO-центр', 'ch_render_seo_box', 'calculator', 'normal', 'high');
});

function ch_render_code_box(WP_Post $post): void
{
    wp_nonce_field('ch_save_calc', 'ch_calc_nonce');
    $code = (string) get_post_meta($post->ID, CH_CODE_META_KEY, true);
    echo '<p>Вставьте виджет/код калькулятора:</p>';
    echo '<textarea name="calc_code_contents" style="width:100%; min-height:260px; font-family:monospace;">' . esc_textarea($code) . '</textarea>';
}

function ch_render_seo_box(WP_Post $post): void
{
    $seo = ch_get_seo_data($post->ID);
    echo '<p><label><strong>H1</strong><br><input type="text" name="calc_seo[h1]" value="' . esc_attr($seo['h1']) . '" style="width:100%"></label></p>';
    echo '<p><label><strong>SEO Title</strong><br><input type="text" name="calc_seo[title]" value="' . esc_attr($seo['title']) . '" style="width:100%"></label></p>';
    echo '<p><label><strong>Meta Description</strong><br><textarea name="calc_seo[desc]" style="width:100%; min-height:80px;">' . esc_textarea($seo['desc']) . '</textarea></label></p>';
    echo '<p><label><strong>Ключевые фразы (через запятую)</strong><br><input type="text" name="calc_seo[keywords]" value="' . esc_attr($seo['keywords']) . '" style="width:100%"></label></p>';
    echo '<p><label><strong>FAQ в формате Вопрос::Ответ (один на строку)</strong><br><textarea name="calc_seo[faq]" style="width:100%; min-height:120px;">' . esc_textarea($seo['faq']) . '</textarea></label></p>';

    $score = ch_calculate_seo_score($post->ID, $seo);
    echo '<p><strong>SEO Score:</strong> ' . esc_html((string) $score) . '/100</p>';
}

function ch_get_seo_data(int $post_id): array
{
    $stored = get_post_meta($post_id, CH_SEO_META_KEY, true);
    $stored = is_array($stored) ? $stored : [];

    return wp_parse_args($stored, [
        'h1' => '',
        'title' => '',
        'desc' => '',
        'keywords' => '',
        'faq' => '',
    ]);
}

add_action('save_post_calculator', function (int $post_id): void {
    if (!isset($_POST['ch_calc_nonce']) || !wp_verify_nonce(sanitize_text_field((string) $_POST['ch_calc_nonce']), 'ch_save_calc')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['calc_code_contents'])) {
        update_post_meta($post_id, CH_CODE_META_KEY, wp_kses_post((string) $_POST['calc_code_contents']));
    }

    $raw_seo = isset($_POST['calc_seo']) && is_array($_POST['calc_seo']) ? $_POST['calc_seo'] : [];
    $seo = [
        'h1' => sanitize_text_field($raw_seo['h1'] ?? ''),
        'title' => sanitize_text_field($raw_seo['title'] ?? ''),
        'desc' => sanitize_textarea_field($raw_seo['desc'] ?? ''),
        'keywords' => sanitize_text_field($raw_seo['keywords'] ?? ''),
        'faq' => sanitize_textarea_field($raw_seo['faq'] ?? ''),
    ];

    update_post_meta($post_id, CH_SEO_META_KEY, $seo);
}, 20);

function ch_generate_seo_defaults(int $post_id): array
{
    $title = get_the_title($post_id);
    $categories = wp_get_post_terms($post_id, 'calc_category', ['fields' => 'names']);
    $primary_category = !empty($categories) ? $categories[0] : 'онлайн';

    return [
        'h1' => $title,
        'title' => sprintf('%s — точный онлайн расчёт | CalcOnlineHub', $title),
        'desc' => sprintf('%s: быстрый и точный расчет онлайн. Формулы, примеры и рекомендации по использованию.', $title),
        'keywords' => implode(', ', array_filter([$title, $primary_category . ' калькулятор', 'онлайн расчет'])),
        'faq' => "Как пользоваться калькулятором {$title}?::Введите исходные данные и нажмите кнопку расчёта.\nНасколько точны расчеты?::Точность зависит от корректности введенных исходных данных.",
    ];
}

add_filter('document_title_parts', function (array $parts): array {
    if (!is_singular('calculator')) {
        return $parts;
    }

    $post_id = get_queried_object_id();
    $seo = ch_get_seo_data($post_id);
    $defaults = ch_generate_seo_defaults($post_id);

    $parts['title'] = $seo['title'] ?: $defaults['title'];
    return $parts;
}, 20);

add_action('wp_head', function (): void {
    if (!is_singular('calculator')) {
        return;
    }

    $post_id = get_queried_object_id();
    $seo = ch_get_seo_data($post_id);
    $defaults = ch_generate_seo_defaults($post_id);

    $description = $seo['desc'] ?: $defaults['desc'];
    $keywords = $seo['keywords'] ?: $defaults['keywords'];
    $canonical = get_permalink($post_id);

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($seo['title'] ?: $defaults['title']) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
}, 5);

function ch_get_faq_entities(string $faq_text): array
{
    $faq_entities = [];
    $lines = preg_split('/\r\n|\r|\n/', $faq_text);

    foreach ($lines as $line) {
        if (strpos($line, '::') === false) {
            continue;
        }

        [$question, $answer] = array_map('trim', explode('::', $line, 2));
        if ($question === '' || $answer === '') {
            continue;
        }

        $faq_entities[] = [
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $answer,
            ],
        ];
    }

    return $faq_entities;
}

add_action('wp_footer', function (): void {
    if (!is_singular('calculator')) {
        return;
    }

    $post_id = get_queried_object_id();
    $seo = ch_get_seo_data($post_id);
    $defaults = ch_generate_seo_defaults($post_id);
    $faq_entities = ch_get_faq_entities($seo['faq'] ?: $defaults['faq']);

    $graph = [];
    $graph[] = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => $seo['h1'] ?: get_the_title($post_id),
        'applicationCategory' => 'CalculatorApplication',
        'operatingSystem' => 'Any',
        'description' => $seo['desc'] ?: $defaults['desc'],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'RUB',
        ],
        'url' => get_permalink($post_id),
    ];

    if (!empty($faq_entities)) {
        $graph[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faq_entities,
        ];
    }

    $crumbs = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Главная',
            'item' => home_url('/'),
        ],
    ];

    $terms = get_the_terms($post_id, 'calc_category');
    if ($terms && !is_wp_error($terms)) {
        $crumbs[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $terms[0]->name,
            'item' => get_term_link($terms[0]),
        ];
    }

    $crumbs[] = [
        '@type' => 'ListItem',
        'position' => count($crumbs) + 1,
        'name' => get_the_title($post_id),
        'item' => get_permalink($post_id),
    ];

    $graph[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $crumbs,
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}, 30);

function ch_inject_toc(string $content): string
{
    if (!is_singular('calculator') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    preg_match_all('/<h([2-3])([^>]*)>(.*?)<\/h[2-3]>/iu', $content, $matches, PREG_SET_ORDER);
    if (count($matches) < 2) {
        return $content;
    }

    $toc = '<aside class="auto-toc"><p class="toc-title">Содержание</p><ul class="toc-list">';
    $count = 1;

    foreach ($matches as $m) {
        $level = (int) $m[1];
        $attrs = $m[2];
        $raw_text = wp_strip_all_tags($m[3]);
        $slug = 'sec-' . $count . '-' . sanitize_title($raw_text);

        if (strpos($attrs, 'id=') === false) {
            $replacement = sprintf('<h%d%s id="%s">%s</h%d>', $level, $attrs, esc_attr($slug), $m[3], $level);
            $content = str_replace($m[0], $replacement, $content);
        }

        $toc .= sprintf('<li class="toc-level-%d"><a href="#%s">%s</a></li>', $level, esc_attr($slug), esc_html($raw_text));
        $count++;
    }

    $toc .= '</ul></aside>';

    if (stripos($content, '[insert_calc]') !== false) {
        return preg_replace('/(\[insert_calc\])/iu', '$1' . $toc, $content, 1);
    }

    return $toc . $content;
}
add_filter('the_content', 'ch_inject_toc', 15);

function ch_collect_linkable_calculators(int $exclude_id = 0): array
{
    $posts = get_posts([
        'post_type' => 'calculator',
        'post_status' => 'publish',
        'numberposts' => -1,
        'exclude' => [$exclude_id],
    ]);

    $map = [];
    foreach ($posts as $post) {
        $seo = ch_get_seo_data($post->ID);
        $keywords = array_filter(array_map('trim', explode(',', $seo['keywords'] ?? '')));
        $anchors = array_unique(array_filter(array_merge([get_the_title($post->ID)], $keywords)));

        foreach ($anchors as $anchor) {
            if (mb_strlen($anchor) < 4) {
                continue;
            }
            $map[$anchor] = get_permalink($post->ID);
        }
    }

    uasort($map, static function ($a, $b) {
        return strlen((string) $b) <=> strlen((string) $a);
    });

    return $map;
}

function ch_apply_internal_linking(string $content): string
{
    if (!is_singular('calculator') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $map = ch_collect_linkable_calculators(get_the_ID());
    if (empty($map)) {
        return $content;
    }

    $linked = 0;
    foreach ($map as $anchor => $url) {
        if ($linked >= 5) {
            break;
        }

        $pattern = '/(?<![\w\-\/>])(' . preg_quote($anchor, '/') . ')(?![^<]*>)/iu';
        $replacement = '<a href="' . esc_url($url) . '" class="auto-link" data-auto-link="1">$1</a>';
        $new_content = preg_replace($pattern, $replacement, $content, 1);

        if ($new_content !== null && $new_content !== $content) {
            $content = $new_content;
            $linked++;
        }
    }

    return $content;
}
add_filter('the_content', 'ch_apply_internal_linking', 25);

add_shortcode('insert_calc', function (): string {
    $post_id = get_the_ID();
    $code = (string) get_post_meta($post_id, CH_CODE_META_KEY, true);
    if ($code === '') {
        return '<div class="custom-calculator-holder">Код калькулятора пока не добавлен.</div>';
    }

    return '<div class="custom-calculator-holder">' . $code . '</div>';
});

add_shortcode('calc_related', function ($atts): string {
    $atts = shortcode_atts(['count' => 6], $atts, 'calc_related');
    $count = max(1, (int) $atts['count']);
    $related = ch_get_related_calculators(get_the_ID(), $count);

    if (empty($related)) {
        return '<p>Похожие калькуляторы скоро появятся.</p>';
    }

    $html = '<div class="related-grid">';
    foreach ($related as $item) {
        $html .= '<a class="related-card" href="' . esc_url(get_permalink($item->ID)) . '">' . esc_html(get_the_title($item->ID)) . '</a>';
    }
    $html .= '</div>';

    return $html;
});

function ch_get_related_calculators(int $post_id, int $limit = 6): array
{
    $terms = get_the_terms($post_id, 'calc_category');
    $term_ids = ($terms && !is_wp_error($terms)) ? wp_list_pluck($terms, 'term_id') : [];

    $args = [
        'post_type' => 'calculator',
        'posts_per_page' => 18,
        'post__not_in' => [$post_id],
        'post_status' => 'publish',
    ];

    if (!empty($term_ids)) {
        $args['tax_query'] = [[
            'taxonomy' => 'calc_category',
            'field' => 'term_id',
            'terms' => $term_ids,
        ]];
    }

    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return [];
    }

    $source_keywords = array_filter(array_map('trim', explode(',', ch_get_seo_data($post_id)['keywords'])));
    $scored = [];

    while ($query->have_posts()) {
        $query->the_post();
        $item_id = get_the_ID();
        $item_keywords = array_filter(array_map('trim', explode(',', ch_get_seo_data($item_id)['keywords'])));

        $common = array_intersect(array_map('mb_strtolower', $source_keywords), array_map('mb_strtolower', $item_keywords));
        $score = (count($common) * 3) + (has_term($term_ids, 'calc_category', $item_id) ? 2 : 0);
        $score += (int) max(1, round((100 - min(100, abs(time() - strtotime((string) get_post_field('post_date', $item_id))) / DAY_IN_SECONDS)) / 40));

        $scored[] = ['id' => $item_id, 'score' => $score];
    }
    wp_reset_postdata();

    usort($scored, static fn($a, $b) => $b['score'] <=> $a['score']);
    $top = array_slice($scored, 0, $limit);

    return array_map(static fn($row) => get_post($row['id']), $top);
}

function ch_breadcrumbs(): void
{
    if (is_front_page()) {
        return;
    }

    echo '<nav class="ch-breadcrumbs" aria-label="Хлебные крошки">';
    echo '<a href="' . esc_url(home_url('/')) . '">Главная</a>';

    if (is_post_type_archive('calculator')) {
        echo '<span>/</span><span>Калькуляторы</span>';
    } elseif (is_singular('calculator')) {
        $terms = get_the_terms(get_the_ID(), 'calc_category');
        echo '<span>/</span><a href="' . esc_url(get_post_type_archive_link('calculator')) . '">Калькуляторы</a>';
        if ($terms && !is_wp_error($terms)) {
            echo '<span>/</span><a href="' . esc_url(get_term_link($terms[0])) . '">' . esc_html($terms[0]->name) . '</a>';
        }
        echo '<span>/</span><span>' . esc_html(get_the_title()) . '</span>';
    }

    echo '</nav>';
}

function ch_calculate_seo_score(int $post_id, ?array $seo_data = null): int
{
    $seo = $seo_data ?: ch_get_seo_data($post_id);
    $content = (string) get_post_field('post_content', $post_id);
    $title = (string) get_post_field('post_title', $post_id);

    $score = 0;
    $score += mb_strlen($title) >= 30 ? 20 : 10;
    $score += mb_strlen($seo['title']) >= 45 ? 20 : 5;
    $score += mb_strlen($seo['desc']) >= 120 ? 20 : 5;
    $score += substr_count(strtolower($content), '<h2') >= 2 ? 15 : 0;
    $score += str_contains($content, '[insert_calc]') ? 15 : 0;
    $score += !empty($seo['keywords']) ? 10 : 0;

    return min(100, $score);
}

add_filter('manage_calculator_posts_columns', function (array $columns): array {
    $columns['ch_seo_score'] = 'SEO Score';
    return $columns;
});

add_action('manage_calculator_posts_custom_column', function (string $column, int $post_id): void {
    if ($column !== 'ch_seo_score') {
        return;
    }

    $score = ch_calculate_seo_score($post_id);
    $class = $score >= 75 ? 'score-good' : ($score >= 50 ? 'score-mid' : 'score-low');
    echo '<span class="ch-score-badge ' . esc_attr($class) . '">' . esc_html((string) $score) . '</span>';
}, 10, 2);

add_action('admin_head', function (): void {
    echo '<style>.ch-score-badge{padding:4px 8px;border-radius:999px;font-weight:700}.score-good{background:#dcfce7;color:#166534}.score-mid{background:#fef9c3;color:#854d0e}.score-low{background:#fee2e2;color:#991b1b}</style>';
});

add_action('init', function () {
    add_rewrite_rule('^calculator-sitemap\.xml$', 'index.php?ch_calc_sitemap=1', 'top');
});

add_filter('query_vars', function (array $vars): array {
    $vars[] = 'ch_calc_sitemap';
    return $vars;
});

add_action('template_redirect', function (): void {
    if ((int) get_query_var('ch_calc_sitemap') !== 1) {
        return;
    }

    $posts = get_posts([
        'post_type' => 'calculator',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    header('Content-Type: application/xml; charset=' . get_bloginfo('charset'));
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($posts as $post) {
        echo '<url><loc>' . esc_url(get_permalink($post->ID)) . '</loc><lastmod>' . esc_html(get_post_modified_time('c', true, $post->ID)) . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
});
