<?php
/**
 * Theme setup: supports, menus, image sizes.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    add_theme_support('custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary', 'locals-realty'),
        'footer'  => __('Footer', 'locals-realty'),
    ]);

    add_image_size('locals-hero',     1920, 900,  true);
    add_image_size('locals-card',     800,  600,  true);
    add_image_size('locals-town',     400,  500,  true);
    add_image_size('locals-property', 800,  500,  true);
});

add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
