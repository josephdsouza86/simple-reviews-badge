<?php
/**
 * Plugin Name: Simple Reviews Badge
 * Plugin URI: https://alphalabs.net/simple-reviews-badge/
 * Description: Displays a simple Simple Reviews Badge with aggregate ratings and stars.
 * Version: 1.0
 * Author: Alpha Labs
 * Author URI: https://alphalabs.net/
 * License: GPL2
 * Text Domain: simple-reviews-badge
 *
 * @package Simple_Reviews_Badge
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the admin settings page.
require_once plugin_dir_path( __FILE__ ) . 'grb-options.php';

// Include the admin settings page.
require_once plugin_dir_path( __FILE__ ) . 'admin-settings.php';

// Register shortcode to display the reviews.
add_shortcode( 'display_reviews', 'grb_fetch_and_display_reviews' );

/**
 * Register plugin settings
 */
function grb_register_settings() {
	// Register basic settings.
	register_setting( 'grb_options_group', 'grb_place_id', 'sanitize_text_field' );
	register_setting( 'grb_options_group', 'grb_api_key', 'sanitize_text_field' );
	register_setting( 'grb_options_group', 'grb_img_src', 'esc_url' );
	register_setting( 'grb_options_group', 'grb_cache_duration', 'intval' );
	register_setting( 'grb_options_group', 'grb_review_link', 'esc_url' );

	// Register schema settings.
	register_setting( 'grb_options_group', 'grb_schema_description', 'sanitize_textarea_field' );
	register_setting( 'grb_options_group', 'grb_schema_name', 'sanitize_text_field' );
	register_setting( 'grb_options_group', 'grb_schema_brand', 'sanitize_text_field' );
	register_setting( 'grb_options_group', 'grb_schema_id', 'esc_url' );
	register_setting( 'grb_options_group', 'grb_schema_url', 'esc_url' );
}
add_action( 'admin_init', 'grb_register_settings' );

/**
 * Fetch and display Google Reviews
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output
 */
function grb_fetch_and_display_reviews( $atts ) {
	// Parse the shortcode attributes.
	$atts = shortcode_atts(
		array(
			'img_src'        => grb_get_option( 'grb_img_src' ),
			'include_schema' => false,
		),
		$atts
	);

	// See if we already have the data cached.
	$place_id       = grb_get_option( 'grb_place_id' );

	$transient_key = 'google_reviews_data_' . md5( $place_id );
	$cached_data   = get_transient( $transient_key );

	if ( false === $cached_data ) {
		// Let's defer the loading of reviews to AJAX, so as not to hold up the page load.

		// Localize the shortcode attributes to use in the AJAX request.
		wp_localize_script(
			'grb-ajax-script',
			'grb_shortcode_atts',
			array(
				'img_src'        => esc_url( $atts['img_src'] ),
				'include_schema' => $atts['include_schema'],
			)
		);

		// Return a placeholder that will be replaced by AJAX.
		return '<div class="review-box-ajax">' . __( 'Loading reviews...', 'simple-reviews-badge' ) . '</div>';
	} else {
		// Generate the review HTML.
		$output = grb_generate_review_html( $atts );

		// Render the review HTML.
		return render_review_html( $output );
	}
}

/**
 * Generate HTML for the Google Reviews.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the reviews
 */
