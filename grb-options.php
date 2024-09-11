<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all default options for the Google Reviews Badge plugin.
 *
 * @return array
 */
function grb_get_default_options() {
    return [
        'grb_place_id'        => 'ChIJuQkUQ5YqdEgRipQ_lAyte_Y',
        'grb_api_key'         => 'your_default_api_key_here',
        'grb_img_src'         => 'https://cdn.trustindex.io/assets/platform/Google/logo.svg',
        'grb_cache_duration'  => HOUR_IN_SECONDS,
        'grb_schema_description' => 'Custom web design services by Alpha Labs.',
        'grb_schema_name'     => 'Alpha Labs Web Design',
        'grb_schema_brand'    => 'Alpha Labs',
        'grb_schema_id'       => 'https://alphalabs.net/web-design-and-development/',
        'grb_schema_url'      => 'https://alphalabs.net/',
        'grb_review_link'     => 'https://maps.app.goo.gl/vgyvhyXa98rc7pUv5',
    ];
}

/**
 * Retrieve a specific option with its default fallback.
 *
 * @param string $option_key
 * @return mixed
 */
function grb_get_option($option_key) {
    $default_options = grb_get_default_options();
    $value = get_option($option_key, isset($default_options[$option_key]) ? $default_options[$option_key] : null);

    // Allow developers to filter the option value
    return apply_filters('grb_get_option_' . $option_key, $value);
}