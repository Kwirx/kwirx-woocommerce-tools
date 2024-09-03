<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1>Kwirx WooCommerce Tools</h1>
    <p>A comprehensive toolkit for WooCommerce store management, featuring tools for image duplicate removal, product dimension updates, and more.</p>

    <h2 class="nav-tab-wrapper">
        <a href="#image-duplicate-remover" class="nav-tab nav-tab-active">Image Duplicate Remover</a>
        <a href="#dimension-updater" class="nav-tab">Dimensions Updater</a>
    </h2>

    <?php include_once KWIRX_TOOLS_PLUGIN_DIR . 'admin/modules/image-duplicate-remover/image-duplicate-remover-display.php'; ?>
    <?php include_once KWIRX_TOOLS_PLUGIN_DIR . 'admin/modules/dimension-updater/dimension-updater-display.php'; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
});
</script>