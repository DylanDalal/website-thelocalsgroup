<?php
/**
 * Generic page fallback.
 * Used for any page that does not specify a Template Name.
 */
if (!defined('ABSPATH')) { exit; }
get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    <article class="page container" data-reveal>
        <header class="page__header">
            <h1 class="page__title"><?php the_title(); ?></h1>
        </header>
        <div class="page__content"><?php the_content(); ?></div>
    </article>
<?php endwhile; ?>

<?php get_footer();
