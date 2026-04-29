<?php
/**
 * Custom post types and taxonomies.
 *
 * State, Town, Lifestyle, Agent, and (optionally) Property.
 * Property is reserved for future direct-feed work — leave disabled while
 * Lofty IDX renders the search/listings pages.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_post_type('state', [
        'label'         => __('States', 'locals-realty'),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-location-alt',
        'has_archive'   => false,
        'rewrite'       => ['slug' => 'state', 'with_front' => false],
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
    ]);

    register_post_type('town', [
        'label'         => __('Towns', 'locals-realty'),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-admin-home',
        'has_archive'   => false,
        'rewrite'       => ['slug' => 'town', 'with_front' => false],
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
    ]);

    register_post_type('agent', [
        'label'         => __('Agents', 'locals-realty'),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-businessperson',
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'agent', 'with_front' => false],
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
    ]);

    register_taxonomy('lifestyle', ['town', 'agent'], [
        'label'        => __('Lifestyles', 'locals-realty'),
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'lifestyle'],
    ]);

    register_taxonomy('state_region', ['town', 'agent'], [
        'label'        => __('State', 'locals-realty'),
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => ['slug' => 'state-region'],
    ]);
});
