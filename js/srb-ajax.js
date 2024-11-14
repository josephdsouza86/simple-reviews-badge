/**
 * Fetch Google Reviews via AJAX
 *
 * @package Google_Review_Box
 */

jQuery( document ).ready(
	function ($) {
		// Get the review placeholder element.
		var reviewContainer = $( '.review-box-ajax' );

		var data_img_src = reviewContainer.data( 'img-src' );
		var data_include_schema = reviewContainer.data( 'include-schema' );

		// Make the AJAX request.
		$.ajax(
			{
				url: simple_reviews_badge_ajax_object.ajax_url,
				type: 'POST',
				data: {
					action: 'simple_reviews_badge_get_reviews',
					nonce: simple_reviews_badge_ajax_object.nonce,
					img_src: data_img_src,
					include_schema: data_include_schema
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
