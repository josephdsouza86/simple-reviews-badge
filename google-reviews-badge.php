<?php
/*
Plugin Name: Google Reviews Badge
Plugin URI: https://yourwebsite.com/
Description: Displays a Google Reviews badge with aggregate ratings and stars.
Version: 1.2
Author: Joe
Author URI: https://alphalabs.net/
License: GPL2
Text Domain: google-reviews-badge
 */

// Prevent direct access
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Include the admin settings page
// Include options
require_once(plugin_dir_path(__FILE__) . 'grb-options.php');

// Include the admin settings page
require_once(plugin_dir_path(__FILE__) . 'admin-settings.php');

// Register shortcode to display the reviews
add_shortcode( 'display_reviews', 'grb_fetch_and_display_reviews' );

// Register settings
function grb_register_settings() {
    // Register basic settings
    register_setting( 'grb_options_group', 'grb_place_id', 'sanitize_text_field' );
    register_setting( 'grb_options_group', 'grb_api_key', 'sanitize_text_field' );
    register_setting( 'grb_options_group', 'grb_img_src', 'esc_url' );
    register_setting( 'grb_options_group', 'grb_cache_duration', 'intval' );

    // Register schema settings
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
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function grb_fetch_and_display_reviews($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(
        array(
            'img_src' => grb_get_option('grb_img_src'),
            'include_schema' => false,
        ),
        $atts
    );

    // Return a placeholder that will be replaced by AJAX
    return '<div class="review-box-ajax">' . __('Loading reviews...', 'google-reviews-badge') . '</div>';
}

/**
 * Generate HTML for the Google Reviews.
 *
 * @return string HTML output for the reviews
 */
function grb_generate_review_html() {
    // Fetch review data (the same way you currently do it)
    $place_id = grb_get_option('grb_place_id');
    $api_key = grb_get_option('grb_api_key');
    $cache_duration = grb_get_option('grb_cache_duration');
    $review_link = grb_get_option('grb_review_link');
    
    $transient_key = 'google_reviews_data_' . md5($place_id);
    $cached_data = get_transient($transient_key);

    if (false === $cached_data) {
        $api_url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode($place_id) . '&fields=rating,user_ratings_total&key=' . urlencode($api_key);
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            $error_message = 'Google Reviews API request failed: ' . $response->get_error_message();
            grb_log_to_console($error_message);
            grb_notify_admin('Google Reviews API Error', $error_message);
            return ''; // Return nothing to prevent display on the page
        }

        $data = wp_remote_retrieve_body($response);
        $data = json_decode($data, true);

        if (!isset($data['result'])) {
            $error_message = 'Invalid response from Google Reviews API: ' . json_encode($data);
            grb_log_to_console($error_message);
            grb_notify_admin('Google Reviews API Invalid Response', $error_message);
            return ''; // Return nothing to prevent display on the page
        }

        $aggregateRating = isset($data['result']['rating']) ? floatval($data['result']['rating']) : 0;
        $reviewCount = isset($data['result']['user_ratings_total']) ? intval($data['result']['user_ratings_total']) : 0;

        // Cache the data
        set_transient($transient_key, ['rating' => $aggregateRating, 'count' => $reviewCount], $cache_duration);
    } else {
        $aggregateRating = $cached_data['rating'];
        $reviewCount = $cached_data['count'];
    }

    $response = '';

    // Check if Schema Markup should be included
    if (filter_var($atts['include_schema'], FILTER_VALIDATE_BOOLEAN)) {
        // Fetch schema settings
        $schema_description = grb_get_option( 'grb_schema_description' );
        $schema_name = grb_get_option( 'grb_schema_name' );
        $schema_brand = grb_get_option( 'grb_schema_brand' );
        $schema_id = grb_get_option( 'grb_schema_id' );
        $schema_url = grb_get_option( 'grb_schema_url' );

        // Echo Schema Markup
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
                "ratingValue": "' . esc_attr( $aggregateRating ) . '",
                "reviewCount": "' . esc_attr( $reviewCount ) . '"
            }
        }
        </script>';
    }

    // Filterable rating word and aggregate rating
    $rating_word = apply_filters( 'grb_rating_word', grb_get_rating_word( $aggregateRating ), $aggregateRating );

    // Visual Output with Star Representation
    $response .= '<div class="review-box">
        <a href="' . esc_url($review_link) . '" class="review-link" target="_blank">
            <div>
                <strong class="review-word">' . esc_html( $rating_word ) . '</strong>
                <div class="stars">';

    // Loop to display stars
    for ( $i = 1; $i <= 5; $i++ ) {
        $response .= apply_filters( 'grb_star_svg', grb_get_star_svg( $i, $aggregateRating ), $i, $aggregateRating );
    }

    $response .= '</div>
            </div>
            <div>
                <div class="review-description">' . __( 'Based on', 'google-reviews-badge' ) . ' <strong>' . esc_html( $reviewCount ) . ' ' . __( 'reviews', 'google-reviews-badge' ) . '</strong></div>
                <img src="' . esc_url( $atts['img_src'] ) . '" alt="' . __( 'Google Business Profile', 'google-reviews-badge' ) . '" class="review-logo">
            </div>
        </a>
    </div>';

    return $response;
}

