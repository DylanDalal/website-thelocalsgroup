<?php
/**
 * Template Name: Search (Lofty IDX)
 *
 * Renders the Lofty IDX search experience. The default state shows a wide
 * results grid; once the user types, Lofty shifts to its map+list focused
 * view internally — we just hand it the query and let the iframe drive.
 */
if (!defined('ABSPATH')) { exit; }
get_header();

$q     = isset($_GET['q'])     ? sanitize_text_field(wp_unslash($_GET['q']))     : '';
$state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
$town  = isset($_GET['town'])  ? sanitize_text_field(wp_unslash($_GET['town']))  : '';
?>

<section class="search-page container">
    <h1 class="section-title">Search</h1>
    <form class="search-page__bar" method="get">
        <input name="q" type="search" value="<?php echo esc_attr($q); ?>" placeholder="<?php esc_attr_e('Search by town, address, agent…', 'locals-realty'); ?>">
        <?php if ($state) : ?><input type="hidden" name="state" value="<?php echo esc_attr($state); ?>"><?php endif; ?>
        <?php if ($town) :  ?><input type="hidden" name="town"  value="<?php echo esc_attr($town); ?>"><?php endif; ?>
        <button type="submit"><?php esc_html_e('Search', 'locals-realty'); ?></button>
    </form>

    <div class="search-page__idx">
        <?php
        echo locals_lofty_iframe(
            array_filter(['q' => $q, 'state' => $state, 'town' => $town]),
            ['style' => 'width:100%;height:80vh;border:0;']
        );
        ?>
    </div>
</section>

<?php get_footer();
