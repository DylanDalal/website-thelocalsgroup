<?php
/**
 * Landing page.
 *
 * Sections (per Figma):
 *  1. Hero — "Learn from a local." over scroll-up bg, with search → Search page.
 *  2. Mission strip — "The right home. The right lifestyle." + tagline image.
 *  3. By State — 4 cards (FL / NC / SC / TN) → state pages.
 *  4. Highlighted Properties — town pills + IDX cards + "View all properties".
 *  5. Meet the Team — preview block → /about.
 *  6. Join Today — recruitment block → /join.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$mission       = function_exists('get_field') ? get_field('mission') : '';
$hero_video    = function_exists('get_field') ? get_field('hero_video_url') : '';
$hero_fallback = function_exists('get_field') ? get_field('hero_fallback') : null;
$states        = get_posts(['post_type' => 'state', 'posts_per_page' => 4, 'orderby' => 'menu_order', 'order' => 'ASC']);
$highlight_towns = function_exists('get_field') ? (get_field('highlight_towns') ?: []) : [];
?>

<section class="hero hero--landing" data-hero>
    <div class="hero__media">
        <?php if ($hero_video) : ?>
            <video class="hero__video" autoplay muted loop playsinline poster="<?php echo $hero_fallback ? esc_url($hero_fallback['url']) : ''; ?>">
                <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
            </video>
        <?php elseif ($hero_fallback) : ?>
            <img class="hero__img" src="<?php echo esc_url($hero_fallback['url']); ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="hero__content">
        <h1 class="hero__title">Learn from a local.</h1>
        <form class="hero__search" action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
            <label class="visually-hidden" for="hero-search"><?php esc_html_e('Search properties', 'locals-realty'); ?></label>
            <input id="hero-search" name="q" type="search" placeholder="<?php esc_attr_e('Search by address, location, agent…', 'locals-realty'); ?>">
            <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
        </form>
    </div>
</section>

<section class="mission container">
    <h2 class="mission__title">The right home. The right lifestyle.<br><em>build the next version of your life.</em></h2>
    <?php if ($mission) : ?>
        <p class="mission__copy"><?php echo esc_html($mission); ?></p>
    <?php endif; ?>
</section>

<section class="states container">
    <h2 class="section-title">By state</h2>
    <ul class="states__grid">
        <?php foreach ($states as $state) : ?>
            <li class="states__item">
                <a href="<?php echo esc_url(get_permalink($state)); ?>">
                    <?php echo get_the_post_thumbnail($state, 'locals-town'); ?>
                    <span class="states__label"><?php echo esc_html(get_the_title($state)); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="highlights container">
    <h2 class="section-title">Highlighted properties</h2>
    <ul class="highlights__pills">
        <?php foreach ($highlight_towns as $town) :
            $tid = is_object($town) ? $town->ID : (int) $town; ?>
            <li><a href="<?php echo esc_url(get_permalink($tid)); ?>"><?php echo esc_html(get_the_title($tid)); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <div class="highlights__cards">
        <?php
        // Replace with Lofty IDX widget once configured.
        echo do_shortcode('[lofty_search height="500"]');
        ?>
    </div>
    <a class="highlights__view-all" href="<?php echo esc_url(home_url('/search')); ?>">
        <?php esc_html_e('View all properties', 'locals-realty'); ?> &rarr;
    </a>
</section>

<section class="team container">
    <?php
    $team_page = get_page_by_path('about');
    if ($team_page) :
        $team_thumb = get_the_post_thumbnail($team_page, 'locals-card');
    endif;
    ?>
    <div class="team__media"><?php echo $team_thumb ?? ''; ?></div>
    <div class="team__body">
        <h2>Meet the Team</h2>
        <p><?php echo esc_html(get_the_excerpt($team_page)); ?></p>
        <a class="btn" href="<?php echo esc_url(home_url('/about')); ?>"><?php esc_html_e('Learn More', 'locals-realty'); ?></a>
    </div>
</section>

<section class="join container">
    <div class="join__body">
        <h2>Join Today &mdash; <em>become a local.</em></h2>
        <p><?php esc_html_e('The Locals Group is a fast-growing, lifestyle-driven real estate team. If you build long-term relationships within your community, we want to talk.', 'locals-realty'); ?></p>
        <a class="btn" href="<?php echo esc_url(home_url('/join')); ?>"><?php esc_html_e('Learn More', 'locals-realty'); ?></a>
    </div>
</section>

<?php get_footer();
