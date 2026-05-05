<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$has_hero = is_front_page() || (is_singular('state'));
?>
<header class="site-header <?php echo $has_hero ? '' : 'site-header--solid'; ?>" data-site-header>
    <div class="site-header__inner">
        <a class="site-header__brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <?php if (has_custom_logo()) {
                the_custom_logo();
            } else { ?>
                <span class="site-header__wordmark" aria-label="<?php bloginfo('name'); ?>">lpt</span>
            <?php } ?>
        </a>
        <nav class="site-header__nav" aria-label="<?php esc_attr_e('Primary', 'locals-realty'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'site-nav',
                'fallback_cb'    => function () {
                    echo '<ul class="site-nav">'
                        . '<li><a href="' . esc_url(home_url('/new-listings')) . '">New Listings</a></li>'
                        . '<li><a href="' . esc_url(home_url('/buy')) . '">Buy</a></li>'
                        . '<li><a href="' . esc_url(home_url('/sell')) . '">Sell</a></li>'
                        . '<li><a href="' . esc_url(home_url('/about')) . '">About</a></li>'
                        . '<li><a href="' . esc_url(home_url('/join')) . '">Join</a></li>'
                        . '</ul>';
                },
            ]);
            ?>
        </nav>
    </div>
</header>

<main id="content" class="site-main">