/**
 * Get rating word based on aggregate rating
 *
 * @param float $aggregateRating
 * @return string
 */
function grb_get_rating_word( $aggregateRating ) {
    if ( $aggregateRating == 5 ) {
        return __( 'Excellent', 'google-reviews-badge' );
    } elseif ( $aggregateRating >= 4.5 ) {
        return __( 'Great', 'google-reviews-badge' );
    } elseif ( $aggregateRating >= 4 ) {
        return __( 'Good', 'google-reviews-badge' );
    } elseif ( $aggregateRating >= 3.5 ) {
        return __( 'Average', 'google-reviews-badge' );
    } elseif ( $aggregateRating >= 3 ) {
        return __( 'Fair', 'google-reviews-badge' );
    } else {
        return __( 'Poor', 'google-reviews-badge' );
    }
}

/**
 * Get SVG for star
 *
 * @param int $i Current star index
 * @param float $aggregateRating
 * @return string SVG markup
 */
function grb_get_star_svg( $i, $aggregateRating ) {
    if ( $i <= floor( $aggregateRating ) ) {
        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                    <path fill="#fbbf30" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798z"></path>
                </svg>';
    } elseif ( $i <= ceil( $aggregateRating ) && $i > floor( $aggregateRating ) ) {
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
    check_ajax_referer('grb_ajax_nonce', 'nonce'); // Security check

    // Output the review data (reuse your existing function to fetch reviews)
    $output = grb_generate_review_html(); // Function to generate review HTML
    echo $output;

    wp_die(); // Required to properly terminate AJAX requests
}
add_action('wp_ajax_grb_get_reviews', 'grb_ajax_get_reviews');
add_action('wp_ajax_nopriv_grb_get_reviews', 'grb_ajax_get_reviews'); // Allow access for non-logged-in users

/**
* Enqueue plugin styles 
*/ 
function grb_enqueue_styles() { 
    wp_enqueue_style('grb-styles', plugin_dir_url(FILE) . 'css/grb-styles.min.css'); 
} 
add_action('wp_enqueue_scripts', 'grb_enqueue_styles'); 

/**
 * Enqueue plugin scripts
 */
function grb_enqueue_ajax_scripts() {
    wp_enqueue_script(
        'grb-ajax-script',
        plugin_dir_url(__FILE__) . 'js/grb-ajax.js',
        array('jquery'),
        null,
        true
    );

    // Localize the AJAX URL for use in JavaScript
    wp_localize_script(
        'grb-ajax-script',
        'grb_ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('grb_ajax_nonce') // Security nonce
        )
    );
}
add_action('wp_enqueue_scripts', 'grb_enqueue_ajax_scripts');

/**
 * Log errors to the browser console.
 *
 * @param string $message
 */
function grb_log_to_console($message) {
    echo '<script>console.error("' . esc_js($message) . '");</script>';
}

/**
 * Notify the admin via email about an error.
 *
 * @param string $subject
 * @param string $message
 */
function grb_notify_admin($subject, $message) {
    $admin_email = get_option('admin_email');
    $email_subject = '[' . get_bloginfo('name') . '] ' . $subject;
    $email_message = 'An issue has occurred with the Google Reviews Badge plugin: ' . "\r\n\r\n" . $message;
    
    // Send the email
    wp_mail($admin_email, $email_subject, $email_message);
}

?>