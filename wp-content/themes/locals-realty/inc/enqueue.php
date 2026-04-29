<?php
/**
 * Asset enqueue.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'locals-realty',
        LOCALS_REALTY_URI . '/assets/css/main.css',
        [],
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
