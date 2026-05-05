<?php
/**
 * Landing page.
 *
 * Sections (Figma):
 *  1. Hero (16:9 video) — "Learn from a local." + search.
 *  2. Mission strip — "The right home. The right lifestyle." + state tagline,
 *     paired with secondary image + "build the next version of your life."
 *  3. By State grid (4 cards).
 *  4. Highlighted Properties — town pills + IDX cards + "View all properties".
 *  5. Meet the Team — split block.
 *  6. Join Today — recruitment split block.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$mission        = function_exists('get_field') ? get_field('mission') : '';
$mission        = $mission ?: __('Our mission is simple: combine world-class real estate expertise with innovative marketing that elevates every listing, humanizes every transaction, and builds long-term relationships within the communities we proudly serve.', 'locals-realty');
$hero_video     = function_exists('get_field') ? get_field('hero_video_url') : '';
$hero_fallback  = function_exists('get_field') ? get_field('hero_fallback') : null;
$hero_poster    = locals_image_url($hero_fallback, 'default-landing-hero.jpg', 'locals-hero');
$default_video  = LOCALS_REALTY_DIR . '/assets/images/default-hero-video.mp4';
$default_video_url = file_exists($default_video) ? LOCALS_REALTY_URI . '/assets/images/default-hero-video.mp4' : '';
$hero_video     = $hero_video ?: $default_video_url;

$states          = get_posts(['post_type' => 'state', 'posts_per_page' => 4, 'orderby' => 'menu_order', 'order' => 'ASC']);
$state_tagline   = $states
    ? implode(' ', array_map(fn($s) => esc_html(get_the_title($s)), $states))
    : 'Florida North Carolina South Carolina Tennessee';

$highlight_towns = function_exists('get_field') ? (get_field('highlight_towns') ?: []) : [];

// Resolve highlight pills to {label, city, state} so they can drive both the
// listings filter and the displayed text. Falls back to a hard-coded list
// if no ACF "Highlighted towns" are set yet.
$pills = [];
if ($highlight_towns) {
    foreach ($highlight_towns as $t) {
        $tid       = is_object($t) ? $t->ID : (int) $t;
        $title     = get_the_title($tid);
        $state_pid = function_exists('get_field') ? get_field('state', $tid) : null;
        $state_abbr = '';
        if ($state_pid) {
            $sp_id = is_object($state_pid) ? $state_pid->ID : (int) $state_pid;
            $state_abbr = function_exists('get_field') ? (get_field('abbreviation', $sp_id) ?: '') : '';
            if (!$state_abbr) {
                // crude fallback: take first two letters of state title.
                $state_abbr = strtoupper(substr(get_the_title($sp_id), 0, 2));
            }
        }
        $pills[] = [
            'label' => $title . ($state_abbr ? ', ' . $state_abbr : ''),
            'city'  => $title,
            'state' => $state_abbr,
        ];
    }
} else {
    $pills = [
        ['label' => 'Jupiter, FL',     'city' => 'Jupiter',    'state' => 'FL'],
        ['label' => 'Charleston, SC',  'city' => 'Charleston', 'state' => 'SC'],
        ['label' => 'Nashville, TN',   'city' => 'Nashville',  'state' => 'TN'],
        ['label' => 'Asheville, NC',   'city' => 'Asheville',  'state' => 'NC'],
        ['label' => 'Miami, FL',       'city' => 'Miami',      'state' => 'FL'],
    ];
}
?>

<section class="hero hero--landing" data-hero>
    <div class="hero__media">
        <?php if ($hero_video) : ?>
            <video class="hero__video" autoplay muted loop playsinline poster="<?php echo esc_url($hero_poster); ?>">
                <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
            </video>
        <?php elseif ($hero_poster) : ?>
            <img class="hero__img" src="<?php echo esc_url($hero_poster); ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="hero__content">
        <div class="hero__overlay-logo">
            <span style="font-size:0.7rem;letter-spacing:0.1em">The</span>
            <strong>locals <span style="font-size:0.55em;font-weight:400">Group</span></strong>
            <strong style="margin-top:0.1em">lpt <span style="font-size:0.85em;font-weight:400">realty</span></strong>
            <small>Brokerage for life&trade;</small>
        </div>
        <h1 class="hero__title">Learn from<br>a local.</h1>
    </div>
</section>

<section class="mission container">
    <div class="mission__main">
        <h2 class="mission__title">The right home.<br>The right lifestyle.</h2>
        <p class="mission__copy"><?php echo esc_html($mission); ?></p>
        <ul class="mission__states">
            <?php foreach ($states as $s) : ?>
                <li><?php echo esc_html(get_the_title($s)); ?></li>
            <?php endforeach; ?>
            <?php if (empty($states)) : ?>
                <li>Florida</li><li>North Carolina</li><li>South Carolina</li><li>Tennessee</li>
            <?php endif; ?>
        </ul>
    </div>
    <aside class="mission__aside">
        <?php
        $tailored      = locals_lofty_tailored_listing();
        $tailored_img  = $tailored['photo']  ?? '';
        $tailored_link = $tailored['permalink'] ?? '';
        $tailored_addr = $tailored['address']    ?? '';
        $tailored_loc  = trim(implode(', ', array_filter([$tailored['city'] ?? '', $tailored['state'] ?? ''])));
        $tailored_price = isset($tailored['price']) ? locals_format_price($tailored['price']) : '';
        $fallback_img  = locals_image_url(null, 'mission-aside.jpg');

        if ($tailored_img || $fallback_img) : ?>
            <a class="mission__property"
               href="<?php echo esc_url($tailored_link ?: '#'); ?>"
               <?php echo $tailored_link ? 'target="_blank" rel="noopener"' : ''; ?>>
                <img src="<?php echo esc_url($tailored_img ?: $fallback_img); ?>" alt="<?php echo esc_attr($tailored_addr); ?>">
                <p class="mission__caption">
                    <span class="mission__caption-prefix">build the</span>
                    next version of<br>your life.
                </p>
                <?php if ($tailored_addr) : ?>
                    <div class="mission__property-meta">
                        <span class="mission__property-addr"><?php echo esc_html($tailored_addr); ?></span>
                        <?php if ($tailored_price) : ?><span class="mission__property-price"><?php echo esc_html($tailored_price); ?></span><?php endif; ?>
                        <?php if ($tailored_loc)   : ?><span class="mission__property-loc"><?php echo esc_html($tailored_loc); ?></span><?php endif; ?>
                    </div>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </aside>
</section>

<section class="landing-search container">
    <form class="landing-search__bar" action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
        <label class="visually-hidden" for="landing-search">Search properties</label>
        <input id="landing-search" name="q" type="search" placeholder="Search by address, location, agent...">
        <button type="submit" aria-label="Search">&rarr;</button>
    </form>
</section>

<section class="states container">
    <h2 class="section-title">By state</h2>
    <ul class="states__grid">
        <?php foreach ($states as $state) :
            $slug = sanitize_title(get_the_title($state));
            $img  = locals_thumbnail_url($state, "state-card-{$slug}.jpg", 'locals-town')
                 ?: locals_thumbnail_url(0, 'state-card-default.jpg', 'locals-town');
        ?>
            <li class="states__item">
                <a href="<?php echo esc_url(get_permalink($state)); ?>">
                    <?php if ($img) : ?>
                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($state)); ?>">
                    <?php endif; ?>
                    <span class="states__label"><?php echo esc_html(get_the_title($state)); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="highlights container">
    <h2 class="section-title">Highlighted properties</h2>
    <ul class="highlights__pills" data-listings-filters>
        <?php foreach ($pills as $i => $p) :
            $filter = ['city' => $p['city'], 'state' => $p['state'], 'scope' => 'office', 'limit' => 6];
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
    <div class="highlights__cards" data-listings-grid aria-live="polite" aria-busy="false">
        <?php
        // Initial render uses the first pill's filter so the page loads with content.
        $initial = ['city' => $pills[0]['city'], 'state' => $pills[0]['state'], 'scope' => 'office', 'limit' => 6];
        locals_render_listings(
            $initial,
            __('Featured listings will populate here once Lofty returns matching active listings.', 'locals-realty')
        );
        ?>
    </div>
    <a class="highlights__view-all" href="<?php echo esc_url(home_url('/search')); ?>">
        <?php esc_html_e('View all properties', 'locals-realty'); ?> &rarr;
    </a>
</section>

<section class="container split">
    <?php
    $team_page = get_page_by_path('about');
    $team_url  = $team_page ? locals_thumbnail_url($team_page, 'team.jpg', 'locals-card') : locals_image_url(null, 'team.jpg');
    ?>
    <div class="split__media">
        <?php if ($team_url) : ?><img src="<?php echo esc_url($team_url); ?>" alt=""><?php endif; ?>
    </div>
    <div class="split__body">
        <h2>Meet the Team</h2>
        <p><?php echo esc_html($mission); ?></p>
        <a class="btn btn--ghost" href="<?php echo esc_url(home_url('/about')); ?>"><?php esc_html_e('Learn More', 'locals-realty'); ?></a>
    </div>
</section>

<section class="container split split--reverse">
    <div class="split__media">
        <?php $join_url = locals_image_url(null, 'join.jpg'); ?>
        <?php if ($join_url) : ?><img src="<?php echo esc_url($join_url); ?>" alt=""><?php endif; ?>
    </div>
    <div class="split__body">
        <h2>Join Today &mdash; <em>become a local.</em></h2>
        <p><?php echo esc_html($mission); ?></p>
        <a class="btn btn--ghost" href="<?php echo esc_url(home_url('/join')); ?>"><?php esc_html_e('Learn More', 'locals-realty'); ?></a>
    </div>
</section>

<?php get_footer();
