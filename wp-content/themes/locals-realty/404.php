<?php
/**
 * 404 page.
 */
if (!defined('ABSPATH')) { exit; }
get_header(); ?>

<section class="error-page container" data-reveal>
    <span class="eyebrow">404</span>
    <h1><?php esc_html_e("This page got lost on the way home.", 'locals-realty'); ?></h1>
    <p><?php esc_html_e("The page you were looking for is not here. Try searching for a property, or head back to the front door.", 'locals-realty'); ?></p>

    <form class="search-page__bar" action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
        <input name="q" type="search" placeholder="<?php esc_attr_e('Search properties...', 'locals-realty'); ?>">
        <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
    </form>

    <p style="margin-top:1.5rem">
        <a class="btn btn--ghost" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back home', 'locals-realty'); ?></a>
    </p>
</section>

<?php get_footer();
