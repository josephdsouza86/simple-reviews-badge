# Simple Reviews Badge

**Simple Reviews Badge** is a WordPress plugin that displays a simple Simple Reviews Badge with aggregate ratings, stars, and a customisable layout. The badge can be used as a shortcode and supports schema markup for SEO.

## Features

- Displays Google Reviews with aggregate ratings and stars.
- Customisable through WordPress admin settings.
- Supports schema markup for enhanced SEO.
- AJAX-based loading for better performance.
- Filterable and customisable HTML structure.
  
## Installation

1. Upload the plugin files to the `/wp-content/plugins/simple-reviews-badge` directory or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure the plugin in **Settings > Simple Reviews Badge**.

## Usage

You can display the Simple Reviews Badge on any page or post by using the following shortcode:

`[simple_reviews_badge_display_reviews include_schema="true" img_src="./logo.png"]`

- include_schema (default: false): Whether to include a product schema describing your review count and average score. Enabling this will add a review star rating to your Google search results on the page your shortcode is included.
- img_src (default: see settings page): Override the main image from the settings page for a single reviews badge instance

The shortcode will render a Simple Reviews Badge with stars, aggregate ratings, and a review count.

## Settings

To configure the plugin, go to Settings > Simple Reviews Badge in the WordPress admin panel. Here are the available settings:

### Basic Settings

1. Google Place ID: The Place ID of your business location on Google.
1. Google API Key: Your Google Places API key.
1. Review Badge Image URL: The image displayed alongside the reviews (e.g., Google logo).
1. Cache Duration: How long (in seconds) to cache the review data before fetching fresh data.
1. Google Review Link: A link to the reviews page on Google (used in the badge).

### Schema Settings

1. Schema Description: A description for schema markup.
1. Schema Name: The name used in the schema markup.
1. Schema Brand: The brand name used in the schema markup.
1. Schema ID: The schema ID (URL).
1. Schema URL: The URL of the product or business being rated.

## Third-Party Services

### Google Places API

This plugin relies on the Google Places API to fetch review data from your Google Business profile. To use this plugin, you will need to provide a Google Places API key and your business’s Place ID.

- **Data Usage**: Your Google API Key and Place ID are used to make API calls to Google’s servers to retrieve review data such as ratings, review counts, and related business information.
- **Service URL**: [Google Places API](https://developers.google.com/places/web-service/overview)
- **Usage Terms**: [Google API Terms of Use](https://cloud.google.com/terms/)

### Note:

Your Google API key and Place ID will be stored locally within your WordPress database, but API calls are made to Google’s external services to retrieve the necessary data.

## Customisation

You can modify the plugin’s behaviour by using WordPress filters and actions.

### Filters

`simple_reviews_badge_rating_word`: Filter the word representing the rating (e.g., 'Excellent', 'Great').

```
add_filter( 'simple_reviews_badge_rating_word', function( $rating_word, $aggregate_rating ) {
    return $aggregate_rating >= 4 ? 'Superb' : $rating_word;
}, 10, 2 );
```

`simple_reviews_badge_review_stars_component`: Modify the HTML for the stars component.

```
add_filter( 'simple_reviews_badge_review_stars_component', function( $stars, $aggregate_rating ) {
    return '<div class="custom-stars">' . $stars . '</div>';
}, 10, 2 );
```

`simple_reviews_badge_review_image_component`: Modify the image used in the badge.

```
add_filter( 'simple_reviews_badge_review_image_component', function( $image, $img_src ) {
    return '<img src="' . esc_url( $img_src ) . '" class="custom-review-logo">';
}, 10, 2 );
```

`simple_reviews_badge_review_template`: Override the entire HTML structure of the badge.

```
add_filter( 'simple_reviews_badge_review_template', function( $template ) {
    return '<div class="custom-review-box">{image}{rating_word}{stars}{review_count}</div>';
});
```

### Actions

`simple_reviews_badge_before_reviews_output`: Triggered before the review data is output.
`simple_reviews_badge_after_reviews_output`: Triggered after the review data is output.

## Error Handling

If the Google Reviews API request fails or returns an invalid response, the plugin will:

- Log the error message to the browser console.
- Send an email notification to the site administrator.

## AJAX Integration
The plugin uses AJAX to load the reviews in the background for better performance. You can modify the AJAX behaviour using:

- AJAX action hooks: `simple_reviews_badge_get_reviews`
- JS script enqueue: Enqueue your custom JavaScript to modify the behaviour of the plugin.

## Styling
To customise the appearance of the badge, modify the CSS file located in `css/srb-styles.min.css` or enqueue additional styles in your theme.

```
.custom-review-box {
    background-color: #f5f5f5;
    padding: 20px;
    border: 1px solid #ddd;
}
```

## FAQ

### How do I get my Google Place ID?

You can find your Google Place ID using the [Google Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id).

### How do I get a Google Places API key?

Refer to the [Google Places API documentation](https://developers.google.com/places/web-service/get-api-key) for instructions on how to get an API key.


## Plugin Information

- **Plugin Name**: Simple Reviews Badge
- **Version**: 1.0.0
- **Author**: [Alpha Labs](https://alphalabs.net)
- **License**: GPL2
- **Text Domain**: simple-reviews-badge

## Changelog
1.0.0
Initial release of the Simple Reviews Badge plugin.
For support and further customisation, visit Alpha Labs.