<?php
/*
Plugin Name: Delete Inactive Themes and Extensions
Description: A plugin to delete selected inactive themes and plugins except the active ones.
Version: 1.0.1
Author: Mahedi Hasan
Author URI: https://mahedi.whizbd.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: delete-inactive-themes-extensions
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('admin_menu', 'ditp_add_admin_menu');

function ditp_add_admin_menu() {
    add_menu_page(
        __('Delete Inactive Themes and Plugins', 'delete-inactive-themes-plugins'), 
        __('Delete Inactive Items', 'delete-inactive-themes-plugins'), 
        'manage_options', 
        'delete-inactive-items', 
        'ditp_admin_page', 
        'dashicons-trash', 
        100 
    );
}

function ditp_admin_page() {
    
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle form submission
    if (isset($_POST['ditp_delete_items'])) {
        ditp_delete_selected_items();
    }

    // Display the admin page
    ?>
    <div class="wrap">
        <h1><?php _e('Delete Inactive Themes and Plugins', 'delete-inactive-themes-plugins'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('ditp_delete_items_nonce', 'ditp_delete_items_nonce'); ?>
            <p><?php _e('Select the themes and plugins you want to delete and click the button below.', 'delete-inactive-themes-plugins'); ?></p>
            
            <h2><?php _e('Themes', 'delete-inactive-themes-plugins'); ?></h2>
            <p><input type="checkbox" id="select-all-themes"> <label for="select-all-themes"><?php _e('Select All Themes', 'delete-inactive-themes-plugins'); ?></label></p>
            <table class="form-table">
                <tbody>
                    <?php
                    // Get the current theme
                    $current_theme = wp_get_theme();
                    $current_theme_slug = $current_theme->get_stylesheet();

                    // Get all themes
                    $all_themes = wp_get_themes();

                    foreach ($all_themes as $theme_slug => $theme) {
                        $is_active = ($theme_slug === $current_theme_slug);
                        ?>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" name="themes_to_delete[]" value="<?php echo esc_attr($theme_slug); ?>" <?php echo $is_active ? 'disabled' : ''; ?>>
                            </th>
                            <td>
                                <?php echo esc_html($theme->get('Name')); ?>
                                <?php if ($is_active) echo ' <strong>' . __('(Active theme)', 'delete-inactive-themes-plugins') . '</strong>'; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            
            <h2><?php _e('Plugins', 'delete-inactive-themes-plugins'); ?></h2>
            <p><input type="checkbox" id="select-all-plugins"> <label for="select-all-plugins"><?php _e('Select All Plugins', 'delete-inactive-themes-plugins'); ?></label></p>
            <table class="form-table">
                <tbody>
                    <?php
                    // Get all plugins
                    $all_plugins = get_plugins();
                    $active_plugins = get_option('active_plugins', []);
                    
                    foreach ($all_plugins as $plugin_path => $plugin) {
                        $is_active = in_array($plugin_path, $active_plugins);
                        ?>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" name="plugins_to_delete[]" value="<?php echo esc_attr($plugin_path); ?>" <?php echo $is_active ? 'disabled' : ''; ?>>
                            </th>
                            <td>
                                <?php echo esc_html($plugin['Name']); ?>
                                <?php if ($is_active) echo ' <strong>' . __('(Active plugin)', 'delete-inactive-themes-plugins') . '</strong>'; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            
            <p><button type="submit" name="ditp_delete_items" class="button button-primary"><?php _e('Delete Selected Items', 'delete-inactive-themes-plugins'); ?></button></p>
        </form>
    </div>
    <script type="text/javascript">
        document.getElementById('select-all-themes').addEventListener('click', function(event) {
            var checkboxes = document.querySelectorAll('input[name="themes_to_delete[]"]');
            for (var checkbox of checkboxes) {
                if (!checkbox.disabled) {
                    checkbox.checked = event.target.checked;
                }
            }
        });

        document.getElementById('select-all-plugins').addEventListener('click', function(event) {
            var checkboxes = document.querySelectorAll('input[name="plugins_to_delete[]"]');
            for (var checkbox of checkboxes) {
                if (!checkbox.disabled) {
                    checkbox.checked = event.target.checked;
                }
            }
        });
    </script>
    <?php
}

function ditp_delete_selected_items() {
    // Verify nonce for security
    if (!isset($_POST['ditp_delete_items_nonce']) || !wp_verify_nonce($_POST['ditp_delete_items_nonce'], 'ditp_delete_items_nonce')) {
        return;
    }

    
    if (isset($_POST['themes_to_delete']) && is_array($_POST['themes_to_delete'])) {
       
        foreach ($_POST['themes_to_delete'] as $theme_slug) {
            delete_theme(sanitize_text_field($theme_slug));
        }
    }

    
    if (isset($_POST['plugins_to_delete']) && is_array($_POST['plugins_to_delete'])) {
        
        foreach ($_POST['plugins_to_delete'] as $plugin_path) {
            delete_plugins([sanitize_text_field($plugin_path)]);
        }
    }

    // Show a success message
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Selected themes and plugins have been deleted.', 'delete-inactive-themes-plugins') . '</p></div>';
}
?>
