<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://merchr.com
 * @since      1.0.0
 *
 * @package    Merchr
 */

// If uninstall not called from WordPress, then exit.
if(!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Check is an admin user, if not then exit
if(!current_user_can('manage_options')) {
    exit;
}

// Check that $_REQUEST['plugin'] matches this plug-in, if not then exit
if($_REQUEST['plugin'] != 'merchr/merchr.php') {
	exit;
}

// Set merchr hub option name
$merchr_hub_option_name = 'merchr_hub_options';

// Fetch merchr hub options first to check if custom tables and data are to be dropped
$merchr_hub_options = get_option($merchr_hub_option_name);

// Check if to remove data and custom tables
if($merchr_hub_options['merchr_hub_remove_data_on_uninstall'] != 'no') {
	// Set list of all plug-in option names
	$option_names = [
		$merchr_hub_option_name,
		'merchrcust_default_custom_label',
		'merchrcust_default_custom_text',
		'merchrcust_default_custom_fallback_text',
		'merchrcust_default_customise_all_label',
		'merchrcust_public_api_key'
	];

	// Delete the option records
	foreach($option_names as $name) {
		delete_option($name);
	}

	// Remove the tables.
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_failed_import_list");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_import_list");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_import_products");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_order_items");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_orders");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}merchr_hub_import_products_taxonomy");
}
