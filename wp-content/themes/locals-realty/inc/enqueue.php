<?php
/**
 * Asset enqueue.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'locals-realty-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Inria+Serif:ital,wght@0,300;0,400;0,700;1,400&family=Italianno&display=swap',
        [],
        null
    );

    $css_path = get_template_directory() . '/assets/css/main.css';
    $js_path  = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'locals-realty',
        LOCALS_REALTY_URI . '/assets/css/main.css',
        ['locals-realty-fonts'],
        file_exists($css_path) ? filemtime($css_path) : LOCALS_REALTY_VERSION
    );

    wp_enqueue_script(
        'locals-realty',
        LOCALS_REALTY_URI . '/assets/js/main.js',
        [],
        file_exists($js_path) ? filemtime($js_path) : LOCALS_REALTY_VERSION,
        true
    );

    // --- Dark homepage redesign: scoped assets, front page only ---
    if (is_front_page()) {
        wp_enqueue_style(
            'locals-realty-home-fonts',
            'https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=Sail&family=Caveat:wght@500;600;700&display=swap',
            [],
            null
        );

        $home_css = get_template_directory() . '/assets/css/home.css';
        $home_js  = get_template_directory() . '/assets/js/home.js';

        wp_enqueue_style(
            'locals-realty-home',
            LOCALS_REALTY_URI . '/assets/css/home.css',
            ['locals-realty', 'locals-realty-home-fonts'],
            file_exists($home_css) ? filemtime($home_css) : LOCALS_REALTY_VERSION
        );

        wp_enqueue_script(
            'locals-realty-home',
            LOCALS_REALTY_URI . '/assets/js/home.js',
            ['locals-realty'],
            file_exists($home_js) ? filemtime($home_js) : LOCALS_REALTY_VERSION,
            true
        );
    }
});
