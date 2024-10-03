/**
 * Fetch Google Reviews via AJAX
 *
 * @package Google_Review_Box
 */

jQuery( document ).ready(
	function ($) {
		// Get the review placeholder element.
		var reviewContainer = $( '.review-box-ajax' );

		// Make the AJAX request.
		$.ajax(
			{
				url: simple_reviews_badge_ajax_object.ajax_url,
				type: 'POST',
				data: {
					action: 'simple_reviews_badge_get_reviews',
					nonce: simple_reviews_badge_ajax_object.nonce,
					img_src: simple_reviews_badge_shortcode_atts.img_src,
					include_schema: simple_reviews_badge_shortcode_atts.include_schema
				},
				success: function (response) {
					// Replace the placeholder with the fetched reviews.
					reviewContainer.html( response );
				},
				error: function () {
					// Handle errors.
					console.error( 'Error fetching Google Reviews.' );
				}
			}
		);
	}
);
