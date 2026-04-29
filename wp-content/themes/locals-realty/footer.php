<?php if (!defined('ABSPATH')) { exit; } ?>
</main>

<footer class="site-footer">
    <div class="site-footer__inner">
        <small class="site-footer__copy">
            &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>
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
                        . '<li><a href="' . esc_url(home_url('/contact')) . '">Contact</a></li>'
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
