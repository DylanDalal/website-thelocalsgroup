<?php if (!defined('ABSPATH')) { exit; } ?>
</main>

<footer class="site-footer">
    <div class="site-footer__top">
        <div class="site-footer__brand">
            <a class="site-footer__brand-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <?php if (has_custom_logo()) {
                    the_custom_logo();
                } else { ?>
                    <span class="site-header__wordmark" aria-hidden="true">lpt</span>
                    <span class="site-footer__brand-text"><?php bloginfo('name'); ?></span>
                <?php } ?>
            </a>
            <p class="site-footer__tag"><?php esc_html_e('Brokerage for life™ — lifestyle real estate across FL · NC · SC · TN · ME · PA · TX.', 'locals-realty'); ?></p>
        </div>

        <nav class="site-footer__col" aria-label="<?php esc_attr_e('Explore', 'locals-realty'); ?>">
            <h3 class="site-footer__heading"><?php esc_html_e('Explore', 'locals-realty'); ?></h3>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/search')); ?>"><?php esc_html_e('Search', 'locals-realty'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/new-listings')); ?>"><?php esc_html_e('New listings', 'locals-realty'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/buy')); ?>"><?php esc_html_e('Buy', 'locals-realty'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/sell')); ?>"><?php esc_html_e('Sell', 'locals-realty'); ?></a></li>
            </ul>
        </nav>

        <nav class="site-footer__col" aria-label="<?php esc_attr_e('Company', 'locals-realty'); ?>">
            <h3 class="site-footer__heading"><?php esc_html_e('Company', 'locals-realty'); ?></h3>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/about')); ?>"><?php esc_html_e('About', 'locals-realty'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/join')); ?>"><?php esc_html_e('Join', 'locals-realty'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>"><?php esc_html_e('Contact', 'locals-realty'); ?></a></li>
            </ul>
        </nav>

        <div class="site-footer__col site-footer__contact">
            <h3 class="site-footer__heading"><?php esc_html_e('Contact', 'locals-realty'); ?></h3>
            <?php
            $email = get_theme_mod('locals_contact_email', 'hello@thelocalsgroup.com');
            $phone = get_theme_mod('locals_contact_phone', '');
            ?>
            <ul>
                <?php if ($email) : ?>
                    <li><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
                <?php endif; ?>
                <?php if ($phone) : ?>
                    <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom">
        <small class="site-footer__copy">
            &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'locals-realty'); ?>
        </small>
        <nav class="site-footer__nav" aria-label="<?php esc_attr_e('Footer', 'locals-realty'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container'      => false,
                'menu_class'     => 'footer-nav',
                'fallback_cb'    => function () {
                    echo '<ul class="footer-nav">'
                        . '<li><a href="' . esc_url(home_url('/privacy')) . '">Privacy</a></li>'
                        . '<li><a href="' . esc_url(home_url('/terms')) . '">Terms</a></li>'
                        . '<li><a href="' . esc_url(home_url('/dmca')) . '">DMCA</a></li>'
                        . '</ul>';
                },
            ]);
            ?>
        </nav>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
