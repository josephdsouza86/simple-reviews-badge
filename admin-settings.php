<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add settings page to the WordPress admin menu
 */
function grb_add_admin_menu() {
    add_options_page(
        'Google Reviews Badge', 
        'Google Reviews Badge', 
        'manage_options', 
        'google-reviews-badge', 
        'grb_settings_page'
    );
}
add_action('admin_menu', 'grb_add_admin_menu');

/**
 * Settings page content
 */
function grb_settings_page() {
    ?>
    <div class="wrap">
        <h1>Google Reviews Badge Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('grb_options_group');
            do_settings_sections('grb_options_group');
            ?>
            <table class="form-table">
                <!-- Basic Settings -->
                <tr valign="top">
                    <th scope="row">Google Place ID</th>
                    <td><input type="text" name="grb_place_id" value="<?php echo esc_attr(get_option('grb_place_id')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Google API Key</th>
                    <td><input type="text" name="grb_api_key" value="<?php echo esc_attr(get_option('grb_api_key')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Review Badge Image URL</th>
                    <td><input type="url" name="grb_img_src" value="<?php echo esc_url(get_option('grb_img_src')); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Cache Duration (in seconds)</th>
                    <td><input type="number" name="grb_cache_duration" value="<?php echo esc_attr(get_option('grb_cache_duration', HOUR_IN_SECONDS)); ?>" min="60" /></td>
                </tr>
                
                <!-- Schema Settings -->
                <tr valign="top">
                    <th scope="row">Include Schema Markup</th>
                    <td>
                        <input type="checkbox" name="grb_include_schema" value="1" <?php checked(1, get_option('grb_include_schema'), true); ?> />
                        <label for="grb_include_schema">Enable Schema Markup</label>
                    </td>
                </tr>
                <?php if (get_option('grb_include_schema')): ?>
                    <tr valign="top">
                        <th scope="row">Schema Description</th>
                        <td><textarea name="grb_schema_description" rows="3" cols="50"><?php echo esc_textarea(get_option('grb_schema_description')); ?></textarea></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Schema Name</th>
                        <td><input type="text" name="grb_schema_name" value="<?php echo esc_attr(get_option('grb_schema_name')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Schema Brand</th>
                        <td><input type="text" name="grb_schema_brand" value="<?php echo esc_attr(get_option('grb_schema_brand')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Schema ID (URL)</th>
                        <td><input type="url" name="grb_schema_id" value="<?php echo esc_url(get_option('grb_schema_id')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Schema URL</th>
                        <td><input type="url" name="grb_schema_url" value="<?php echo esc_url(get_option('grb_schema_url')); ?>" /></td>
                    </tr>
                <?php endif; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        // JavaScript to show/hide schema fields based on the checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.querySelector('input[name="grb_include_schema"]');
            const schemaFields = [
                'tr[valign="top"]:nth-child(6)', // Description
                'tr[valign="top"]:nth-child(7)', // Name
                'tr[valign="top"]:nth-child(8)', // Brand
                'tr[valign="top"]:nth-child(9)', // ID
                'tr[valign="top"]:nth-child(10)' // URL
            ];
            
            function toggleSchemaFields() {
                schemaFields.forEach(function(selector) {
                    const field = document.querySelector(selector);
                    if (field) {
                        field.style.display = checkbox.checked ? '' : 'none';
                    }
                });
            }

            toggleSchemaFields(); // Initial call
            checkbox.addEventListener('change', toggleSchemaFields);
        });
    </script>
    <?php
}
?>
