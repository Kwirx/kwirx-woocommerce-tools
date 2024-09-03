<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kwirx.com
 * @since      1.0.0
 *
 * @package    Kwirx_WooCommerce_Tools
 * @subpackage Kwirx_WooCommerce_Tools/admin
 */

class Kwirx_WooCommerce_Tools_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once KWIRX_TOOLS_PLUGIN_DIR . 'admin/modules/image-duplicate-remover/class-image-duplicate-remover.php';
        new Kwirx_Image_Duplicate_Remover();

        require_once KWIRX_TOOLS_PLUGIN_DIR . 'admin/modules/dimension-updater/class-dimension-updater.php';
        new Kwirx_Dimension_Updater();
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, KWIRX_TOOLS_PLUGIN_URL . 'admin/css/kwirx-woocommerce-tools-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, KWIRX_TOOLS_PLUGIN_URL . 'admin/js/kwirx-woocommerce-tools-admin.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'kwirx_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'remove_duplicate_images_nonce' => wp_create_nonce('kwirx_remove_duplicate_images'),
            'update_product_dimensions_nonce' => wp_create_nonce('kwirx_update_product_dimensions')
        ));
    }

    public function add_plugin_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Kwirx WooCommerce Tools',
            'Kwirx Tools',
            'manage_woocommerce',
            'kwirx-woocommerce-tools',
            array($this, 'display_plugin_admin_page')
        );
    }

    public function display_plugin_admin_page() {
        include_once KWIRX_TOOLS_PLUGIN_DIR . 'admin/partials/kwirx-woocommerce-tools-admin-display.php';
    }

    /**
     * Log messages for debugging purposes.
     *
     * @param mixed $message The message to log.
     */
    public function log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}