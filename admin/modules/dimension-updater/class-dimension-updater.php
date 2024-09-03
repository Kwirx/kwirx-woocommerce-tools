<?php

class Kwirx_Dimension_Updater {

    public function __construct() {
        add_action('wp_ajax_update_product_dimensions', array($this, 'update_product_dimensions_ajax'));
    }

    public function update_product_dimensions_ajax() {
        try {
            // Check if the security nonce is set
            if (!isset($_POST['security'])) {
                wp_send_json_error('Security nonce is missing.');
                return;
            }

            // Verify the nonce
            if (!wp_verify_nonce($_POST['security'], 'kwirx_update_product_dimensions')) {
                wp_send_json_error('Invalid security token.');
                return;
            }

            // Check user capabilities
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error('You do not have permission to perform this action.');
                return;
            }

            $batch_size = 10;
            $batch_number = isset($_POST["batch_number"]) ? absint($_POST["batch_number"]) : 1;

            $args = [
                "status" => "publish",
                "limit" => $batch_size,
                "page" => $batch_number,
                "type" => "simple",
            ];
            $products = wc_get_products($args);
            $total_products = wp_count_posts("product")->publish + wp_count_posts("product_variation")->publish;

            $updated_products = array();
            $updated_count = 0;

            foreach ($products as $product) {
                $height = $product->get_attribute("height");
                $width = $product->get_attribute("width");
                $length = $product->get_attribute("length");

                // Check for and handle attributes with multiple values separated by "|", take the last value
                $height = strpos($height, '|') !== false ? trim(end(explode('|', $height))) : $height;
                $width  = strpos($width, '|')  !== false ? trim(end(explode('|', $width)))  : $width;
                $length  = strpos($length, '|')  !== false ? trim(end(explode('|', $length)))  : $length;

                if (!empty($height) && !empty($width) && !empty($length)) {
                    $product->set_height($height);
                    $product->set_width($width);
                    $product->set_length($length);
                    $product->save();
                    $updated_count++;
                    $updated_products[] = array(
                        'name' => $product->get_name(),
                        'link' => get_edit_post_link($product->get_id()),
                    );
                }
            }

            $processed_count = $batch_size * ($batch_number - 1) + count($products);
            $done = $processed_count >= $total_products;

            wp_send_json_success([
                "processed" => $processed_count,
                "updated" => $updated_count,
                "total_products" => $total_products,
                "updated_products" => $updated_products,
                "done" => $done,
            ]);

        } catch (Exception $e) {
            wp_send_json_error('An unexpected error occurred: ' . $e->getMessage());
        }
    }
}