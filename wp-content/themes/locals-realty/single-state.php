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

<section class="hero hero--state">
    <div class="hero__media">
        <?php if ($hero_url) : ?>
            <img class="hero__img"
                 src="<?php echo esc_url($hero_url); ?>"
                 alt=""
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

<?php if ($state_slug !== 'florida' && !empty($towns)) : ?>
<section class="favorites container" data-reveal>
    <h2 class="section-title">Our favorites:</h2>
    <div class="favorites__layout">
        <ul class="favorites__list" data-favorites-list>
            <?php foreach ($towns as $i => $t) : ?>
                <li class="<?php echo $i === 0 ? 'is-active' : ''; ?>" data-town-id="<?php echo esc_attr($t->ID); ?>">
                    <button type="button"><?php echo esc_html(get_the_title($t)); ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="favorites__detail" data-favorites-detail>
            <?php if (!empty($towns[0])) :
                $first      = $towns[0];
                $blurb      = get_field('blurb', $first->ID);
                $first_img  = locals_thumbnail_url($first, '', 'locals-card');
            ?>
                <?php if ($first_img) : ?><img src="<?php echo esc_url($first_img); ?>" alt=""><?php endif; ?>
                <p><?php echo esc_html($blurb); ?></p>
                <a href="<?php echo esc_url(get_permalink($first)); ?>">
                    <?php printf(esc_html__('View properties in %s', 'locals-realty'), esc_html(get_the_title($first))); ?> &rarr;
                </a>
            <?php endif; ?>
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
    $fl_pills = array_values(array_filter($fl_pill_candidates, function ($p) {
        $filter = ['city' => $p['city'], 'state' => 'FL', 'scope' => 'office', 'limit' => 9];
        return !empty(locals_lofty_listings($filter));
    }));
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

<section class="lifestyles container" data-reveal>
    <ul class="lifestyles__pills" data-lifestyles>
        <?php
        if ($lifestyles && !is_wp_error($lifestyles)) {
            foreach ($lifestyles as $i => $term) {
                $cls = $i === 0 ? 'is-active' : '';
                printf(
                    '<li class="%s"><a href="%s">%s</a></li>%s',
                    esc_attr($cls),
                    esc_url(get_term_link($term)),
                    esc_html($term->name),
                    $i < count($lifestyles) - 1 ? '<li aria-hidden="true">&middot;</li>' : ''
                );
            }
        } else {
            $defaults = ['Coastal Living', 'Small Towns', 'Fishing Focused', 'Theme Parks'];
            foreach ($defaults as $i => $name) {
                $cls = $i === 1 ? 'is-active' : '';
                printf(
                    '<li class="%s">%s</li>%s',
                    esc_attr($cls),
                    esc_html($name),
                    $i < count($defaults) - 1 ? '<li aria-hidden="true">&middot;</li>' : ''
                );
            }
        }
        ?>
    </ul>

    <div class="lifestyles__feature">
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
