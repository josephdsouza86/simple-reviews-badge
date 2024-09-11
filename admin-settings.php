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
					<td><input type="text" name="grb_place_id" value="<?php echo esc_attr( grb_get_option( 'grb_place_id' ) ); ?>" required /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Google API Key', 'google-reviews-badge' ); ?></th>
					<td><input type="text" name="grb_api_key" value="<?php echo esc_attr( grb_get_option( 'grb_api_key' ) ); ?>" required /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Review Badge Image URL', 'google-reviews-badge' ); ?></th>
					<td><input type="url" name="grb_img_src" value="<?php echo esc_url( grb_get_option( 'grb_img_src' ) ); ?>" required /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Google Review Link', 'google-reviews-badge' ); ?></th>
					<td><input type="url" name="grb_review_link" value="<?php echo esc_url( grb_get_option( 'grb_review_link' ) ); ?>" required /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Cache Duration (in seconds)', 'google-reviews-badge' ); ?></th>
					<td><input type="number" name="grb_cache_duration" value="<?php echo esc_attr( grb_get_option( 'grb_cache_duration' ) ); ?>" min="60" /></td>
				</tr>
				
				<!-- Schema Settings -->
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Schema Description', 'google-reviews-badge' ); ?></th>
					<td><textarea name="grb_schema_description" rows="3" cols="50"><?php echo esc_textarea( grb_get_option( 'grb_schema_description' ) ); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Schema Name', 'google-reviews-badge' ); ?></th>
					<td><input type="text" name="grb_schema_name" value="<?php echo esc_attr( grb_get_option( 'grb_schema_name' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Schema Brand', 'google-reviews-badge' ); ?></th>
					<td><input type="text" name="grb_schema_brand" value="<?php echo esc_attr( grb_get_option( 'grb_schema_brand' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Schema ID (URL)', 'google-reviews-badge' ); ?></th>
					<td><input type="url" name="grb_schema_id" value="<?php echo esc_url( grb_get_option( 'grb_schema_id' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Schema URL', 'google-reviews-badge' ); ?></th>
					<td><input type="url" name="grb_schema_url" value="<?php echo esc_url( grb_get_option( 'grb_schema_url' ) ); ?>" /></td>
				</tr>
			</table>
			<?php submit_button(); // Default submit button with nonce. ?>
		</form>
	</div>
	<?php
}
?>
