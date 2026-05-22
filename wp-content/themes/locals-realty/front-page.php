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
        <h1 class="hero__title" data-hero-title>Learn from<br>a local.</h1>
    </div>
    <form class="hero__search hero__search--floating"
          data-hero-search
          action="<?php echo esc_url(home_url('/search')); ?>"
          method="get"
          role="search">
        <label class="visually-hidden" for="hero-search-input"><?php esc_html_e('Search properties', 'locals-realty'); ?></label>
        <input id="hero-search-input" name="q" type="search"
               placeholder="<?php esc_attr_e('Search by address, town, agent...', 'locals-realty'); ?>">
        <button type="submit" aria-label="<?php esc_attr_e('Search', 'locals-realty'); ?>">&rarr;</button>
    </form>
</section>

<?php
// Mission scroll-reveal: two-beat curtain that plays before the existing
// mission grid. Beat 1 is "Home." in solid type on white; beat 2 is
// "Lifestyle." in white type with mix-blend-difference over a full-bleed
// southern-town photo. Wired via inline style so the image can move into
// ACF (mission_reveal_image) without touching JS or CSS.
$reveal_life_img = function_exists('get_field') ? get_field('mission_reveal_image') : '';
// High-res Savannah, GA — oak-canopied southern road (Jacob Mathers, 3857x2169).
$reveal_life_img = is_array($reveal_life_img) && !empty($reveal_life_img['url'])
    ? $reveal_life_img['url']
    : ($reveal_life_img ?: 'https://images.unsplash.com/photo-1623184185917-d2e8ec0daa27?w=2400&q=85&auto=format&fit=crop');
?>
<section class="mission-reveal" data-mission-reveal aria-hidden="true">
    <div class="mission-reveal__pin">
        <div class="mission-reveal__beat mission-reveal__beat--home">
            <p class="mission-reveal__kicker">The right</p>
            <h2 class="mission-reveal__big">Home.</h2>
        </div>
        <div class="mission-reveal__beat mission-reveal__beat--life"
             style="background-image:url('<?php echo esc_url($reveal_life_img); ?>');">
            <div class="mission-reveal__beat-veil" aria-hidden="true"></div>
            <p class="mission-reveal__kicker mission-reveal__kicker--light">The right</p>
            <h2 class="mission-reveal__big mission-reveal__big--knockout">Lifestyle.</h2>
        </div>
        <div class="mission-reveal__caret" aria-hidden="true">scroll</div>
    </div>
</section>

<section class="mission container" data-reveal>
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

<section class="landing-search container" data-reveal>
    <form class="landing-search__bar" action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
        <label class="visually-hidden" for="landing-search">Search properties</label>
        <input id="landing-search" name="q" type="search" placeholder="Search by address, location, agent...">
        <button type="submit" aria-label="Search">&rarr;</button>
    </form>
</section>

<section class="states container">
    <h2 class="section-title">By state</h2>
    <ul class="states__grid" data-reveal data-reveal-stagger="0.09">
        <?php foreach ($states as $state) :
            $slug = sanitize_title(get_the_title($state));
            $img  = locals_thumbnail_url($state, "state-card-{$slug}.jpg", 'locals-town')
                 ?: locals_thumbnail_url(0, 'state-card-default.jpg', 'locals-town');
        ?>
            <li class="states__item">
                <a href="<?php echo esc_url(get_permalink($state)); ?>">
                    <?php if ($img) : ?>
                        <img src="<?php echo esc_url($img); ?>"
                             alt="<?php echo esc_attr(get_the_title($state)); ?>"
                             style="view-transition-name: state-photo-<?php echo esc_attr($slug); ?>;">
                    <?php endif; ?>
                    <span class="states__label"
                          style="view-transition-name: state-label-<?php echo esc_attr($slug); ?>;"><?php echo esc_html(get_the_title($state)); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php
