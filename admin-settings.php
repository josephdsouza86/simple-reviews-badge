<?php
/**
 * Add settings page to the WordPress admin menu
 *
 * @package Simple_Reviews_Badge
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add settings page to the WordPress admin menu
 */
function simple_reviews_badge_add_admin_menu() {
	add_options_page(
		__( 'Simple Reviews Badge', 'simple-reviews-badge' ), // i18n support.
		__( 'Simple Reviews Badge', 'simple-reviews-badge' ), // i18n support.
		'manage_options',
		'simple-reviews-badge',
		'simple_reviews_badge_settings_page'
	);
}
add_action( 'admin_menu', 'simple_reviews_badge_add_admin_menu' );

/**
 * Settings page content
 */
function simple_reviews_badge_settings_page() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Display messages.
	settings_errors( 'simple_reviews_badge_messages' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Simple Reviews Badge Settings', 'simple-reviews-badge' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'simple_reviews_badge_options_group' ); // Security: nonce for form submission.
				do_settings_sections( 'simple_reviews_badge_options_group' );
				?>
				<table class="form-table">
					<!-- Basic Settings -->
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google Place ID', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="simple_reviews_badge_place_id" value="<?php echo esc_attr( simple_reviews_badge_get_option( 'simple_reviews_badge_place_id' ) ); ?>" placeholder="e.g., ChIJuQkUQ5YqdEgRipQ_lAyte_Y" required />
							<p class="description"><?php esc_html_e( 'Enter your Google Place ID. This is used to fetch reviews for your business.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google API Key', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="simple_reviews_badge_api_key" value="<?php echo esc_attr( simple_reviews_badge_get_option( 'simple_reviews_badge_api_key' ) ); ?>" placeholder="e.g., AIzaFyWDzmlFDSRE_gSsFtDsAw3" required />
							<p class="description"><?php esc_html_e( 'Enter your Google API key. You can create this in your Google Cloud Console.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Review Badge Image URL', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="simple_reviews_badge_img_src" value="<?php echo esc_url( simple_reviews_badge_get_option( 'simple_reviews_badge_img_src' ) ); ?>" placeholder="e.g., https://example.com/Google/logo.svg" required />
							<p class="description"><?php esc_html_e( 'Enter the URL of the image for the Google Review Badge. Default: ', 'simple-reviews-badge' ) ?><?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/logo.svg' ) ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google Review Link', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="simple_reviews_badge_review_link" value="<?php echo esc_url( simple_reviews_badge_get_option( 'simple_reviews_badge_review_link' ) ); ?>" placeholder="e.g., https://maps.app.goo.gl/example" required />
							<p class="description"><?php esc_html_e( 'Enter the link to your Google Reviews page.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Cache Duration (in seconds)', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="number" name="simple_reviews_badge_cache_duration" value="<?php echo esc_attr( simple_reviews_badge_get_option( 'simple_reviews_badge_cache_duration' ) ); ?>" min="60" placeholder="e.g., 3600" />
							<p class="description"><?php esc_html_e( 'Set the cache duration in seconds. Minimum is 60 seconds.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>

					<!-- Schema Settings -->
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Description', 'simple-reviews-badge' ); ?></th>
						<td>
							<textarea name="simple_reviews_badge_schema_description" rows="3" cols="50" placeholder="e.g., Custom web design services by Alpha Labs."><?php echo esc_textarea( simple_reviews_badge_get_option( 'simple_reviews_badge_schema_description' ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter a description for the schema markup. This should be a brief summary of your business.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Name', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="simple_reviews_badge_schema_name" value="<?php echo esc_attr( simple_reviews_badge_get_option( 'simple_reviews_badge_schema_name' ) ); ?>" placeholder="e.g., Alpha Labs Web Design" />
							<p class="description"><?php esc_html_e( 'Enter the name of your business for the schema markup.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Brand', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="simple_reviews_badge_schema_brand" value="<?php echo esc_attr( simple_reviews_badge_get_option( 'simple_reviews_badge_schema_brand' ) ); ?>" placeholder="e.g., Alpha Labs" />
							<p class="description"><?php esc_html_e( 'Enter the brand of your business for the schema markup.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema ID (URL)', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="simple_reviews_badge_schema_id" value="<?php echo esc_url( simple_reviews_badge_get_option( 'simple_reviews_badge_schema_id' ) ); ?>" placeholder="e.g., https://alphalabs.net/web-design-and-development/" />
							<p class="description"><?php esc_html_e( 'Enter the ID URL for the schema markup.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema URL', 'simple-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="simple_reviews_badge_schema_url" value="<?php echo esc_url( simple_reviews_badge_get_option( 'simple_reviews_badge_schema_url' ) ); ?>" placeholder="e.g., https://alphalabs.net/" />
							<p class="description"><?php esc_html_e( 'Enter the URL for the schema markup.', 'simple-reviews-badge' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
}
?>