function grb_generate_review_html( $atts ) {
	// Fetch review data.
	$place_id       = grb_get_option( 'grb_place_id' );
	$api_key        = grb_get_option( 'grb_api_key' );
	$cache_duration = grb_get_option( 'grb_cache_duration' );
	$review_link    = grb_get_option( 'grb_review_link' );

	$transient_key = 'google_reviews_data_' . md5( $place_id );
	$cached_data   = get_transient( $transient_key );

	if ( false === $cached_data ) {
		// Fetch review data from Google Places API.
		$api_url  = apply_filters( 'grb_before_api_request', 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode( $place_id ) . '&fields=rating,user_ratings_total&key=' . urlencode( $api_key ) );
		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			$error_message = 'Google Reviews API request failed: ' . $response->get_error_message();
			grb_log_to_console( $error_message );
			grb_notify_admin( 'Google Reviews API Error', $error_message );
			return '';
		}

		// Parse the response.
		$data = wp_remote_retrieve_body( $response );
		$data = json_decode( $data, true );

		if ( ! isset( $data['result'] ) ) {
			$error_message = 'Invalid response from Google Reviews API: ' . wp_json_encode( $data );
			grb_log_to_console( $error_message );
			grb_notify_admin( 'Google Reviews API Invalid Response', $error_message );
			return '';
		}

		$aggregate_rating = isset( $data['result']['rating'] ) ? floatval( $data['result']['rating'] ) : 0;
		$review_count     = isset( $data['result']['user_ratings_total'] ) ? intval( $data['result']['user_ratings_total'] ) : 0;

		// Cache the data.
		set_transient(
			$transient_key,
			array(
				'rating' => $aggregate_rating,
				'count'  => $review_count,
			),
			$cache_duration
		);
	} else {
		$aggregate_rating = $cached_data['rating'];
		$review_count     = $cached_data['count'];
	}

	$response = '';

	// Check if Schema Markup should be included.
	if ( filter_var( $atts['include_schema'], FILTER_VALIDATE_BOOLEAN ) ) {
		// Fetch schema settings.
		$schema_description = grb_get_option( 'grb_schema_description' );
		$schema_name        = grb_get_option( 'grb_schema_name' );
		$schema_brand       = grb_get_option( 'grb_schema_brand' );
		$schema_id          = grb_get_option( 'grb_schema_id' );
		$schema_url         = grb_get_option( 'grb_schema_url' );

		// Echo Schema Markup.
		$response .= '<script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "description": "' . esc_js( $schema_description ) . '",
            "@id": "' . esc_url( $schema_id ) . '",
            "name": "' . esc_js( $schema_name ) . '",
            "brand": {
                "@type": "Brand",
                "name": "' . esc_js( $schema_brand ) . '"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "' . esc_attr( $aggregate_rating ) . '",
                "reviewCount": "' . esc_attr( $review_count ) . '"
            }
        }
        </script>';
	}

	// Get the rating word based on the aggregate rating.
	$rating_word = apply_filters( 'grb_rating_word', grb_get_rating_word( $aggregate_rating ), $aggregate_rating );

	// Loop to display stars.
	$stars = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$stars .= apply_filters( 'grb_star_svg', grb_get_star_svg( $i, $aggregate_rating ), $i, $aggregate_rating );
	}

	// Get the brand image.
	$brand_img = '<img src="' . esc_url( $atts['img_src'] ) . '" alt="' . __( 'Google Business Profile', 'simple-reviews-badge' ) . '" class="review-logo">';

	// Get the review count text.
	$review_count_text = __( 'Based on', 'simple-reviews-badge' ) . ' <strong>' . esc_html( $review_count ) . ' ' . __( 'reviews', 'simple-reviews-badge' ) . '</strong>';

	// Allow each component to be filtered.
	$rating_word       = apply_filters( 'grb_review_word_component', '<strong class="review-word">' . esc_html( $rating_word ) . '</strong>', $aggregate_rating );
	$stars             = apply_filters( 'grb_review_stars_component', '<div class="stars">' . $stars . '</div>', $aggregate_rating );
	$brand_img         = apply_filters( 'grb_review_image_component', $brand_img, $atts['img_src'] );
	$review_count_text = apply_filters( 'grb_review_count_component', '<div class="review-description">' . $review_count_text . '</div>', $review_count );

	// Default template structure using named placeholders.
	$template = '<div class="review-box">
    <a href="{review_link}" class="review-link" target="_blank">
        {rating_word}{stars}
        {review_count}{image}
    </a>
    </div>';

	// Allow developers to modify the template structure.
	$template = apply_filters( 'grb_review_template', $template );

	// Map of placeholders and corresponding component values.
	$placeholders = array(
		'{review_link}'  => esc_url( $review_link ),
		'{rating_word}'  => $rating_word,
		'{stars}'        => $stars,
		'{review_count}' => $review_count_text,
		'{image}'        => $brand_img,
	);

	// Replace the placeholders with actual values.
	$response = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template );

	// Return the final output.
	return $response;
}

/**
 * Get rating word based on aggregate rating
 *
 * @param float $aggregate_rating Aggregate rating.
 * @return string
 */
function grb_get_rating_word( $aggregate_rating ) {
	if ( 5 === $aggregate_rating ) {
		return __( 'Excellent', 'simple-reviews-badge' );
	} elseif ( 4.5 <= $aggregate_rating ) {
		return __( 'Great', 'simple-reviews-badge' );
	} elseif ( 4 <= $aggregate_rating ) {
		return __( 'Good', 'simple-reviews-badge' );
	} elseif ( 3.5 <= $aggregate_rating ) {
		return __( 'Average', 'simple-reviews-badge' );
	} elseif ( 3 <= $aggregate_rating ) {
		return __( 'Fair', 'simple-reviews-badge' );
	} else {
		return __( 'Poor', 'simple-reviews-badge' );
	}
}

/**
 * Get SVG for star
 *
 * @param int   $i Current star index (1-5).
 * @param float $aggregate_rating Aggregate rating.
 * @return string SVG markup
 */
