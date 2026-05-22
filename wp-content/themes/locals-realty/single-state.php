<?php
/**
 * State page (single state CPT).
 *
 * Sections:
 *  1. State hero (full-width 16:9) with state name + IDX search bar.
 *  2. "Our favorites:" town list + detail card.
 *  3. Lifestyle hero (16:9).
 *  4. Lifestyle pill row + featured lifestyle block (default: Small Towns).
 *  5. "We're here to help." split CTA.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();
    $hero       = get_field('hero_image');
    $state_slug = sanitize_title(get_the_title());

    // Tag this visitor's preferred state so the homepage can tailor the
    // mission "build the next version of your life." card next time.
    $state_abbr = function_exists('get_field') ? (get_field('abbreviation') ?: '') : '';
    if (!$state_abbr) {
        $state_abbr = strtoupper(substr(get_the_title(), 0, 2));
    }
    if (!headers_sent() && $state_abbr) {
        setcookie('locals_pref_state', $state_abbr, [
            'expires'  => time() + 30 * DAY_IN_SECONDS,
            'path'     => '/',
            'samesite' => 'Lax',
            'secure'   => is_ssl(),
            'httponly' => false,
        ]);
        $_COOKIE['locals_pref_state'] = $state_abbr;
    }
    // Match the landing-page state card image so the View Transition morph
    // shares a single source asset (state-card-{slug}.jpg) on both ends.
    $hero_url   = locals_thumbnail_url(get_the_ID(), "state-card-{$state_slug}.jpg", 'locals-hero')
               ?: locals_thumbnail_url(0, 'state-card-default.jpg', 'locals-hero');

    $life_hero  = get_field('lifestyle_hero');
    $life_url   = locals_image_url($life_hero, 'default-lifestyle-hero.jpg', 'locals-hero');

    $defaults        = locals_state_defaults($state_slug);
    $life_tag        = get_field('lifestyle_tagline')       ?: $defaults['lifestyle_tagline'];
    $feature_title   = get_field('lifestyle_feature_title') ?: $defaults['lifestyle_feature_title'];
    $feature_body    = get_field('lifestyle_feature_body')  ?: $defaults['lifestyle_feature_body'];
    $help_copy       = get_field('help_copy')               ?: $defaults['help_copy'];

    $towns      = get_posts([
        'post_type'      => 'town',
        'posts_per_page' => 12,
        'tax_query'      => [[
            'taxonomy' => 'state_region',
            'field'    => 'slug',
            'terms'    => $state_slug,
        ]],
    ]);
    $lifestyles = get_terms(['taxonomy' => 'lifestyle', 'hide_empty' => false]);
    $mission    = __('Our mission is simple: combine world-class real estate expertise with innovative marketing that elevates every listing, humanizes every transaction, and builds long-term relationships within the communities we proudly serve.', 'locals-realty');
?>

<?php $state_geom = locals_state_map_geom($state_slug); ?>
<section class="hero hero--state" data-state="<?php echo esc_attr($state_slug); ?>">
    <div class="hero__media">
        <?php if ($hero_url) : ?>
            <img class="hero__img"
                 src="<?php echo esc_url($hero_url); ?>"
                 alt=""
                 fetchpriority="high"
                 decoding="sync"
                 style="view-transition-name: state-photo-<?php echo esc_attr($state_slug); ?>;">
        <?php endif; ?>
    </div>
    <div class="hero__content">
        <h1 class="hero__title hero__title--state"
            style="view-transition-name: state-label-<?php echo esc_attr($state_slug); ?>;"><?php the_title(); ?></h1>
        <form class="hero__search" action="<?php echo esc_url(home_url('/search')); ?>" method="get">
            <input type="hidden" name="state" value="<?php echo esc_attr($state_abbr); ?>">
            <input name="q" type="search" placeholder="Search by town, address, agent...">
            <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
        </form>
    </div>
</section>

<?php if ($state_slug !== 'florida' && !empty($towns)) :
    // Build the town data set once, so the map and list share identical projection.
    $town_data = [];
    foreach ($towns as $t) {
        $coords = locals_town_latlng($t);
        $xy     = null;
        if ($coords && $state_geom) {
            $xy = locals_project_latlng($coords[0], $coords[1], $state_slug);
        }
        $town_data[] = [
            'post'   => $t,
            'coords' => $coords,
            'xy'     => $xy,
            'blurb'  => function_exists('get_field') ? get_field('blurb', $t->ID) : '',
            'img'    => locals_thumbnail_url($t, '', 'locals-card'),
            'href'   => get_permalink($t),
        ];
    }
?>
<section class="state-map container<?php echo $state_geom ? ' state-map--geo' : ''; ?>"
         data-state-map data-state="<?php echo esc_attr($state_slug); ?>">
    <h2 class="section-title state-map__title">Our favorites:</h2>
    <div class="state-map__layout">
        <?php if ($state_geom) : ?>
        <div class="state-map__stage">
            <div class="state-map__sticky">
                <svg class="state-map__svg"
                     viewBox="0 0 <?php echo esc_attr($state_geom['view_w']); ?> <?php echo esc_attr($state_geom['view_h']); ?>"
                     preserveAspectRatio="xMidYMid meet"
                     aria-hidden="true"
                     data-state-svg>
                    <g class="state-map__camera" data-state-camera>
                        <path class="state-map__fill"    d="<?php echo esc_attr($state_geom['path']); ?>" />
                        <path class="state-map__outline" d="<?php echo esc_attr($state_geom['path']); ?>" pathLength="1" data-state-outline />
                        <g class="state-map__pins" data-state-pins>
                            <?php foreach ($town_data as $i => $td) :
                                if (!$td['xy']) { continue; }
                                [$x, $y] = $td['xy'];
                            ?>
                                <g class="state-map__pin"
                                   data-town-id="<?php echo esc_attr($td['post']->ID); ?>"
                                   data-pin-index="<?php echo (int) $i; ?>"
                                   data-x="<?php echo esc_attr($x); ?>"
                                   data-y="<?php echo esc_attr($y); ?>"
                                   transform="translate(<?php echo esc_attr($x); ?> <?php echo esc_attr($y); ?>)"
                                   style="--pin-delay: <?php echo (int) ($i * 80); ?>ms">
                                    <circle class="state-map__pin-ring" r="14" />
                                    <circle class="state-map__pin-dot"  r="5"  />
                                    <text   class="state-map__pin-label" y="-22" text-anchor="middle"><?php echo esc_html(get_the_title($td['post'])); ?></text>
                                </g>
                            <?php endforeach; ?>
                        </g>
                    </g>
                </svg>
                <div class="state-map__compass" aria-hidden="true">
                    <span><?php echo esc_html($state_abbr ?: strtoupper(substr($state_slug, 0, 2))); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="state-map__rail">
            <ol class="favorites__list state-map__list" data-favorites-list>
                <?php foreach ($town_data as $i => $td) : ?>
                    <li class="state-map__list-item <?php echo $i === 0 ? 'is-active' : ''; ?>"
                        data-town-id="<?php echo esc_attr($td['post']->ID); ?>"
                        data-pin-index="<?php echo (int) $i; ?>"
                        <?php if ($td['xy']) : ?>data-x="<?php echo esc_attr($td['xy'][0]); ?>" data-y="<?php echo esc_attr($td['xy'][1]); ?>"<?php endif; ?>
                        data-blurb="<?php echo esc_attr($td['blurb']); ?>"
                        data-img="<?php echo esc_url($td['img']); ?>"
                        data-href="<?php echo esc_url($td['href']); ?>"
                        data-title="<?php echo esc_attr(get_the_title($td['post'])); ?>">
                        <button type="button">
                            <span class="state-map__list-num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
                            <span class="state-map__list-name"><?php echo esc_html(get_the_title($td['post'])); ?></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="favorites__detail state-map__detail" data-favorites-detail>
                <?php if (!empty($town_data[0])) :
                    $first_td = $town_data[0];
                ?>
                    <?php if ($first_td['img']) : ?><img data-detail-img src="<?php echo esc_url($first_td['img']); ?>" alt=""><?php endif; ?>
                    <p data-detail-blurb><?php echo esc_html($first_td['blurb']); ?></p>
                    <a data-detail-link href="<?php echo esc_url($first_td['href']); ?>">
                        <?php printf(esc_html__('View properties in %s', 'locals-realty'), esc_html(get_the_title($first_td['post']))); ?> &rarr;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
if ($state_slug === 'florida') :
    $fl_pill_candidates = [
        ['label' => 'Jupiter',        'city' => 'Jupiter'],
        ['label' => 'Naples',         'city' => 'Naples'],
        ['label' => 'Miami',          'city' => 'Miami'],
        ['label' => 'Tampa',          'city' => 'Tampa'],
        ['label' => 'Sarasota',       'city' => 'Sarasota'],
        ['label' => 'Palm Beach',     'city' => 'Palm Beach'],
        ['label' => 'Bonita Springs', 'city' => 'Bonita Springs'],
        ['label' => 'Boca Raton',     'city' => 'Boca Raton'],
        ['label' => 'Orlando',        'city' => 'Orlando'],
    ];
    // Render all candidate pills up-front; empty ones surface the existing
    // "No active listings" empty state on click. Previously we probed each
    // city's Lofty listings synchronously during render, which blocked the
    // document by ~2-4s on a cold cache and made the cross-document View
    // Transition into this page feel laggy.
    $fl_pills = $fl_pill_candidates;
    if ($fl_pills) : ?>
<section class="highlights container" data-reveal>
    <h2 class="section-title"><?php esc_html_e('Florida properties', 'locals-realty'); ?></h2>
    <ul class="highlights__pills" data-listings-filters>
        <?php foreach ($fl_pills as $i => $p) :
            $filter = ['city' => $p['city'], 'state' => 'FL', 'scope' => 'office', 'limit' => 9];
        ?>
            <li>
                <button type="button"
                        class="highlights__pill <?php echo $i === 0 ? 'is-active' : ''; ?>"
                        data-filter="<?php echo esc_attr(wp_json_encode($filter)); ?>">
                    <?php echo esc_html($p['label']); ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="highlights__carousel" data-carousel>
        <button type="button" class="highlights__nav highlights__nav--prev" data-carousel-prev
                aria-label="<?php esc_attr_e('Previous properties', 'locals-realty'); ?>" hidden>
            <span aria-hidden="true">&larr;</span>
        </button>
        <div class="highlights__cards" data-listings-grid aria-live="polite" aria-busy="false">
            <?php
            $initial = ['city' => $fl_pills[0]['city'], 'state' => 'FL', 'scope' => 'office', 'limit' => 9];
            locals_render_listings($initial, __('No active listings here right now.', 'locals-realty'));
            ?>
        </div>
        <button type="button" class="highlights__nav highlights__nav--next" data-carousel-next
                aria-label="<?php esc_attr_e('Next properties', 'locals-realty'); ?>" hidden>
            <span aria-hidden="true">&rarr;</span>
        </button>
    </div>
</section>
<?php endif; endif; ?>

<section class="hero hero--lifestyle<?php echo $state_slug === 'florida' ? ' hero--lifestyle-flipbook' : ''; ?>"
         <?php echo $state_slug === 'florida' ? 'data-flipbook' : ''; ?>>
    <div class="hero__media">
        <?php if ($state_slug === 'florida') :
            $frame_base = get_template_directory_uri() . '/assets/images/';
            for ($i = 1; $i <= 6; $i++) : ?>
                <img class="hero-flipbook__frame"
                     data-frame="<?php echo (int) ($i - 1); ?>"
                     src="<?php echo esc_url($frame_base . 'florida' . $i . '.webp'); ?>"
                     alt=""
                     <?php echo $i === 1 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                     decoding="async">
            <?php endfor;
        elseif ($life_url) : ?>
            <img class="hero__img" src="<?php echo esc_url($life_url); ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="hero__content">
        <h2 class="hero__title">Lifestyle realty.</h2>
        <p style="max-width:32ch"><?php echo esc_html($life_tag); ?></p>
    </div>
</section>

<?php
$lifestyle_pills = [];
if ($lifestyles && !is_wp_error($lifestyles)) {
    foreach ($lifestyles as $term) {
        $lifestyle_pills[] = [
            'name' => $term->name,
            'slug' => $term->slug,
            'href' => get_term_link($term),
        ];
    }
} else {
    foreach (['Coastal Living', 'Small Towns', 'Fishing Focused', 'Theme Parks'] as $name) {
        $lifestyle_pills[] = [
            'name' => $name,
            'slug' => sanitize_title($name),
            'href' => '',
        ];
    }
}
$region_data = locals_state_lifestyle_regions($state_slug);
$has_lifestyle_map = $state_geom && !empty($region_data);
$active_slug = !empty($lifestyle_pills) ? $lifestyle_pills[0]['slug'] : '';
?>
<section class="lifestyles container<?php echo $has_lifestyle_map ? ' lifestyles--mapped' : ''; ?>"
         data-reveal
         <?php if ($has_lifestyle_map) : ?>data-lifestyles-map data-state="<?php echo esc_attr($state_slug); ?>"<?php endif; ?>>
    <ul class="lifestyles__pills" data-lifestyles>
        <?php foreach ($lifestyle_pills as $i => $p) :
            $cls = $i === 0 ? 'is-active' : ''; ?>
            <li class="<?php echo esc_attr($cls); ?>" data-region="<?php echo esc_attr($p['slug']); ?>">
                <?php if ($p['href']) : ?>
                    <a href="<?php echo esc_url($p['href']); ?>"><?php echo esc_html($p['name']); ?></a>
                <?php else : ?>
                    <?php echo esc_html($p['name']); ?>
                <?php endif; ?>
            </li>
            <?php if ($i < count($lifestyle_pills) - 1) : ?>
                <li aria-hidden="true">&middot;</li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <div class="lifestyles__feature">
        <?php if ($has_lifestyle_map) : ?>
            <div class="lifestyles__map" data-lifestyles-map-svg>
                <svg viewBox="0 0 <?php echo esc_attr($state_geom['view_w']); ?> <?php echo esc_attr($state_geom['view_h']); ?>"
                     preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                    <path class="lifestyles__map-fill" d="<?php echo esc_attr($state_geom['path']); ?>" />
                    <path class="lifestyles__map-outline" d="<?php echo esc_attr($state_geom['path']); ?>" pathLength="1" />
                    <g class="lifestyles__map-regions">
                        <?php foreach ($region_data as $region_slug => $points) :
                            $projected = locals_project_region($points, $state_slug);
                            if (empty($projected)) { continue; }
                            $path_d   = locals_smooth_path_d($projected);
                        ?>
                            <g class="lifestyles__map-region" data-region="<?php echo esc_attr($region_slug); ?>">
                                <path class="lifestyles__map-region-line"
                                      d="<?php echo esc_attr($path_d); ?>"
                                      pathLength="1" />
                            </g>
                        <?php endforeach; ?>
                    </g>
                </svg>
            </div>
        <?php else : ?>
            <div class="lifestyles__feature-media">
                <?php
                for ($n = 1; $n <= 3; $n++) {
                    $u = locals_image_url(null, "lifestyle-small-towns-{$n}.jpg");
                    if ($u) {
                        echo '<img src="' . esc_url($u) . '" alt="">';
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        <div class="lifestyles__feature-body">
            <h3><?php echo esc_html($feature_title); ?></h3>
            <p><?php echo esc_html($feature_body); ?></p>
            <p style="margin-top:1rem"><a class="highlights__view-all" href="<?php echo esc_url(home_url('/search?state=' . $state_abbr)); ?>"><?php printf(esc_html__('View %s properties', 'locals-realty'), esc_html(strtolower($feature_title))); ?> &rarr;</a></p>
        </div>
    </div>
</section>

<section class="container split split--reverse" data-reveal>
    <?php $help_img = locals_image_url(null, 'were-here-to-help.jpg'); ?>
    <div class="split__media">
        <?php if ($help_img) : ?><img src="<?php echo esc_url($help_img); ?>" alt=""><?php endif; ?>
    </div>
    <div class="split__body">
        <h2>We're here to help.</h2>
        <p><?php echo esc_html($help_copy); ?></p>
        <a class="btn btn--ghost" href="<?php echo esc_url(home_url('/contact')); ?>"><?php esc_html_e('Contact', 'locals-realty'); ?></a>
    </div>
</section>

<?php endwhile; get_footer();
