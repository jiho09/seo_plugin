<?php
/**
 * Plugin Name:       WordPress SEO Check Plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Checks SEO elements and site performance for each post/page.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      8.1
 * Author:            Gemini
 * Author URI:        https://gemini.google.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-seo-check
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin file constant for consistent path resolution
if ( ! defined( 'WPSEOCHK_PLUGIN_FILE' ) ) {
    define( 'WPSEOCHK_PLUGIN_FILE', __FILE__ );
}

// Require the autoloader
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate_plugin_seo_check() {
    Plugin_SEO_Check\Core\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_plugin_seo_check() {
    Plugin_SEO_Check\Core\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_seo_check' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_seo_check' );

/**
 * Begins execution of the plugin.
 */
function run_plugin_seo_check() {
    $plugin = new Plugin_SEO_Check\Core\Loader();
    $plugin->run();
}

run_plugin_seo_check();
