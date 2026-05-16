<?php
/**
 * Template Name: About
 *
 * Sections: lead hero, mission, leadership, team grid, regions strip, CTA.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();
    $lead         = function_exists('get_field') ? get_field('lead') : '';
    $lead         = $lead ?: __('The Locals Group is a fast-growing, lifestyle-driven real estate team led by William Dailey, serving buyers, sellers, and investors across Florida, Tennessee, Maine, Pennsylvania, South Carolina, and Texas.', 'locals-realty');
    $mission      = function_exists('get_field') ? get_field('mission') : '';
    $mission      = $mission ?: __('Our mission is simple: combine world-class real estate expertise with innovative marketing that elevates every listing, humanizes every transaction, and builds long-term relationships within the communities we proudly serve.', 'locals-realty');
    $hero_image   = function_exists('get_field') ? get_field('about_hero') : null;
    $hero_url     = locals_image_url($hero_image, 'about-hero.jpg', 'locals-hero');
    $leadership   = function_exists('get_field') ? get_field('leadership') : null;
    $lead_id      = is_object($leadership) ? $leadership->ID : (int) $leadership;
    $regions      = ['Florida', 'Tennessee', 'Maine', 'Pennsylvania', 'South Carolina', 'Texas'];
    $custom_regions = function_exists('get_field') ? get_field('regions_served') : '';
    if (is_string($custom_regions) && trim($custom_regions) !== '') {
        $regions = array_filter(array_map('trim', explode(',', $custom_regions)));
    }
    $agents = get_posts(['post_type' => 'agent', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
?>

<section class="about-hero container" data-reveal>
    <div class="about-hero__copy">
        <span class="eyebrow"><?php esc_html_e('About', 'locals-realty'); ?></span>
        <h1 class="about-hero__title"><?php the_title(); ?></h1>
        <p class="about-hero__lead"><?php echo esc_html($lead); ?></p>
    </div>
    <?php if ($hero_url) : ?>
        <div class="about-hero__media">
            <img src="<?php echo esc_url($hero_url); ?>" alt="">
        </div>
    <?php endif; ?>
</section>

<section class="about-mission container" data-reveal>
    <h2 class="about-mission__heading"><?php esc_html_e('Our mission.', 'locals-realty'); ?></h2>
    <p class="about-mission__copy"><?php echo esc_html($mission); ?></p>
    <?php if (get_the_content()) : ?>
        <div class="about-mission__extra"><?php the_content(); ?></div>
    <?php endif; ?>
</section>

<?php if ($lead_id) :
    $lead_role  = function_exists('get_field') ? get_field('role',  $lead_id) : '';
    $lead_email = function_exists('get_field') ? get_field('email', $lead_id) : '';
    $lead_phone = function_exists('get_field') ? get_field('phone', $lead_id) : '';
    $lead_photo = locals_thumbnail_url($lead_id, '', 'locals-card');
?>
<section class="about-leadership container" data-reveal>
    <div class="about-leadership__media">
        <?php if ($lead_photo) : ?>
            <img src="<?php echo esc_url($lead_photo); ?>" alt="<?php echo esc_attr(get_the_title($lead_id)); ?>">
        <?php endif; ?>
    </div>
    <div class="about-leadership__body">
        <span class="eyebrow"><?php esc_html_e('Leadership', 'locals-realty'); ?></span>
        <h2 class="about-leadership__name"><?php echo esc_html(get_the_title($lead_id)); ?></h2>
        <?php if ($lead_role) : ?><p class="about-leadership__role"><?php echo esc_html($lead_role); ?></p><?php endif; ?>
        <div class="about-leadership__bio"><?php echo apply_filters('the_content', get_post_field('post_content', $lead_id)); ?></div>
        <ul class="about-leadership__contact">
            <?php if ($lead_email) : ?><li><a href="mailto:<?php echo esc_attr($lead_email); ?>"><?php echo esc_html($lead_email); ?></a></li><?php endif; ?>
            <?php if ($lead_phone) : ?><li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $lead_phone)); ?>"><?php echo esc_html($lead_phone); ?></a></li><?php endif; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if ($agents) : ?>
<section class="about-team container" data-reveal>
    <header class="about-team__head">
        <h2><?php esc_html_e('Meet the team', 'locals-realty'); ?></h2>
        <p><?php esc_html_e('Local agents in every market we serve — each a specialist in the lifestyle their community offers.', 'locals-realty'); ?></p>
    </header>
    <ul class="agents-grid" data-reveal data-reveal-stagger="0.06">
        <?php foreach ($agents as $a) :
            $role  = function_exists('get_field') ? get_field('role', $a->ID) : '';
            $photo = locals_thumbnail_url($a, '', 'locals-card');
        ?>
            <li class="agent-card">
                <a href="<?php echo esc_url(get_permalink($a)); ?>">
                    <div class="agent-card__photo">
                        <?php if ($photo) : ?>
                            <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr(get_the_title($a)); ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <h3 class="agent-card__name"><?php echo esc_html(get_the_title($a)); ?></h3>
                    <?php if ($role) : ?><p class="agent-card__role"><?php echo esc_html($role); ?></p><?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<section class="about-regions container" data-reveal>
    <h2><?php esc_html_e('Where we work.', 'locals-realty'); ?></h2>
    <ul class="about-regions__list">
        <?php foreach ($regions as $r) : ?>
            <li><?php echo esc_html($r); ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="container split" data-reveal>
    <?php $cta_img = locals_image_url(null, 'about-cta.jpg'); ?>
    <div class="split__media">
        <?php if ($cta_img) : ?><img src="<?php echo esc_url($cta_img); ?>" alt=""><?php endif; ?>
    </div>
    <div class="split__body">
        <h2><?php esc_html_e("Let's find your version of local.", 'locals-realty'); ?></h2>
        <p><?php esc_html_e('Tell us what you love. We will introduce you to the agent who knows it best.', 'locals-realty'); ?></p>
        <a class="btn" href="<?php echo esc_url(home_url('/contact')); ?>"><?php esc_html_e('Contact us', 'locals-realty'); ?></a>
    </div>
</section>

<?php endwhile; get_footer();
