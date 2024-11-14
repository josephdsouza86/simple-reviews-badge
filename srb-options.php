<?php
/**
 * Simple Reviews Badge Options
 *
 * @package Simple_Reviews_Badge
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all default options for the Simple Reviews Badge plugin.
 *
 * @return array
 */
function simple_reviews_badge_get_default_options() {
	return array(
		'simple_reviews_badge_place_id'           => '',
		'simple_reviews_badge_api_key'            => '',
		'simple_reviews_badge_img_src'            => esc_url( plugin_dir_url( __FILE__ ) . 'img/logo.svg' ),
		'simple_reviews_badge_cache_duration'     => HOUR_IN_SECONDS,
		'simple_reviews_badge_schema_description' => '',
		'simple_reviews_badge_schema_name'        => '',
		'simple_reviews_badge_schema_brand'       => '',
		'simple_reviews_badge_schema_id'          => '',
		'simple_reviews_badge_schema_url'         => '',
		'simple_reviews_badge_review_link'        => '',
		'simple_reviews_badge_use_ajax'   		  => false,
	);
}

/**
 * Retrieve a specific option with its default fallback.
 *
 * @param string $option_key The option key to retrieve.
 * @return mixed
 */
function simple_reviews_badge_get_option( $option_key ) {
	$default_options = simple_reviews_badge_get_default_options();
	$value           = get_option( $option_key, isset( $default_options[ $option_key ] ) ? $default_options[ $option_key ] : null );

	// Allow developers to filter the option value.
	return apply_filters( 'simple_reviews_badge_get_option_' . $option_key, $value );
}
