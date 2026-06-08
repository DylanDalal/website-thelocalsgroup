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

/**
 * Resolve the homepage agent roster used by the dark landing redesign.
 *
 * Source of truth is the `agent` CPT (ordered by menu_order then title). If no
 * agent posts exist yet, falls back to auto-discovering placeholder headshots
 * dropped into assets/images/team/ — filenames become display names
 * (e.g. "rachel_garcia.webp" -> "Rachel Garcia").
 *
 * @param int $limit  Max entries to return (0 = all).
 * @return array<int, array{name:string, role:string, img:string, url:string}>
 */
function locals_home_roster($limit = 0) {
    $out = [];

    $agents = get_posts([
        'post_type'      => 'agent',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);
    foreach ($agents as $a) {
        $out[] = [
            'name' => get_the_title($a),
            'role' => function_exists('get_field') ? (get_field('role', $a->ID) ?: 'Local Agent') : 'Local Agent',
            'img'  => locals_thumbnail_url($a, '', 'locals-card'),
            'url'  => get_permalink($a),
        ];
    }

    if (!$out) {
        $dir = LOCALS_REALTY_DIR . '/assets/images/team';
        $files = glob($dir . '/*.{webp,jpg,jpeg,png}', GLOB_BRACE) ?: [];
        sort($files);
        foreach ($files as $f) {
            $base = pathinfo($f, PATHINFO_FILENAME);
            $out[] = [
                'name' => ucwords(str_replace(['_', '-'], ' ', $base)),
                'role' => 'Local Agent',
                'img'  => LOCALS_REALTY_URI . '/assets/images/team/' . rawurlencode(basename($f)),
                'url'  => '#',
            ];
        }
    }

    if ($limit > 0) {
        $out = array_slice($out, 0, $limit);
    }
    return $out;
}

/**
 * Face-normalized agent cutouts used by the editorial hero cluster.
 *
 * Pulls the uniform .webp cutouts from assets/images/team/normalized/ (faces
 * pre-aligned to the same size) and pairs each with its roster entry so the
 * cluster can link through to the agent's page. Names come from the filename
 * (e.g. "chris_igoe.webp" -> "Chris Igoe"); the URL is the matching agent
 * permalink when one exists, otherwise '#'.
 *
 * @param int $limit Max cutouts to return (0 = all, ordered by filename).
 * @return array<int, array{name:string, img:string, url:string}>
 */
function locals_home_cluster($limit = 0) {
    $dir = LOCALS_REALTY_DIR . '/assets/images/team/normalized';
    $files = glob($dir . '/*.{webp,jpg,jpeg,png}', GLOB_BRACE) ?: [];
    sort($files);

    // Index roster by normalized name so cutouts can resolve real permalinks.
    $by_name = [];
    foreach (locals_home_roster() as $member) {
        $by_name[strtolower($member['name'])] = $member['url'];
    }

    // Per-cutout zoom correction: these "normalized" crops still frame each
    // subject at a slightly different scale, so equal display width gives
    // unequal heads. The multiplier evens out apparent head size (1 = base).
    $head_scale = [
        'chris_igoe'     => 1.06,
        'kelly_jones'    => 0.80,
        'glen_asher'     => 1.34,
        'rachel_garcia'  => 0.90,
        'william_dailey' => 1.14,
    ];

    $out = [];
    foreach ($files as $f) {
        $base = pathinfo($f, PATHINFO_FILENAME);
        $name = ucwords(str_replace(['_', '-'], ' ', $base));
        $out[] = [
            'name'  => $name,
            'slug'  => $base,
            'scale' => $head_scale[$base] ?? 1.0,
            'img'   => LOCALS_REALTY_URI . '/assets/images/team/normalized/' . rawurlencode(basename($f)),
            'url'   => $by_name[strtolower($name)] ?? '#',
        ];
    }

    if ($limit > 0) {
        $out = array_slice($out, 0, $limit);
    }
    return $out;
}

/**
 * Per-state default copy used by single-state.php when the corresponding ACF
 * field is empty. Keyed by state slug (post slug of the state CPT).
 */
function locals_state_defaults($slug) {
    $map = [
        'florida' => [
            'lifestyle_tagline'       => 'Live where the season never quite ends.',
            'lifestyle_feature_title' => 'Coastal Living',
            'lifestyle_feature_body'  => 'From the quiet inlets of Jupiter to the energy of Miami, Florida rewards a life lived outdoors. Our local agents help you find the right stretch of coast — the boating town, the gated community, the walkable beach village — and the home that fits how you actually plan to spend your weekends.',
            'help_copy'               => 'Whether you\'re relocating from the Northeast, trading up to a waterfront, or buying a second home in the sun, our Florida team knows the inventory, the HOAs, and the insurance landscape. We\'ll guide you through it.',
        ],
        'north-carolina' => [
            'lifestyle_tagline'       => 'Mountains, music, and Main Streets — North Carolina at its most welcoming.',
            'lifestyle_feature_title' => 'Mountain Towns',
            'lifestyle_feature_body'  => 'Asheville\'s arts scene, the Blue Ridge Parkway out your back door, four real seasons, and small towns where the brewery and the bookshop know your name. North Carolina rewards people who want a community to belong to — not just an address.',
            'help_copy'               => 'Our North Carolina agents live in the towns they sell — from Asheville and Black Mountain to the lake communities near Hickory. We\'ll help you weigh elevation, drive times, and the rhythm of each neighborhood before you commit.',
        ],
        'south-carolina' => [
            'lifestyle_tagline'       => 'Lowcountry charm, historic streets, and tides that set the pace.',
            'lifestyle_feature_title' => 'Lowcountry Living',
            'lifestyle_feature_body'  => 'Charleston\'s historic peninsula, the marshes of Mount Pleasant, the barrier islands and the inland golf communities — South Carolina is a state where front porches still mean something. We help buyers translate that charm into the right zip code for their life.',
            'help_copy'               => 'From historic district renovations to new construction on Daniel Island, our South Carolina team understands the quirks — flood zones, BAR review, dock permits — that the listing photo doesn\'t show. We\'ll get you to the right yes.',
        ],
        'tennessee' => [
            'lifestyle_tagline'       => 'Music City energy, Smoky Mountain quiet, and everything in between.',
            'lifestyle_feature_title' => 'Nashville & Beyond',
            'lifestyle_feature_body'  => 'Nashville\'s neighborhoods each have their own song — East Nashville\'s creative pulse, Germantown\'s walkability, Franklin\'s small-town feel a short drive south. Head east and the pace shifts again toward Knoxville, Chattanooga, and the Smokies. We help you find the part of Tennessee that actually sounds like you.',
            'help_copy'               => 'Tennessee\'s growth is real, and the market moves fast. Our local agents have the relationships and the off-market intel to help you compete — and the patience to make sure the home you land is the one you actually wanted.',
        ],
    ];
    return $map[$slug] ?? [
        'lifestyle_tagline'       => 'We want to help you reach your new lifestyle.',
        'lifestyle_feature_title' => 'Small Towns',
        'lifestyle_feature_body'  => 'Our mission is simple: combine world-class real estate expertise with innovative marketing that elevates every listing, humanizes every transaction, and builds long-term relationships within the communities we proudly serve.',
        'help_copy'               => 'Our local agents know this region the way you only can by living in it. Tell us what you\'re looking for and we\'ll help you find it.',
    ];
}
