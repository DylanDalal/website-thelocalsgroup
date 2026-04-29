<?php
/**
 * Lofty IDX integration helpers.
 *
 * Lofty does not ship a first-party WordPress plugin. The supported integration
 * shapes are:
 *   1. iframe an IDX subdomain (e.g. search.thelocalsgroup.com)
 *   2. embed Lofty JS widgets / lead-capture snippets
 *   3. consume the Lofty API directly into a custom Property post type
 *
 * Phase 1 (this scaffold) supports (1) and (2). Configure the values below in
 * wp-config.php or via Settings → General once we ship a settings page.
 *
 *   define('LOFTY_IDX_BASE_URL', 'https://search.thelocalsgroup.com');
 *   define('LOFTY_WIDGET_KEY',   '<your-widget-key>');
 */

if (!defined('ABSPATH')) {
    exit;
}

function locals_lofty_idx_base() {
    return defined('LOFTY_IDX_BASE_URL') ? rtrim(LOFTY_IDX_BASE_URL, '/') : '';
}

function locals_lofty_search_url($query = []) {
    $base = locals_lofty_idx_base();
    if (!$base) {
        return '#';
    }
    return $query
        ? $base . '/search?' . http_build_query($query)
        : $base . '/search';
}

function locals_lofty_iframe($query = [], $attrs = []) {
    $url = locals_lofty_search_url($query);
    $defaults = [
        'class'           => 'locals-idx-iframe',
        'loading'         => 'lazy',
        'referrerpolicy'  => 'no-referrer-when-downgrade',
        'allow'           => 'geolocation',
        'title'           => __('Property search', 'locals-realty'),
    ];
    $attrs = array_merge($defaults, $attrs);
    $html  = '<iframe src="' . esc_url($url) . '"';
    foreach ($attrs as $k => $v) {
        $html .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
    }
    $html .= '></iframe>';
    return $html;
}

add_shortcode('lofty_search', function ($atts) {
    $atts = shortcode_atts([
        'town'      => '',
        'state'     => '',
        'lifestyle' => '',
        'height'    => '800',
    ], $atts, 'lofty_search');

    $query = array_filter([
        'town'      => $atts['town'],
        'state'     => $atts['state'],
        'lifestyle' => $atts['lifestyle'],
    ]);

    return locals_lofty_iframe($query, [
        'style' => 'width:100%;height:' . intval($atts['height']) . 'px;border:0;',
    ]);
});
