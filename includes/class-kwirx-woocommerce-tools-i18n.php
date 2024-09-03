<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://kwirx.com
 * @since      1.0.0
 *
 * @package    Kwirx_WooCommerce_Tools
 * @subpackage Kwirx_WooCommerce_Tools/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Kwirx_WooCommerce_Tools
 * @subpackage Kwirx_WooCommerce_Tools/includes
 * @author     Kwirx Creative <info@kwirx.com>
 */
class Kwirx_WooCommerce_Tools_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'kwirx-woocommerce-tools',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }

}