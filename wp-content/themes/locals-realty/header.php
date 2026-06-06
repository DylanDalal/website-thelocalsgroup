<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#357976">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<?php
$page_template = is_page() ? get_page_template_slug() : '';
$dark_hero_templates = ['page-recruitment.php'];
$has_hero = is_front_page()
    || is_singular('state')
    || in_array($page_template, $dark_hero_templates, true);
?>
<body <?php body_class($has_hero ? 'has-hero' : 'no-hero'); ?>>
<?php wp_body_open(); ?>

<?php
$nav_items = [
    ['url' => home_url('/new-listings'), 'label' => __('New Listings', 'locals-realty')],
    ['url' => home_url('/buy'),          'label' => __('Buy', 'locals-realty')],
    ['url' => home_url('/sell'),         'label' => __('Sell', 'locals-realty')],
    ['url' => home_url('/about'),        'label' => __('About', 'locals-realty')],
    ['url' => home_url('/join'),         'label' => __('Join', 'locals-realty')],
];
?>
<header class="site-header site-header--solid" data-site-header>
    <div class="site-header__inner">
        <a class="site-header__brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <?php if (has_custom_logo()) {
                the_custom_logo();
            } else { ?>
                <span class="site-header__wordmark" aria-hidden="true">lpt</span>
                <span class="site-header__brand-text"><?php bloginfo('name'); ?></span>
            <?php } ?>
        </a>
        <nav class="site-header__nav" aria-label="<?php esc_attr_e('Primary', 'locals-realty'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'site-nav',
                'fallback_cb'    => function () use ($nav_items) {
                    echo '<ul class="site-nav">';
                    foreach ($nav_items as $item) {
                        printf('<li><a href="%s">%s</a></li>', esc_url($item['url']), esc_html($item['label']));
                    }
                    echo '</ul>';
                },
            ]);
            ?>
        </nav>
        <form class="site-header__search" data-header-search action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
            <label class="visually-hidden" for="header-search-input"><?php esc_html_e('Search properties', 'locals-realty'); ?></label>
            <input id="header-search-input" name="q" type="search" placeholder="<?php esc_attr_e('Search by town, address, agent...', 'locals-realty'); ?>">
            <button type="submit" aria-label="<?php esc_attr_e('Search', 'locals-realty'); ?>">&rarr;</button>
        </form>
        <button class="site-header__toggle" type="button"
                aria-expanded="false"
                aria-controls="site-drawer"
                aria-label="<?php esc_attr_e('Toggle menu', 'locals-realty'); ?>"
                data-nav-toggle>
            <span></span>
        </button>
    </div>
</header>
<aside id="site-drawer" class="site-drawer" data-nav-drawer aria-hidden="true">
    <form class="site-drawer__search" action="<?php echo esc_url(home_url('/search')); ?>" method="get" role="search">
        <label class="visually-hidden" for="drawer-search-input"><?php esc_html_e('Search properties', 'locals-realty'); ?></label>
        <input id="drawer-search-input" name="q" type="search" placeholder="<?php esc_attr_e('Search by town, address, agent...', 'locals-realty'); ?>">
        <button type="submit" aria-label="<?php esc_attr_e('Search', 'locals-realty'); ?>">&rarr;</button>
    </form>
    <ul>
        <?php foreach ($nav_items as $item) : ?>
            <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
        <?php endforeach; ?>
    </ul>
</aside>

<main id="content" class="site-main">
