<?php
// Generate a nonce for the Dimension Updater
$kwirx_update_product_dimensions_nonce = wp_create_nonce('kwirx_update_product_dimensions');
?>
<div id="dimension-updater" class="tab-content" style="display: none;">
    <h3>Dimensions Updater</h3>
    <p>This tool updates the dimensions (height, width, and length) of all WooCommerce products based on their set attributes.</p>
    <p>The process will:</p>
    <ol>
        <li>Fetch product data in batches.</li>
        <li>Update dimensions based on the 'height', 'width', and 'length' attributes.</li>
        <li>Handle attributes with multiple values, using the last value.</li>
        <li>Save the updated product information.</li>
    </ol>
    <p><strong>Warning:</strong> This process will update product dimensions. Please make sure you have a backup before proceeding.</p>
    <button id="start-dimension-update" class="button button-primary">Update Dimensions</button>
    <button id="pause-dimension-update" class="button" style="display: none;">Pause</button>
    <button id="cancel-dimension-update" class="button" style="display: none;">Cancel</button>
    <div id="dimension-progress-bar" style="display: none; margin-top: 20px;">
        <div id="dimension-progress" style="width: 0%; height: 20px; background-color: #0073aa;"></div>
    </div>
    <p id="dimension-progress-text" style="display: none;"></p>
    <div id="dimension-updated-products" style="display: none; margin-top: 20px;">
        <h4>Updated Products:</h4>
        <ul id="dimension-updated-products-list"></ul>
    </div>
    <div id="dimension-error-message" class="notice notice-error" style="display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    var isDimensionProcessing = false;
    var isDimensionPaused = false;
    var totalDimensionProcessed = 0;
    var totalDimensionUpdated = 0;
    var totalDimensionProducts = 0;

    $('#start-dimension-update').on('click', function() {
        if (confirm('Are you sure you want to update product dimensions? This action cannot be undone.')) {
            $(this).prop('disabled', true);
            $('#pause-dimension-update, #cancel-dimension-update, #dimension-progress-bar, #dimension-progress-text, #dimension-updated-products').show();
            $('#dimension-error-message').hide();
            isDimensionProcessing = true;
            isDimensionPaused = false;
            totalDimensionProcessed = 0;
            totalDimensionUpdated = 0;
            $('#dimension-updated-products-list').empty();
            updateProductDimensions(1);
        }
    });

    $('#pause-dimension-update').on('click', function() {
        isDimensionPaused = !isDimensionPaused;
        $(this).text(isDimensionPaused ? 'Resume' : 'Pause');
        if (!isDimensionPaused) {
            updateProductDimensions(Math.floor(totalDimensionProcessed / 10) + 1);
        }
    });

    $('#cancel-dimension-update').on('click', function() {
        if (confirm('Are you sure you want to cancel the process?')) {
            isDimensionProcessing = false;
            isDimensionPaused = false;
            $('#start-dimension-update').prop('disabled', false);
            $('#pause-dimension-update, #cancel-dimension-update').hide();
            $('#dimension-progress-text').text('Process cancelled. Processed: ' + totalDimensionProcessed + ' products. Updated: ' + totalDimensionUpdated + ' products.');
        }
    });

    function updateProductDimensions(batchNumber) {
        if (!isDimensionProcessing || isDimensionPaused) return;

        $.ajax({
            url: kwirx_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'update_product_dimensions',
                security: '<?php echo $kwirx_update_product_dimensions_nonce; ?>',
                batch_number: batchNumber
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    totalDimensionProcessed = data.processed;
                    totalDimensionUpdated = data.updated;
                    totalDimensionProducts = data.total_products;

                    var progressPercentage = (totalDimensionProcessed / totalDimensionProducts) * 100;
                    $('#dimension-progress').css('width', progressPercentage + '%');
                    $('#dimension-progress-text').text('Processed: ' + totalDimensionProcessed + ' of ' + totalDimensionProducts + ' products. Updated: ' + totalDimensionUpdated + ' products.');
                    
                    // Update the list of products with updated dimensions
                    data.updated_products.forEach(function(product) {
                        $('#dimension-updated-products-list').append('<li><a href="' + product.link + '" target="_blank">' + product.name + '</a> - Dimensions updated</li>');
                    });

                    if (!data.done && isDimensionProcessing && !isDimensionPaused) {
                        updateProductDimensions(batchNumber + 1);
                    } else if (data.done) {
                        isDimensionProcessing = false;
                        $('#dimension-progress-text').text('Completed! Processed: ' + totalDimensionProcessed + ' products. Updated: ' + totalDimensionUpdated + ' products.');
                        $('#start-dimension-update').prop('disabled', false);
                        $('#pause-dimension-update, #cancel-dimension-update').hide();
                    }
                } else {
                    handleDimensionError(response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                handleDimensionError('An error occurred while communicating with the server. Please try again.');
            }
        });
    }

    function handleDimensionError(errorMessage) {
        isDimensionProcessing = false;
        isDimensionPaused = false;
        $('#start-dimension-update').prop('disabled', false);
        $('#pause-dimension-update, #cancel-dimension-update').hide();
        $('#dimension-error-message').html('<p>' + errorMessage + '</p>').show();
        console.error('Error:', errorMessage);
    }
});
</script>