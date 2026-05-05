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

    wp_enqueue_style(
        'locals-realty',
        LOCALS_REALTY_URI . '/assets/css/main.css',
        ['locals-realty-fonts'],
        LOCALS_REALTY_VERSION
    );

    wp_enqueue_script(
        'locals-realty',
        LOCALS_REALTY_URI . '/assets/js/main.js',
        [],
        LOCALS_REALTY_VERSION,
        true
    );
});
