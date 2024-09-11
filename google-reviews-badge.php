<?php
/*
Plugin Name: Google Reviews Badge
Plugin URI: https://yourwebsite.com/
Description: Displays a Google Reviews badge with aggregate ratings and stars.
Version: 1.1
Author: Joe
Author URI: https://alphalabs.net/
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the admin settings page
require_once(plugin_dir_path(__FILE__) . 'admin-settings.php');

// Register shortcode to display the reviews
add_shortcode('display_reviews', 'grb_fetch_and_display_reviews');

// Register settings
function grb_register_settings() {
    // Register basic settings
    register_setting('grb_options_group', 'grb_place_id', 'sanitize_text_field');
    register_setting('grb_options_group', 'grb_api_key', 'sanitize_text_field');
    register_setting('grb_options_group', 'grb_img_src', 'esc_url');
    register_setting('grb_options_group', 'grb_cache_duration', 'intval');
    
    // Register schema settings
    register_setting('grb_options_group', 'grb_include_schema', 'boolval');
    register_setting('grb_options_group', 'grb_schema_description', 'sanitize_textarea_field');
    register_setting('grb_options_group', 'grb_schema_name', 'sanitize_text_field');
    register_setting('grb_options_group', 'grb_schema_brand', 'sanitize_text_field');
    register_setting('grb_options_group', 'grb_schema_id', 'esc_url');
    register_setting('grb_options_group', 'grb_schema_url', 'esc_url');
}
add_action('admin_init', 'grb_register_settings');

/**
 * Fetch and display Google Reviews
 *
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function grb_fetch_and_display_reviews($atts) {
    // Extract shortcode attributes with default
    $atts = shortcode_atts(
        array(
            'img_src' => get_option('grb_img_src', 'https://cdn.trustindex.io/assets/platform/Google/logo.svg'),
        ),
        $atts
    );

    // Get settings from admin
    $place_id = get_option('grb_place_id', 'ChIJuQkUQ5YqdEgRipQ_lAyte_Y');
    $api_key = get_option('grb_api_key', 'your_default_api_key_here');
    $cache_duration = get_option('grb_cache_duration', HOUR_IN_SECONDS);

    // Check if transient exists
    $cached_data = get_transient('google_reviews_data');

    if (false === $cached_data) {
        // Fetch reviews from Google Places API
        $api_url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode($place_id) . '&fields=rating,user_ratings_total&key=' . urlencode($api_key);
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return '<p>Error fetching reviews.</p>';
        }

        $data = wp_remote_retrieve_body($response);
        $data = json_decode($data, true);

        if (!isset($data['result'])) {
            return '<p>Invalid response from Google API.</p>';
        }

        // Calculate aggregate rating
        $aggregateRating = isset($data['result']['rating']) ? floatval($data['result']['rating']) : 0;
        $reviewCount = isset($data['result']['user_ratings_total']) ? intval($data['result']['user_ratings_total']) : 0;

        // Store data in transient
        set_transient('google_reviews_data', ['rating' => $aggregateRating, 'count' => $reviewCount], $cache_duration);
    } else {
        $aggregateRating = $cached_data['rating'];
        $reviewCount = $cached_data['count'];
    }

    $response = '';

    // Check if Schema Markup should be included
    if (get_option('grb_include_schema', false)) {
        // Fetch schema settings
        $schema_description = get_option('grb_schema_description', 'Custom web design services by Alpha Labs.');
        $schema_name = get_option('grb_schema_name', 'Alpha Labs Web Design');
        $schema_brand = get_option('grb_schema_brand', 'Alpha Labs');
        $schema_id = get_option('grb_schema_id', 'https://alphalabs.net/web-design-and-development/');
        $schema_url = get_option('grb_schema_url', 'https://alphalabs.net/');

        // Echo Schema Markup
        $response .= '<script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "description": "' . esc_js($schema_description) . '",
            "@id": "' . esc_url($schema_id) . '",
            "name": "' . esc_js($schema_name) . '",
            "brand": {
                "@type": "Brand",
                "name": "' . esc_js($schema_brand) . '"
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "' . esc_attr($aggregateRating) . '",
                "reviewCount": "' . esc_attr($reviewCount) . '"
            }
        }
        </script>';
    }

    // Determine rating word
    $rating_word = '';
    if ($aggregateRating == 5) {
        $rating_word = 'Excellent';
    } elseif ($aggregateRating >= 4.5) {
        $rating_word = 'Great';
    } elseif ($aggregateRating >= 4) {
        $rating_word = 'Good';
    } elseif ($aggregateRating >= 3.5) {
        $rating_word = 'Average';
    } elseif ($aggregateRating >= 3) {
        $rating_word = 'Fair';
    } else {
        $rating_word = 'Poor';
    }

    // Visual Output with Star Representation
    $response .= '<div class="review-box">
        <a href="https://maps.app.goo.gl/vgyvhyXa98rc7pUv5" class="review-link" target="_blank">
            <div>
                <strong class="review-word">' . esc_html($rating_word) . '</strong>
                <div class="stars">';

    // Loop to display stars
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($aggregateRating)) {
            // Full star
            $response .= grb_get_star_svg('full');
        } elseif ($i <= ceil($aggregateRating) && $i > floor($aggregateRating)) {
            // Half star
            $response .= grb_get_star_svg('half');
        } else {
            // Empty star
            $response .= grb_get_star_svg('empty');
        }
    }

    $response .= '</div>
            </div>
            <div>
                <div class="review-description">Based on <strong>' . esc_html($reviewCount) . ' reviews</strong></div>
                <img src="' . esc_url($atts['img_src']) . '" alt="Google Business Profile" class="review-logo">
            </div>
        </a>
    </div>';

    // Inline Styles (Consider moving to a separate CSS file for better performance)
    $response .= '<style>
        .review-box {
            max-width: 315px;
            text-align: center;
            padding: 10px 15px;
            background-color: #fff;
            box-shadow: 2px 1px 2px 0px #e9e9e9;
            margin: 20px 0;
            border: 1px solid #ededed;
        }
        .review-box > a {                
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }
        .review-box .review-word {
            font-size: 1.4rem;
            display: block;
            margin-bottom: 5px;
        }
        .review-box .stars svg {
            width: 22px;
            height: 22px;
            display: inline-block;                
        }
        .review-box .stars svg + svg {
            margin-left: 2px;
        }
        .review-box .review-description {
            font-size: 14px;
        }
        .review-box .review-logo {
            max-width: 80px;
        }
    </style>';

    return $response;
}

/**
 * Get SVG for star
 *
 * @param string $type 'full', 'half', or 'empty'
 * @return string SVG markup
 */
function grb_get_star_svg($type) {
    switch ($type) {
        case 'full':
            return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <path fill="#fbbf30" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798z"></path>
                    </svg>';
        case 'half':
            return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <defs>
                            <linearGradient id="halfGradient">
                                <stop offset="50%" stop-color="#fbbf30"/>
                                <stop offset="50%" stop-color="#e0e0e0"/>
                            </linearGradient>
                        </defs>
                        <path fill="url(#halfGradient)" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798z"></path>
                    </svg>';
        case 'empty':
        default:
            return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                        <path fill="#e0e0e0" d="M32 12.408l-11.056-1.607-4.944-10.018-4.944 10.018-11.056 1.607 8 7.798-1.889 11.011 9.889-5.199 9.889 5.199-1.889-11.011 8-7.798zM16 23.547l-6.983 3.671 1.334-7.776-5.65-5.507 7.808-1.134 3.492-7.075 3.492 7.075 7.807 1.134-5.65 5.507 1.334 7.776-6.983-3.671z"></path>
                    </svg>';
    }
}
?>
