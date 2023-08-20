<?php
/*
Plugin Name: Precincts Listing Plugin
Description: A plugin to manage precinct information for a political committee.
Version: 1.0
Author: Ricardo Alfaro
*/

// Load the required files for WP_List_Table
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once(ABSPATH . 'wp-admin/includes/screen.php');

// Define plugin constants
define('CUSTOM_PRECINCTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOM_PRECINCTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Create the database table when activating the plugin
function create_precincts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_precincts';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        precinct_number INT NOT NULL,
        precinct_name VARCHAR(255) NOT NULL,
        precinct_captain VARCHAR(255) NOT NULL,
        precinct_deputy VARCHAR(255) NOT NULL,
        precinct_location VARCHAR(255) NOT NULL,
        precinct_region VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_precincts_table');

// Delete the database table when deactivating the plugin
function delete_precincts_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_precincts';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'delete_precincts_table');

// Enqueue scripts and styles
function custom_precincts_enqueue_scripts() {
    wp_enqueue_style('custom-precincts-style', CUSTOM_PRECINCTS_PLUGIN_URL . 'styles.css');
}
add_action('admin_enqueue_scripts', 'custom_precincts_enqueue_scripts');

// Create the admin menu
function custom_precincts_menu() {
    add_menu_page(
        'Precincts',
        'Precincts',
        'manage_options',
        'custom-precincts',
        'custom_precincts_page',
        'dashicons-admin-multisite'
    );
}
add_action('admin_menu', 'custom_precincts_menu');

// Ensure that Bootstrap is enabled to enhance the administrative interface
function enqueue_bootstrap_assets() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.1', true);
}
add_action('admin_enqueue_scripts', 'enqueue_bootstrap_assets');

// Create the "Precinct Vice Chair" user role
function add_precinct_vice_chair_role() {
    add_role('precinct_vice_chair', 'Precinct Vice Chair');
}
register_activation_hook(__FILE__, 'add_precinct_vice_chair_role');

// Check if the current user has permission to access the form
function has_access_to_precinct_form() {
    return current_user_can('activate_plugins') || current_user_can('precinct_vice_chair');
}

require_once(plugin_dir_path(__FILE__) . 'class-custom-precincts-table.php');

// Callback for the admin page
function custom_precincts_page() {
    require_once(plugin_dir_path(__FILE__) . 'page-precincts.php');
}

// Add precinct
function custom_precincts_add_precinct() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_precincts';

    $precinct_number = sanitize_text_field($_POST['precinct_number']);
    $precinct_name = sanitize_text_field($_POST['precinct_name']);
    $precinct_captain = sanitize_text_field($_POST['precinct_captain']);
    $precinct_deputy = sanitize_text_field($_POST['precinct_deputy']);
    $precinct_location = sanitize_text_field($_POST['precinct_location']);
    $precinct_region = sanitize_text_field($_POST['precinct_region']);
    
    if (!empty($precinct_number)) {
        $wpdb->insert(
            $table_name,
            array(
                'precinct_number' => $precinct_number,
                'precinct_name' => $precinct_name,
                'precinct_captain' => $precinct_captain,
                'precinct_deputy' => $precinct_deputy,
                'precinct_location' => $precinct_location,
                'precinct_region' => $precinct_region,
            )
        );        
        wp_send_json_success();
    }    
    wp_send_json_error();
}

add_action('wp_ajax_add_precinct', 'custom_precincts_add_precinct');
add_action('wp_ajax_nopriv_add_precinct', 'custom_precincts_add_precinct');

// Modify precinct
function custom_precincts_update_precinct() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_precincts';

    $entry_id = sanitize_text_field($_POST['entry_id']);
    $precinct_number = sanitize_text_field($_POST['precinct_number']);
    $precinct_name = sanitize_text_field($_POST['precinct_name']);
    $precinct_captain = sanitize_text_field($_POST['precinct_captain']);
    $precinct_deputy = sanitize_text_field($_POST['precinct_deputy']);
    $precinct_location = sanitize_text_field($_POST['precinct_location']);
    $precinct_region = sanitize_text_field($_POST['precinct_region']);
    
    if (!empty($precinct_number)) {
        $wpdb->update(
            $table_name,
            array(
                'precinct_number' => $precinct_number,
                'precinct_name' => $precinct_name,
                'precinct_captain' => $precinct_captain,
                'precinct_deputy' => $precinct_deputy,
                'precinct_location' => $precinct_location,
                'precinct_region' => $precinct_region,
            ),
            array(
                'id'=>$entry_id
            )
        );        
        wp_send_json_success();
    }    
    wp_send_json_error();
}
add_action('wp_ajax_update_precinct', 'custom_precincts_update_precinct');
add_action('wp_ajax_nopriv_update_precinct', 'custom_precincts_update_precinct');

// Delete precinct
function custom_precincts_delete_precinct() {
    // Check if the request is valid
    if (
        !isset($_POST['entry_id']) ||
        !isset($_POST['nonce']) ||
        !wp_verify_nonce($_POST['nonce'], 'delete_precinct_' . $_POST['entry_id'])
    ) {
        wp_send_json_error(__('Failed to delete precinct.', 'custom_precincts'), null, $error_message);
    } else {
        global $wpdb;

        $entry_id = $_POST['entry_id'];

        $table_name = $wpdb->prefix . 'custom_precincts';

        // Delete the attendee
        $result = $wpdb->delete($table_name, ['id' => $entry_id], ['%d']);

        // Return success or failure
        if ($result == false) {
            wp_send_json_error(__('Failed to delete precinct.', 'custom_precincts'), null, $error_message);
        } else {
            wp_send_json_success();
        }
    }
}

add_action('wp_ajax_delete_precinct', 'custom_precincts_delete_precinct');
add_action('wp_ajax_nopriv_delete_precinct', 'custom_precincts_delete_precinct');

function custom_precincts_get_precinct_data() {
    if (isset($_GET['entry_id']) && !empty($_GET['entry_id'])) {
        $entry_id = intval($_GET['entry_id']);

        $data = get_precinct_data_from_database($entry_id);

        // Return the data as a JSON-encoded response
        wp_send_json($data);
    } else {
        wp_send_json_error('Invalid entry ID');
    }
}
add_action('wp_ajax_get_precinct_data', 'custom_precincts_get_precinct_data');
add_action('wp_ajax_nopriv_get_precinct_data', 'custom_precincts_get_precinct_data');

// Retrieve existing precinct for editing() {
function get_precinct_data_from_database($entry_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'custom_precincts';

    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $entry_id);
    $data = $wpdb->get_row($query, ARRAY_A);

    return $data;
}

// Shortcode to display precinct data
function custom_precincts_shortcode() {
    // Retrieve data from your custom database table
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_precincts';

    $query = "SELECT * FROM $table_name ORDER BY precinct_number ASC";
    $precincts = $wpdb->get_results($query, ARRAY_A);

    include 'template.php';

    return $precincts;
}
add_shortcode('custom_precincts', 'custom_precincts_shortcode');

?>