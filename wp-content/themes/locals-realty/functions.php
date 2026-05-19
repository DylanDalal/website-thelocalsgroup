<?php
/**
 * Locals Realty theme bootstrap.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LOCALS_REALTY_VERSION', '0.1.0');
define('LOCALS_REALTY_DIR', get_stylesheet_directory());
define('LOCALS_REALTY_URI', get_stylesheet_directory_uri());

require_once LOCALS_REALTY_DIR . '/inc/setup.php';
require_once LOCALS_REALTY_DIR . '/inc/enqueue.php';
require_once LOCALS_REALTY_DIR . '/inc/post-types.php';
require_once LOCALS_REALTY_DIR . '/inc/acf.php';
require_once LOCALS_REALTY_DIR . '/inc/state-map.php';
require_once LOCALS_REALTY_DIR . '/inc/lofty-idx.php';
require_once LOCALS_REALTY_DIR . '/inc/forms.php';
