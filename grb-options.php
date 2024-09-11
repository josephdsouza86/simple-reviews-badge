<?php
/**
 * Google Reviews Badge Options
 *
 * @package Google_Reviews_Badge
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all default options for the Google Reviews Badge plugin.
 *
 * @return array
 */
function grb_get_default_options() {
	return array(
		'grb_place_id'           => '',
		'grb_api_key'            => '',
		'grb_img_src'            => 'https://cdn.trustindex.io/assets/platform/Google/logo.svg',
		'grb_cache_duration'     => HOUR_IN_SECONDS,
		'grb_schema_description' => '',
		'grb_schema_name'        => '',
		'grb_schema_brand'       => '',
		'grb_schema_id'          => '',
		'grb_schema_url'         => '',
		'grb_review_link'        => '',
	);
}

/**
 * Retrieve a specific option with its default fallback.
 *
 * @param string $option_key The option key to retrieve.
 * @return mixed
 */
function grb_get_option( $option_key ) {
	$default_options = grb_get_default_options();
	$value           = get_option( $option_key, isset( $default_options[ $option_key ] ) ? $default_options[ $option_key ] : null );

	// Allow developers to filter the option value.
	return apply_filters( 'grb_get_option_' . $option_key, $value );
}
