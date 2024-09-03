<?php
// Generate a nonce for the Image Duplicate Remover
$kwirx_remove_duplicate_images_nonce = wp_create_nonce('kwirx_remove_duplicate_images');
?>
<div id="image-duplicate-remover" class="tab-content">
    <h3>Image Duplicate Remover</h3>
    <p>This tool removes duplicate images from WooCommerce products, keeping only the latest version of each image.</p>
    <p>The process will:</p>
    <ol>
        <li>Fetch current product data, including all images.</li>
        <li>Identify duplicate images based on their filenames.</li>
        <li>Determine the latest image among duplicates based on the upload date and time.</li>
        <li>Remove older duplicate images, keeping only the latest one.</li>
        <li>Update the product with the cleaned image gallery.</li>
    </ol>
    <p><strong>Warning:</strong> This process will permanently delete duplicate images. Please make sure you have a backup before proceeding.</p>
    <button id="start-duplicate-removal" class="button button-primary">Remove Duplicate Images</button>
    <button id="pause-duplicate-removal" class="button" style="display: none;">Pause</button>
    <button id="cancel-duplicate-removal" class="button" style="display: none;">Cancel</button>
    <div id="progress-bar" style="display: none; margin-top: 20px;">
        <div id="progress" style="width: 0%; height: 20px; background-color: #0073aa;"></div>
    </div>
    <p id="progress-text" style="display: none;"></p>
    <div id="updated-products" style="display: none; margin-top: 20px;">
        <h4>Updated Products:</h4>
        <ul id="updated-products-list"></ul>
    </div>
    <div id="error-message" class="notice notice-error" style="display: none;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    var isProcessing = false;
    var isPaused = false;
    var totalProcessed = 0;
    var totalRemoved = 0;
    var totalProducts = 0;

    $('#start-duplicate-removal').on('click', function() {
        if (confirm('Are you sure you want to remove duplicate images? This action cannot be undone.')) {
            $(this).prop('disabled', true);
            $('#pause-duplicate-removal, #cancel-duplicate-removal, #progress-bar, #progress-text, #updated-products').show();
            $('#error-message').hide();
            isProcessing = true;
            isPaused = false;
            totalProcessed = 0;
            totalRemoved = 0;
            totalProducts = 0;
            $('#updated-products-list').empty();
            removeDuplicateImages(0);
        }
    });

    $('#pause-duplicate-removal').on('click', function() {
        isPaused = !isPaused;
        $(this).text(isPaused ? 'Resume' : 'Pause');
        if (!isPaused) {
            removeDuplicateImages(totalProcessed);
        }
    });

    $('#cancel-duplicate-removal').on('click', function() {
        if (confirm('Are you sure you want to cancel the process?')) {
            isProcessing = false;
            isPaused = false;
            $('#start-duplicate-removal').prop('disabled', false);
            $('#pause-duplicate-removal, #cancel-duplicate-removal').hide();
            $('#progress-text').text('Process cancelled. Processed: ' + totalProcessed + ' of ' + totalProducts + ' products. Removed: ' + totalRemoved + ' duplicate images.');
        }
    });

    function removeDuplicateImages(offset) {
        if (!isProcessing || isPaused) return;

        $.ajax({
            url: kwirx_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'kwirx_remove_duplicate_images',
                security: '<?php echo $kwirx_remove_duplicate_images_nonce; ?>',
                offset: offset
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    totalProcessed += data.processed;
                    totalRemoved += data.total_removed;
                    totalProducts = data.total_products;
                    var progressPercentage = (totalProcessed / totalProducts) * 100;
                    $('#progress').css('width', progressPercentage + '%');
                    $('#progress-text').text('Processed: ' + totalProcessed + ' of ' + totalProducts + ' products. Removed: ' + totalRemoved + ' duplicate images.');
                    
                    // Update the list of products with removed duplicates
                    data.updated_products.forEach(function(product) {
                        $('#updated-products-list').append('<li><a href="' + product.link + '" target="_blank">' + product.name + '</a> - ' + product.removed + ' duplicate(s) removed</li>');
                    });

                    if (data.more && isProcessing && !isPaused) {
                        removeDuplicateImages(data.next_offset);
                    } else if (!data.more) {
                        isProcessing = false;
                        $('#progress-text').text('Completed! Processed: ' + totalProcessed + ' of ' + totalProducts + ' products. Removed: ' + totalRemoved + ' duplicate images.');
                        $('#start-duplicate-removal').prop('disabled', false);
                        $('#pause-duplicate-removal, #cancel-duplicate-removal').hide();
                    }
                } else {
                    handleError(response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                handleError('An error occurred while communicating with the server. Please try again.');
            }
        });
    }

    function handleError(errorMessage) {
        isProcessing = false;
        isPaused = false;
        $('#start-duplicate-removal').prop('disabled', false);
        $('#pause-duplicate-removal, #cancel-duplicate-removal').hide();
        $('#error-message').html('<p>' + errorMessage + '</p>').show();
        console.error('Error:', errorMessage);
    }
});
</script>