=== Simple Reviews Badge ===
Contributors: dsouzaj86
Tags: google, reviews, badge, ratings
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show your Google rating on your website with a simple badge, including stars and number of reviews.

== Description ==
Simple Reviews Badge is a lightweight plugin that displays aggregate Google Reviews and star ratings in a customisable badge format. It uses the Google Places API to fetch and display reviews with schema markup support for better SEO. 

**Key Features:**
* Display Google Reviews with aggregate ratings and stars.
* Customisable through WordPress admin settings.
* Supports schema markup for enhanced SEO.
* AJAX-based loading for better performance.
* Filterable and customisable HTML structure.

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

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/simple-reviews-badge/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings -> Simple Reviews Badge to configure the plugin options.

== Frequently Asked Questions ==

= How do I get my Google Place ID? =
You can find your Google Place ID using the [Google Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id).

= How do I get a Google Places API key? =
Refer to the [Google Places API documentation](https://developers.google.com/places/web-service/get-api-key) for instructions on how to get an API key.

= Does this plugin rely on any third-party services? =
Yes, the plugin uses the Google Places API to fetch review data. Please refer to Google’s [Privacy Policy](https://policies.google.com/privacy) and [Terms of Service](https://developers.google.com/maps/terms).

== Screenshots ==
1. Example of the Simple Reviews Badge display.

== Changelog ==
= 1.0.0 =
* Initial release of Simple Reviews Badge plugin.

== Upgrade Notice ==
= 1.0.0 =
Initial release of the plugin.
