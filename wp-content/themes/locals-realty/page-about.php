<?php
/**
 * Template Name: About
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post(); ?>
    <article class="about container">
        <header class="about__header">
            <h1><?php the_title(); ?></h1>
        </header>
        <div class="about__content"><?php the_content(); ?></div>

        <section class="about__team">
            <h2><?php esc_html_e('Meet the Team', 'locals-realty'); ?></h2>
            <ul class="agents-grid">
                <?php
                $agents = get_posts(['post_type' => 'agent', 'posts_per_page' => -1]);
                foreach ($agents as $a) :
                    $role = get_field('role', $a->ID);
                ?>
                    <li class="agents-grid__item">
                        <a href="<?php echo esc_url(get_permalink($a)); ?>">
                            <?php echo get_the_post_thumbnail($a, 'locals-card'); ?>
                            <h3><?php echo esc_html(get_the_title($a)); ?></h3>
                            <?php if ($role) : ?><p><?php echo esc_html($role); ?></p><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </article>
<?php endwhile; get_footer();
