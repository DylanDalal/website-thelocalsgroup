<?php
/**
 * Fallback template.
 */
if (!defined('ABSPATH')) { exit; }
get_header(); ?>

<section class="container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <h1><?php the_title(); ?></h1>
            <div class="entry-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; else : ?>
        <p><?php esc_html_e('Nothing here yet.', 'locals-realty'); ?></p>
    <?php endif; ?>
</section>

<?php get_footer();
