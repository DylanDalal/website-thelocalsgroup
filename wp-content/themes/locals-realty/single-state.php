<?php
/**
 * State page (single state CPT).
 *
 * Sections:
 *  1. State hero with name + IDX search bar.
 *  2. "Our favorites" town list with detail card.
 *  3. Lifestyle hero + lifestyle pill filter.
 *  4. Lifestyle content block (currently "Small Towns" demo).
 *  5. "We're here to help" CTA.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();
    $hero       = get_field('hero_image');
    $idx_query  = get_field('idx_default_query');
    $life_hero  = get_field('lifestyle_hero');
    $life_tag   = get_field('lifestyle_tagline');
    $towns      = get_posts([
        'post_type'      => 'town',
        'posts_per_page' => 12,
        'tax_query'      => [[
            'taxonomy' => 'state_region',
            'field'    => 'slug',
            'terms'    => sanitize_title(get_the_title()),
        ]],
    ]);
    $lifestyles = get_terms(['taxonomy' => 'lifestyle', 'hide_empty' => false]);
?>

<section class="hero hero--state">
    <?php if ($hero) : ?>
        <img class="hero__img" src="<?php echo esc_url($hero['sizes']['locals-hero'] ?? $hero['url']); ?>" alt="">
    <?php endif; ?>
    <div class="hero__content">
        <h1 class="hero__title hero__title--state"><?php the_title(); ?></h1>
        <form class="hero__search" action="<?php echo esc_url(home_url('/search')); ?>" method="get">
            <input type="hidden" name="state" value="<?php echo esc_attr(get_the_title()); ?>">
            <input name="q" type="search" placeholder="<?php esc_attr_e('Search by town, address, agent…', 'locals-realty'); ?>">
            <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
        </form>
    </div>
</section>

<section class="favorites container">
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
                $first = $towns[0];
                $blurb = get_field('blurb', $first->ID);
            ?>
                <?php echo get_the_post_thumbnail($first, 'locals-card'); ?>
                <p><?php echo esc_html($blurb); ?></p>
                <a href="<?php echo esc_url(get_permalink($first)); ?>">
                    <?php printf(esc_html__('View properties in %s', 'locals-realty'), esc_html(get_the_title($first))); ?> &rarr;
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="hero hero--lifestyle">
    <?php if ($life_hero) : ?>
        <img class="hero__img" src="<?php echo esc_url($life_hero['sizes']['locals-hero'] ?? $life_hero['url']); ?>" alt="">
    <?php endif; ?>
    <div class="hero__content">
        <h2 class="hero__title">Lifestyle realty.</h2>
        <p><?php echo esc_html($life_tag ?: __('We want to help you reach your new lifestyle.', 'locals-realty')); ?></p>
    </div>
</section>

<section class="lifestyles container">
    <ul class="lifestyles__pills" data-lifestyles>
        <?php foreach ($lifestyles as $i => $term) : ?>
            <li class="<?php echo $i === 0 ? 'is-active' : ''; ?>">
                <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="lifestyles__content">
        <?php // populated by JS or template-part swap based on selected pill ?>
    </div>
</section>

<section class="cta container">
    <div class="cta__body">
        <h2>We're here to help.</h2>
        <p><?php esc_html_e('A locally rooted team across Florida, Tennessee, the Carolinas, and beyond. Reach out and we will match you with a local.', 'locals-realty'); ?></p>
        <a class="btn" href="<?php echo esc_url(home_url('/contact')); ?>"><?php esc_html_e('Contact', 'locals-realty'); ?></a>
    </div>
</section>

<?php endwhile; get_footer();
