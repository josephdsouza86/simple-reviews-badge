<?php
/**
 * Add settings page to the WordPress admin menu
 *
 * @package Google_Reviews_Badge
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add settings page to the WordPress admin menu
 */
function grb_add_admin_menu() {
	add_options_page(
		__( 'Google Reviews Badge', 'google-reviews-badge' ), // i18n support.
		__( 'Google Reviews Badge', 'google-reviews-badge' ), // i18n support.
		'manage_options',
		'google-reviews-badge',
		'grb_settings_page'
	);
}
add_action( 'admin_menu', 'grb_add_admin_menu' );

/**
 * Settings page content
 */
function grb_settings_page() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Security: Ensure settings have been saved.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'grb_messages', 'grb_message', __( 'Settings Saved', 'google-reviews-badge' ), 'updated' );
	}

	// Display messages.
	settings_errors( 'grb_messages' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Google Reviews Badge Settings', 'google-reviews-badge' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'grb_options_group' ); // Security: nonce for form submission.
				do_settings_sections( 'grb_options_group' );
				?>
				<table class="form-table">
					<!-- Basic Settings -->
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google Place ID', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="grb_place_id" value="<?php echo esc_attr( grb_get_option( 'grb_place_id' ) ); ?>" placeholder="e.g., ChIJuQkUQ5YqdEgRipQ_lAyte_Y" required />
							<p class="description"><?php esc_html_e( 'Enter your Google Place ID. This is used to fetch reviews for your business.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google API Key', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="grb_api_key" value="<?php echo esc_attr( grb_get_option( 'grb_api_key' ) ); ?>" placeholder="e.g., AIzaFyWDzmlFDSRE_gSsFtDsAw3" required />
							<p class="description"><?php esc_html_e( 'Enter your Google API key. You can create this in your Google Cloud Console.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Review Badge Image URL', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="grb_img_src" value="<?php echo esc_url( grb_get_option( 'grb_img_src' ) ); ?>" placeholder="e.g., https://cdn.trustindex.io/assets/platform/Google/logo.svg" required />
							<p class="description"><?php esc_html_e( 'Enter the URL of the image for the Google Review Badge.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Google Review Link', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="grb_review_link" value="<?php echo esc_url( grb_get_option( 'grb_review_link' ) ); ?>" placeholder="e.g., https://maps.app.goo.gl/example" required />
							<p class="description"><?php esc_html_e( 'Enter the link to your Google Reviews page.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Cache Duration (in seconds)', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="number" name="grb_cache_duration" value="<?php echo esc_attr( grb_get_option( 'grb_cache_duration' ) ); ?>" min="60" placeholder="e.g., 3600" />
							<p class="description"><?php esc_html_e( 'Set the cache duration in seconds. Minimum is 60 seconds.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>

					<!-- Schema Settings -->
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Description', 'google-reviews-badge' ); ?></th>
						<td>
							<textarea name="grb_schema_description" rows="3" cols="50" placeholder="e.g., Custom web design services by Alpha Labs."><?php echo esc_textarea( grb_get_option( 'grb_schema_description' ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter a description for the schema markup. This should be a brief summary of your business.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Name', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="grb_schema_name" value="<?php echo esc_attr( grb_get_option( 'grb_schema_name' ) ); ?>" placeholder="e.g., Alpha Labs Web Design" />
							<p class="description"><?php esc_html_e( 'Enter the name of your business for the schema markup.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema Brand', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="text" name="grb_schema_brand" value="<?php echo esc_attr( grb_get_option( 'grb_schema_brand' ) ); ?>" placeholder="e.g., Alpha Labs" />
							<p class="description"><?php esc_html_e( 'Enter the brand of your business for the schema markup.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema ID (URL)', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="grb_schema_id" value="<?php echo esc_url( grb_get_option( 'grb_schema_id' ) ); ?>" placeholder="e.g., https://alphalabs.net/web-design-and-development/" />
							<p class="description"><?php esc_html_e( 'Enter the ID URL for the schema markup.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Schema URL', 'google-reviews-badge' ); ?></th>
						<td>
							<input type="url" name="grb_schema_url" value="<?php echo esc_url( grb_get_option( 'grb_schema_url' ) ); ?>" placeholder="e.g., https://alphalabs.net/" />
							<p class="description"><?php esc_html_e( 'Enter the URL for the schema markup.', 'google-reviews-badge' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
}
?>