function grb_get_star_svg( $i, $aggregate_rating ) {
	if ( $i <= floor( $aggregate_rating ) ) {
		return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                    <path fill="#fbbf30" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798z"></path>
                </svg>';
	} elseif ( $i <= ceil( $aggregate_rating ) && $i > floor( $aggregate_rating ) ) {
		return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                            <path fill="#fbbf30" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798zM16 23.547l-0.029 0.015 0.029-17.837 3.492 7.075 7.807 1.134-5.65 5.507 1.334 7.776-6.983-3.671z"></path>
                        </svg>';
	} else {
		return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                            <path fill="#fbbf30" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798zM16 23.547l-6.983 3.671 1.334-7.776-5.65-5.507 7.808-1.134 3.492-7.075 3.492 7.075 7.807 1.134-5.65 5.507 1.334 7.776-6.983-3.671z"></path>
                        </svg>';
	}
}

/**
 * Handle AJAX request to fetch Google Reviews.
 */
function grb_ajax_get_reviews() {
	check_ajax_referer( 'grb_ajax_nonce', 'nonce' );

	// Get the shortcode attributes from the AJAX request.
	$img_src        = isset( $_POST['img_src'] ) ? sanitize_url( wp_unslash( $_POST['img_src'] ) ) : '';
	$include_schema = isset( $_POST['include_schema'] ) ? filter_var( wp_unslash( $_POST['include_schema'] ), FILTER_VALIDATE_BOOLEAN ) : false;

	// Output the review data.
	$output = grb_generate_review_html(
		array(
			'img_src'        => $img_src,
			'include_schema' => $include_schema,
		)
	);

	echo render_review_html( $output );

	wp_die(); // Required to properly terminate AJAX requests.
}
add_action( 'wp_ajax_grb_get_reviews', 'grb_ajax_get_reviews' );
add_action( 'wp_ajax_nopriv_grb_get_reviews', 'grb_ajax_get_reviews' );

/**
 * Render the review HTML output. Includes hooks for customisation and tag escaping.
 * 
 * @param string $output Review HTML output.
 * @return void
 */
function render_review_html( $output ) {
	$final_html = '';

	ob_start();

	// Action hook before the review output.
	do_action( 'grb_before_reviews_output' );

	$allowed_tags = array(
		'div'    => array(
			'class' => array(),
		),
		'a'      => array(
			'href'   => array(),
			'class'  => array(),
			'target' => array(),
		),
		'strong' => array(
			'class' => array(),
		),
		'img'    => array(
			'src'   => array(),
			'alt'   => array(),
			'class' => array(),
		),
		'svg'    => array(
			'version' => array(),
			'xmlns'   => array(),
			'width'   => array(),
			'height'  => array(),
			'viewBox' => array(),
			'viewbox' => array(),
		),
		'path'   => array(
			'fill' => array(),
			'd'    => array(),
		),
	);

	echo wp_kses( $output, $allowed_tags );

	// Action hook after the review output.
	do_action( 'grb_after_reviews_output' );

    // Get the output buffer contents and clean the buffer
    $final_html = ob_get_clean();

    // Return the captured HTML
    return $final_html;
}

/**
 * Enqueue plugin styles
 */
function grb_enqueue_styles() {
	wp_enqueue_style( 'grb-styles', plugin_dir_url( __FILE__ ) . 'css/grb-styles.min.css' );
}
add_action( 'wp_enqueue_scripts', 'grb_enqueue_styles' );

/**
 * Enqueue plugin scripts
 */
function grb_enqueue_ajax_scripts() {
	wp_enqueue_script(
		'grb-ajax-script',
		plugin_dir_url( __FILE__ ) . 'js/grb-ajax.js',
		array( 'jquery' ),
		null,
		true
	);

	// Localize the AJAX URL for use in JavaScript.
	wp_localize_script(
		'grb-ajax-script',
		'grb_ajax_object',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'grb_ajax_nonce' ), // Security nonce.
		)
	);
}
add_action( 'wp_enqueue_scripts', 'grb_enqueue_ajax_scripts' );

/**
 * Log errors to the browser console.
 *
 * @param string $message Error message.
 */
function grb_log_to_console( $message ) {
	echo '<script>console.error("' . esc_js( $message ) . '");</script>';
}

/**
 * Notify the admin via email about an error.
 *
 * @param string $subject Email subject.
 * @param string $message Email message.
 */
function grb_notify_admin( $subject, $message ) {
	$admin_email   = get_option( 'admin_email' );
	$email_subject = '[' . get_bloginfo( 'name' ) . '] ' . $subject;
	$email_message = 'An issue has occurred with the Simple Reviews Badge plugin: ' . "\r\n\r\n" . $message;

	// Send the email.
	wp_mail( $admin_email, $email_subject, $email_message );
}
