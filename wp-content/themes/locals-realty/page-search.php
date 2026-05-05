<?php
/**
 * Template Name: Search
 *
 * Hosts Lofty's search widget (rendered into our page DOM, not iframed) and
 * a results grid we render ourselves from the Lofty API so the cards match
 * our brand styling.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$q     = isset($_GET['q'])     ? sanitize_text_field(wp_unslash($_GET['q']))     : '';
$state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
$town  = isset($_GET['town'])  ? sanitize_text_field(wp_unslash($_GET['town']))  : '';
?>

<section class="search-page container">
    <h1 style="font-size:clamp(2.5rem, 5vw, 4rem);font-weight:700;margin:0 0 1.25rem">Search</h1>
    <form class="search-page__bar" method="get">
        <input name="q" type="search" value="<?php echo esc_attr($q); ?>" placeholder="Search by town, address, agent...">
        <?php if ($state) : ?><input type="hidden" name="state" value="<?php echo esc_attr($state); ?>"><?php endif; ?>
        <?php if ($town) :  ?><input type="hidden" name="town"  value="<?php echo esc_attr($town); ?>"><?php endif; ?>
        <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
    </form>

    <div class="search-page__widget">
        <?php
        // Set the Lofty Search widget ID in WP Admin → Appearance → Customize,
        // or hard-code via a wp-config define for now. 84467 is the example ID
        // Lofty supplied; replace with the actual Search widget ID.
        $search_widget_id = defined('LOFTY_SEARCH_WIDGET_ID') ? LOFTY_SEARCH_WIDGET_ID : 0;
        if ($search_widget_id) {
            echo locals_lofty_widget($search_widget_id, ['style' => 'width:100%;height:80vh;border:0;display:block']);
        } else {
            echo '<p class="listings__empty">' . esc_html__('Define LOFTY_SEARCH_WIDGET_ID in wp-config.php with the Lofty widget id.', 'locals-realty') . '</p>';
        }
        ?>
    </div>

    <div class="search-page__results">
        <?php
        locals_render_listings(
            array_filter([
                'town'  => $town,
                'state' => $state,
                'limit' => 24,
            ]),
            __('Listings will populate here from Lofty once the API is connected.', 'locals-realty')
        );
        ?>
    </div>
</section>

<?php get_footer();
