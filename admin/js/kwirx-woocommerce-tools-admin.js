(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab functionality
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.tab-content').hide();
            $(target).show();
        });

        // Show the first tab by default
        $('.nav-tab:first').click();
    });

})(jQuery);