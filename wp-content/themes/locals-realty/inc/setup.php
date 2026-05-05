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

/**
 * Resolve an ACF image field with a theme-asset fallback.
 *
 * @param mixed       $acf_image  ACF image array (return_format=array) or null/false.
 * @param string      $fallback   Filename inside assets/images/ to use if ACF empty.
 * @param string|null $size       Image size key (e.g. 'locals-hero'); falls back to original URL.
 * @return string  URL ready to drop into a src attribute, or '' if neither exists.
 */
function locals_image_url($acf_image, $fallback = '', $size = null) {
    if (is_array($acf_image) && !empty($acf_image['url'])) {
        if ($size && !empty($acf_image['sizes'][$size])) {
            return $acf_image['sizes'][$size];
        }
        return $acf_image['url'];
    }
    if ($fallback) {
        $path = LOCALS_REALTY_DIR . '/assets/images/' . ltrim($fallback, '/');
        if (file_exists($path)) {
            return LOCALS_REALTY_URI . '/assets/images/' . ltrim($fallback, '/');
        }
    }
    return '';
}

/**
 * Resolve a post's featured image with a theme-asset fallback.
 *
 * @param int|WP_Post $post      Post or ID.
 * @param string      $fallback  Filename inside assets/images/.
 * @param string      $size      Image size key.
 * @return string  URL or '' if no image.
 */
function locals_thumbnail_url($post, $fallback = '', $size = 'locals-card') {
    $post_id = is_object($post) ? $post->ID : (int) $post;
    if (has_post_thumbnail($post_id)) {
        $url = get_the_post_thumbnail_url($post_id, $size);
        if ($url) {
            return $url;
        }
    }
    if ($fallback) {
        $path = LOCALS_REALTY_DIR . '/assets/images/' . ltrim($fallback, '/');
        if (file_exists($path)) {
            return LOCALS_REALTY_URI . '/assets/images/' . ltrim($fallback, '/');
        }
    }
    return '';
}