// Probe each pill's filter and drop ones that return no active listings.
// locals_lofty_listings() caches each filter in a 15-min transient, and the
// initial render below reuses the same filter shape so it's a cache hit.
$pills = array_values(array_filter($pills, function ($p) {
    $filter = ['city' => $p['city'], 'state' => $p['state'], 'scope' => 'office', 'limit' => 9];
    return !empty(locals_lofty_listings($filter));
}));
?>
<?php if ($pills) : ?>
<section class="highlights container" data-reveal>
    <h2 class="section-title">Highlighted properties</h2>
    <ul class="highlights__pills" data-listings-filters>
        <?php foreach ($pills as $i => $p) :
            $filter = ['city' => $p['city'], 'state' => $p['state'], 'scope' => 'office', 'limit' => 9];
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
            $initial = ['city' => $pills[0]['city'], 'state' => $pills[0]['state'], 'scope' => 'office', 'limit' => 9];
            locals_render_listings(
                $initial,
                __('Featured listings will populate here once Lofty returns matching active listings.', 'locals-realty')
            );
            ?>
        </div>
        <button type="button" class="highlights__nav highlights__nav--next" data-carousel-next
                aria-label="<?php esc_attr_e('Next properties', 'locals-realty'); ?>" hidden>
            <span aria-hidden="true">&rarr;</span>
        </button>
    </div>
    <div class="highlights__view-all-row">
        <a class="highlights__view-all" href="<?php echo esc_url(home_url('/search')); ?>">
            <?php esc_html_e('View all properties', 'locals-realty'); ?> &rarr;
        </a>
    </div>
</section>
<?php endif; ?>

<?php
$lifestyle_eyebrow = function_exists('get_field') ? get_field('lifestyle_eyebrow') : '';
$lifestyle_title   = function_exists('get_field') ? get_field('lifestyle_title')   : '';
$lifestyle_body    = function_exists('get_field') ? get_field('lifestyle_body')    : '';
$lifestyle_image   = function_exists('get_field') ? get_field('lifestyle_image')   : null;
$lifestyle_eyebrow = $lifestyle_eyebrow ?: __('Lifestyle realty.', 'locals-realty');
$lifestyle_title   = $lifestyle_title   ?: __('Let\'s build your new life.', 'locals-realty');
$lifestyle_body    = $lifestyle_body    ?: __('A move is more than a transaction — it\'s a chance to step into a different rhythm. We listen for what you\'re actually after (slower mornings, water access, a school, room to breathe) and pair you with the town, neighborhood, and home that fits the life you\'re reaching toward.', 'locals-realty');
$lifestyle_img_url = locals_image_url($lifestyle_image, 'lifestyle.jpg', 'locals-card');
// Fall back to a cinematic small-town dusk shot until an ACF image is set.
// High-res Charleston, SC street (Alexander Wark Feeney, 5184x3456).
$lifestyle_bg_url  = $lifestyle_img_url
    ?: 'https://images.unsplash.com/photo-1642534683740-2334ba785e3e?w=2400&q=85&auto=format&fit=crop';
?>
<section class="lifestyle-stack" data-lifestyle-stack>
    <div class="lifestyle-stack__media" aria-hidden="true">
        <img src="<?php echo esc_url($lifestyle_bg_url); ?>" alt="" loading="lazy">
    </div>
    <h2 class="lifestyle-stack__title" aria-label="<?php echo esc_attr($lifestyle_title); ?>">
        <span class="lifestyle-stack__line" style="--i:0;--dir:-1;">Let's</span>
        <span class="lifestyle-stack__line" style="--i:1;--dir:1;">build</span>
        <span class="lifestyle-stack__line" style="--i:2;--dir:-1;">your</span>
        <span class="lifestyle-stack__line" style="--i:3;--dir:1;">new</span>
        <span class="lifestyle-stack__line" style="--i:4;--dir:-1;">life.</span>
    </h2>
    <div class="lifestyle-stack__cta">
        <p class="lifestyle-stack__eyebrow"><?php echo esc_html($lifestyle_eyebrow); ?></p>
        <p class="lifestyle-stack__copy"><?php echo esc_html($lifestyle_body); ?></p>
        <a class="btn lifestyle-stack__btn" href="<?php echo esc_url(home_url('/about')); ?>">
            <?php esc_html_e('Start the conversation', 'locals-realty'); ?> &rarr;
        </a>
    </div>
</section>

<section class="container split" data-reveal>
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

<section class="container split split--reverse" data-reveal>
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
