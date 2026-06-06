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
            ['key' => 'field_state_feature_title', 'name' => 'lifestyle_feature_title', 'label' => 'Lifestyle feature title (e.g. "Coastal Living")', 'type' => 'text'],
            ['key' => 'field_state_feature_body',  'name' => 'lifestyle_feature_body',  'label' => 'Lifestyle feature body',                          'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_state_help_copy',     'name' => 'help_copy',               'label' => '"We\'re here to help" body',                      'type' => 'textarea', 'rows' => 4],
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
            ['key' => 'field_town_lat',       'name' => 'latitude',      'label' => 'Latitude  (decimal, e.g. 35.5951)',  'type' => 'text', 'instructions' => 'Paste from Google Maps. Used to place the town pin on the state map.'],
            ['key' => 'field_town_lng',       'name' => 'longitude',     'label' => 'Longitude (decimal, e.g. -82.5515)', 'type' => 'text'],
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
        'key'      => 'group_about',
        'title'    => 'About Page',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-about.php']]],
        'fields'   => [
            ['key' => 'field_about_lead',       'name' => 'lead',           'label' => 'Lead paragraph (one or two sentences)', 'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_about_mission',    'name' => 'mission',        'label' => 'Mission statement',                     'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_about_hero',       'name' => 'about_hero',     'label' => 'Hero image (right column)',             'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_about_leadership', 'name' => 'leadership',     'label' => 'Lead agent (featured)',                  'type' => 'post_object', 'post_type' => ['agent'], 'return_format' => 'object'],
            ['key' => 'field_about_regions',    'name' => 'regions_served', 'label' => 'Regions served (comma-separated)',      'type' => 'text'],
        ],
    ]);

    acf_add_local_field_group([
        'key'      => 'group_recruitment',
        'title'    => 'Recruitment Page',
        'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-recruitment.php']]],
        'fields'   => [
            ['key' => 'field_recruit_lead',     'name' => 'recruit_lead',    'label' => 'Lead paragraph',                  'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_recruit_hero',     'name' => 'recruit_hero',    'label' => 'Hero image',                      'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_recruit_benefit_1','name' => 'benefit_1',       'label' => 'Benefit 1 (heading)',             'type' => 'text'],
            ['key' => 'field_recruit_benefit_1_body','name' => 'benefit_1_body','label' => 'Benefit 1 (body)',             'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_recruit_benefit_2','name' => 'benefit_2',       'label' => 'Benefit 2 (heading)',             'type' => 'text'],
            ['key' => 'field_recruit_benefit_2_body','name' => 'benefit_2_body','label' => 'Benefit 2 (body)',             'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_recruit_benefit_3','name' => 'benefit_3',       'label' => 'Benefit 3 (heading)',             'type' => 'text'],
            ['key' => 'field_recruit_benefit_3_body','name' => 'benefit_3_body','label' => 'Benefit 3 (body)',             'type' => 'textarea', 'rows' => 3],
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
            ['key' => 'field_landing_lifestyle_eyebrow', 'name' => 'lifestyle_eyebrow', 'label' => 'Lifestyle section eyebrow', 'type' => 'text'],
            ['key' => 'field_landing_lifestyle_title',   'name' => 'lifestyle_title',   'label' => 'Lifestyle section title',   'type' => 'text'],
            ['key' => 'field_landing_lifestyle_body',    'name' => 'lifestyle_body',    'label' => 'Lifestyle section body',    'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_landing_lifestyle_image',   'name' => 'lifestyle_image',   'label' => 'Lifestyle section image',   'type' => 'image', 'return_format' => 'array'],

            // --- Dark homepage redesign (all optional; theme falls back to mockup copy) ---
            ['key' => 'field_home_tab',            'name' => '',                'label' => 'Homepage (Locals Group design)', 'type' => 'tab'],
            ['key' => 'field_home_intro',          'name' => 'home_intro',      'label' => 'Hero intro paragraph',        'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_home_group_photo',    'name' => 'home_group_photo','label' => 'Team group photo (hero, right)', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Transparent-background cutout works best. Falls back to a headshot collage.'],
            ['key' => 'field_home_action_bg',      'name' => 'home_action_bg',  'label' => 'Find/Sell section background', 'type' => 'image', 'return_format' => 'array'],
            ['key' => 'field_home_cta_find',       'name' => 'cta_find_url',    'label' => 'CTA — "Find Homes" link',     'type' => 'url'],
            ['key' => 'field_home_cta_approve',    'name' => 'cta_approve_url', 'label' => 'CTA — "Get Approved" link',   'type' => 'url'],
            ['key' => 'field_home_cta_sell',       'name' => 'cta_sell_url',    'label' => 'CTA — "Sell Homes" link',     'type' => 'url'],
            ['key' => 'field_home_touch_body',     'name' => 'touch_body',      'label' => '"Stay in Touch" body',        'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_home_touch_link',     'name' => 'touch_link',      'label' => '"Stay in Touch" button link', 'type' => 'url'],
            ['key' => 'field_home_join_vendor',    'name' => 'join_vendor_url', 'label' => 'Join — Preferred Vendor link','type' => 'url'],
            ['key' => 'field_home_join_lender',    'name' => 'join_lender_url', 'label' => 'Join — Preferred Lender link','type' => 'url'],
            ['key' => 'field_home_join_affiliate', 'name' => 'join_affiliate_url','label' => 'Join — Business Affiliate link','type' => 'url'],
            ['key' => 'field_home_join_partner',   'name' => 'join_partner_url','label' => 'Join — Partner with TLG link', 'type' => 'url'],
        ],
    ]);
});
