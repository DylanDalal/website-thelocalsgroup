<?php
/**
 * ACF field-group registration (PHP-defined so groups travel with the theme).
 *
 * Requires the Advanced Custom Fields plugin (free or Pro). Field groups are
 * skipped silently if ACF is not active — install via the WP admin or via
 * Composer (wpackagist-plugin/advanced-custom-fields).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_state',
        'title'    => 'State Page',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'state']]],
        'fields'   => [
            ['key' => 'field_state_abbr',          'name' => 'abbreviation',      'label' => 'State abbreviation (2-letter, e.g. FL)', 'type' => 'text', 'maxlength' => 2],
            ['key' => 'field_state_hero',          'name' => 'hero_image',        'label' => 'Hero image',        'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_state_idx_default',   'name' => 'idx_default_query', 'label' => 'IDX default query', 'type' => 'text'],
            ['key' => 'field_state_lifestyle_hero','name' => 'lifestyle_hero',    'label' => 'Lifestyle hero',    'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_state_lifestyle_tag', 'name' => 'lifestyle_tagline', 'label' => 'Lifestyle tagline', 'type' => 'text'],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_town',
        'title'    => 'Town Detail',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'town']]],
        'fields'   => [
            ['key' => 'field_town_state',     'name' => 'state',         'label' => 'State', 'type' => 'post_object', 'post_type' => ['state'], 'return_format' => 'id'],
            ['key' => 'field_town_blurb',     'name' => 'blurb',         'label' => 'Short description', 'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_town_gallery',   'name' => 'gallery',       'label' => 'Gallery', 'type' => 'gallery', 'return_format' => 'array'],
            ['key' => 'field_town_idx_query', 'name' => 'idx_query',     'label' => 'IDX query (town slug or polygon)', 'type' => 'text'],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_agent',
        'title'    => 'Agent',
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'agent']]],
        'fields'   => [
            ['key' => 'field_agent_role',     'name' => 'role',     'label' => 'Role / title', 'type' => 'text'],
            ['key' => 'field_agent_phone',    'name' => 'phone',    'label' => 'Phone',        'type' => 'text'],
            ['key' => 'field_agent_email',    'name' => 'email',    'label' => 'Email',        'type' => 'email'],
            ['key' => 'field_agent_lofty_id', 'name' => 'lofty_id', 'label' => 'Lofty agent ID', 'type' => 'text'],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_landing',
        'title'    => 'Landing Page',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'front-page.php']]],
        'fields'   => [
            ['key' => 'field_landing_hero_video',    'name' => 'hero_video_url',  'label' => 'Hero background video URL', 'type' => 'url'],
            ['key' => 'field_landing_hero_fallback', 'name' => 'hero_fallback',   'label' => 'Hero fallback image',       'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_landing_mission',       'name' => 'mission',         'label' => 'Mission text',              'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_landing_highlight_towns', 'name' => 'highlight_towns', 'label' => 'Highlighted towns',       'type' => 'relationship', 'post_type' => ['town'], 'max' => 8],
        ],
    ]);
});
