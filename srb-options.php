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
function srb_get_default_options() {
	return array(
		'srb_place_id'           => '',
		'srb_api_key'            => '',
		'srb_img_src'            => esc_url( plugin_dir_url( __FILE__ ) . 'img/logo.svg' ),
		'srb_cache_duration'     => HOUR_IN_SECONDS,
		'srb_schema_description' => '',
		'srb_schema_name'        => '',
		'srb_schema_brand'       => '',
		'srb_schema_id'          => '',
		'srb_schema_url'         => '',
		'srb_review_link'        => '',
	);
}

/**
 * Retrieve a specific option with its default fallback.
 *
 * @param string $option_key The option key to retrieve.
 * @return mixed
 */
function srb_get_option( $option_key ) {
	$default_options = srb_get_default_options();
	$value           = get_option( $option_key, isset( $default_options[ $option_key ] ) ? $default_options[ $option_key ] : null );

	// Allow developers to filter the option value.
	return apply_filters( 'srb_get_option_' . $option_key, $value );
}
