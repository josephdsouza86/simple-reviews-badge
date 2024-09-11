jQuery(document).ready(function($) {
    // Get the review placeholder element
    var reviewContainer = $('.review-box-ajax');

    // Make the AJAX request
    $.ajax({
        url: grb_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'grb_get_reviews',
            nonce: grb_ajax_object.nonce
        },
        success: function(response) {
            // Replace the placeholder with the fetched reviews
            reviewContainer.html(response);
        },
        error: function() {
            // Handle errors
            console.error('Error fetching Google Reviews.');
        }
    });
});
