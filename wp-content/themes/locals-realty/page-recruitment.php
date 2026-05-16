<?php
/**
 * Template Name: Recruitment ("Join Today / become a local")
 *
 * Sections: hero, three benefits, image collage, application form.
 * Selling-to-realtors page — emphasize lifestyle marketing, brand polish, and
 * the fact that we serve six states with one team identity.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

while (have_posts()) : the_post();
    $lead         = function_exists('get_field') ? get_field('recruit_lead') : '';
    $lead         = $lead ?: __('Real estate is more than transactions — it is a lifestyle, a community, and a calling. Join a team that elevates every listing and supports every agent with marketing, technology, and brand polish that wins.', 'locals-realty');
    $hero         = function_exists('get_field') ? get_field('recruit_hero') : null;
    $hero_url     = locals_image_url($hero, 'recruit-hero.jpg', 'locals-hero');

    $benefits = [
        [
            'heading' => function_exists('get_field') ? (get_field('benefit_1') ?: __('Lifestyle-first marketing.', 'locals-realty')) : __('Lifestyle-first marketing.', 'locals-realty'),
            'body'    => function_exists('get_field') ? (get_field('benefit_1_body') ?: __('Our in-house creative team produces story-led video, photography, and copy for every listing. You sell the home; we sell the life.', 'locals-realty')) : __('Our in-house creative team produces story-led video, photography, and copy for every listing.', 'locals-realty'),
        ],
        [
            'heading' => function_exists('get_field') ? (get_field('benefit_2') ?: __('One brand. Six states.', 'locals-realty')) : __('One brand. Six states.', 'locals-realty'),
            'body'    => function_exists('get_field') ? (get_field('benefit_2_body') ?: __('Refer across the network. Move with your clients. Build a national reputation while staying local where it matters.', 'locals-realty')) : __('Refer across the network. Build a national reputation while staying local where it matters.', 'locals-realty'),
        ],
        [
            'heading' => function_exists('get_field') ? (get_field('benefit_3') ?: __('Tech that works for you.', 'locals-realty')) : __('Tech that works for you.', 'locals-realty'),
            'body'    => function_exists('get_field') ? (get_field('benefit_3_body') ?: __('Lofty CRM, automated drip campaigns, listing syndication, and lead routing built so you can focus on relationships, not workflow.', 'locals-realty')) : __('Lofty CRM, automated drip, listing syndication, lead routing.', 'locals-realty'),
        ],
    ];

    $success = isset($_GET['joined']) && $_GET['joined'] === '1';
    $failed  = isset($_GET['joined']) && $_GET['joined'] === '0';
?>

<section class="recruit-hero" data-hero>
    <div class="hero__media">
        <?php if ($hero_url) : ?><img class="hero__img" src="<?php echo esc_url($hero_url); ?>" alt=""><?php endif; ?>
    </div>
    <div class="recruit-hero__content">
        <span class="eyebrow eyebrow--light"><?php esc_html_e('Join Today', 'locals-realty'); ?></span>
        <h1 class="recruit-hero__title">
            <?php esc_html_e('Become a', 'locals-realty'); ?><br>
            <em class="recruit-hero__script"><?php esc_html_e('local.', 'locals-realty'); ?></em>
        </h1>
        <p class="recruit-hero__lead"><?php echo esc_html($lead); ?></p>
        <a class="btn btn--light" href="#apply"><?php esc_html_e('Apply now', 'locals-realty'); ?> &rarr;</a>
    </div>
</section>

<section class="recruit-benefits container" data-reveal data-reveal-stagger="0.12">
    <?php foreach ($benefits as $b) : ?>
        <article class="recruit-benefit">
            <h2 class="recruit-benefit__heading"><?php echo esc_html($b['heading']); ?></h2>
            <p class="recruit-benefit__body"><?php echo esc_html($b['body']); ?></p>
        </article>
    <?php endforeach; ?>
</section>

<section class="recruit-collage container" data-reveal>
    <?php
    $collage = [];
    for ($i = 1; $i <= 4; $i++) {
        $u = locals_image_url(null, "recruit-collage-{$i}.jpg");
        if ($u) { $collage[] = $u; }
    }
    if (count($collage) >= 3) : ?>
        <div class="recruit-collage__grid">
            <?php foreach ($collage as $i => $u) : ?>
                <figure class="recruit-collage__cell recruit-collage__cell--<?php echo (int) ($i + 1); ?>">
                    <img src="<?php echo esc_url($u); ?>" alt="" loading="lazy">
                </figure>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <blockquote class="recruit-quote">
            <p><?php esc_html_e('"We do not chase deals. We build careers, one community at a time."', 'locals-realty'); ?></p>
            <cite><?php esc_html_e('— William Dailey, Founder', 'locals-realty'); ?></cite>
        </blockquote>
    <?php endif; ?>
</section>

<section id="apply" class="recruit-apply container" data-reveal>
    <div class="recruit-apply__intro">
        <h2><?php esc_html_e('Apply to join.', 'locals-realty'); ?></h2>
        <p><?php esc_html_e('Tell us about yourself and where you sell. We respond within two business days.', 'locals-realty'); ?></p>
    </div>

    <?php if ($success) : ?>
        <p class="recruit-apply__success" role="status">
            <?php esc_html_e('Thank you — we received your application and will be in touch shortly.', 'locals-realty'); ?>
        </p>
    <?php endif; ?>
    <?php if ($failed) : ?>
        <p class="recruit-apply__error" role="alert">
            <?php esc_html_e('Something went wrong with your submission. Please email us directly.', 'locals-realty'); ?>
        </p>
    <?php endif; ?>

    <form class="recruitment__form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <?php wp_nonce_field('locals_join', 'locals_join_nonce'); ?>
        <input type="hidden" name="action" value="locals_join">
        <input type="text" name="company" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <label>
            <span><?php esc_html_e('Name', 'locals-realty'); ?></span>
            <input type="text" name="name" required>
        </label>
        <label>
            <span><?php esc_html_e('Email', 'locals-realty'); ?></span>
            <input type="email" name="email" required>
        </label>
        <label>
            <span><?php esc_html_e('Phone (optional)', 'locals-realty'); ?></span>
            <input type="tel" name="phone">
        </label>
        <label>
            <span><?php esc_html_e('Where do you sell?', 'locals-realty'); ?></span>
            <input type="text" name="region" placeholder="<?php esc_attr_e('e.g. Jupiter, FL — Treasure Coast', 'locals-realty'); ?>">
        </label>
        <label>
            <span><?php esc_html_e('Years licensed', 'locals-realty'); ?></span>
            <input type="text" name="experience" placeholder="<?php esc_attr_e('e.g. 5', 'locals-realty'); ?>">
        </label>
        <label>
            <span><?php esc_html_e('Tell us a little about you', 'locals-realty'); ?></span>
            <textarea name="message" rows="4"></textarea>
        </label>
        <button class="btn" type="submit"><?php esc_html_e('Apply', 'locals-realty'); ?></button>
    </form>
</section>

<?php endwhile; get_footer();
