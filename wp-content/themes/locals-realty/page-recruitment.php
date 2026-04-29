<?php
/**
 * Template Name: Recruitment ("Join Today / become a local")
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post(); ?>
    <section class="recruitment container">
        <header class="recruitment__header">
            <h1><?php the_title(); ?></h1>
            <p class="recruitment__lede"><em><?php esc_html_e('become a local.', 'locals-realty'); ?></em></p>
        </header>
        <div class="recruitment__content"><?php the_content(); ?></div>

        <form class="recruitment__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <?php wp_nonce_field('locals_join', 'locals_join_nonce'); ?>
            <input type="hidden" name="action" value="locals_join">
            <label>
                <span><?php esc_html_e('Name', 'locals-realty'); ?></span>
                <input type="text" name="name" required>
            </label>
            <label>
                <span><?php esc_html_e('Email', 'locals-realty'); ?></span>
                <input type="email" name="email" required>
            </label>
            <label>
                <span><?php esc_html_e('Where do you sell?', 'locals-realty'); ?></span>
                <input type="text" name="region">
            </label>
            <label>
                <span><?php esc_html_e('Tell us a little about you', 'locals-realty'); ?></span>
                <textarea name="message" rows="4"></textarea>
            </label>
            <button class="btn" type="submit"><?php esc_html_e('Apply', 'locals-realty'); ?></button>
        </form>
    </section>
<?php endwhile; get_footer();
