<?php

class Kwirx_Image_Duplicate_Remover {

    public function __construct() {
        add_action('wp_ajax_kwirx_remove_duplicate_images', array($this, 'remove_duplicate_images'));
    }

    public function remove_duplicate_images() {
        try {
            // Check if the security nonce is set
            if (!isset($_POST['security'])) {
                wp_send_json_error('Security nonce is missing.');
                return;
            }

            // Verify the nonce
            if (!wp_verify_nonce($_POST['security'], 'kwirx_remove_duplicate_images')) {
                wp_send_json_error('Invalid security token.');
                return;
            }

            // Check user capabilities
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error('You do not have permission to perform this action.');
                return;
            }

            $batch_size = 10;
            $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => $batch_size,
                'offset'         => $offset,
            );

            $products = get_posts($args);

            $processed = 0;
            $total_removed = 0;
            $updated_products = array();

            foreach ($products as $product) {
                $removed = $this->process_product_images($product->ID);
                if ($removed > 0) {
                    $updated_products[] = array(
                        'name' => $product->post_title,
                        'link' => get_edit_post_link($product->ID),
                        'removed' => $removed
                    );
                }
                $total_removed += $removed;
                $processed++;
            }

            $more = count($products) == $batch_size;

            wp_send_json_success(array(
                'processed'     => $processed,
                'total_removed' => $total_removed,
                'more'          => $more,
                'next_offset'   => $offset + $processed,
                'updated_products' => $updated_products
            ));

        } catch (Exception $e) {
            wp_send_json_error('An unexpected error occurred: ' . $e->getMessage());
        }
    }

    private function process_product_images($product_id) {
        $product = wc_get_product($product_id);
        $image_ids = $product->get_gallery_image_ids();
        array_unshift($image_ids, $product->get_image_id());

        $unique_images = array();
        $removed = 0;

        foreach ($image_ids as $image_id) {
            $image_url = wp_get_attachment_url($image_id);
            $base_name = basename($image_url);
            $name_parts = explode('.', $base_name);
            $extension = array_pop($name_parts);
            $name = implode('.', $name_parts);

            // Remove WP naming scheme for duplicates
            $clean_name = preg_replace('/-\d+$/', '', $name);

            if (!isset($unique_images[$clean_name])) {
                $unique_images[$clean_name] = array(
                    'id' => $image_id,
                    'url' => $image_url,
                    'time' => get_post_time('U', true, $image_id),
                );
            } else {
                if ($unique_images[$clean_name]['time'] < get_post_time('U', true, $image_id)) {
                    // Remove the older image
                    wp_delete_attachment($unique_images[$clean_name]['id'], true);
                    $unique_images[$clean_name] = array(
                        'id' => $image_id,
                        'url' => $image_url,
                        'time' => get_post_time('U', true, $image_id),
                    );
                } else {
                    // Remove the current image
                    wp_delete_attachment($image_id, true);
                }
                $removed++;
            }
        }

        // Update product gallery
        $new_gallery = array_values(array_map(function($img) { return $img['id']; }, $unique_images));
        $product->set_image_id(array_shift($new_gallery));
        $product->set_gallery_image_ids($new_gallery);
        $product->save();

        return $removed;
    }
}