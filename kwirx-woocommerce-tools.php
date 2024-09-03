<?php
/**
 * Plugin Name: Kwirx WooCommerce Tools
 * Plugin URI: https://kwirx.com
 * Description: A comprehensive toolkit for WooCommerce store management.
 * Version: 1.1.0
 * Author: Kwirx Creative
 * Author URI: https://kwirx.com
 * Text Domain: kwirx-woocommerce-tools
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 6.0
 *
 * @package Kwirx_WooCommerce_Tools
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('KWIRX_TOOLS_VERSION', '1.0.0');
define('KWIRX_TOOLS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KWIRX_TOOLS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_kwirx_woocommerce_tools() {
    require_once KWIRX_TOOLS_PLUGIN_DIR . 'includes/class-kwirx-woocommerce-tools-activator.php';
    Kwirx_WooCommerce_Tools_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_kwirx_woocommerce_tools() {
    require_once KWIRX_TOOLS_PLUGIN_DIR . 'includes/class-kwirx-woocommerce-tools-deactivator.php';
    Kwirx_WooCommerce_Tools_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_kwirx_woocommerce_tools');
register_deactivation_hook(__FILE__, 'deactivate_kwirx_woocommerce_tools');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require KWIRX_TOOLS_PLUGIN_DIR . 'includes/class-kwirx-woocommerce-tools.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_kwirx_woocommerce_tools() {
    $plugin = new Kwirx_WooCommerce_Tools();
    $plugin->run();
}
run_kwirx_woocommerce_tools();