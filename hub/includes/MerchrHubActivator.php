<?php
/**
 * Merchr Hub Activator Class.
 *
 * @since      1.0.1
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use Defuse\Crypto\Key;
use MerchrHub\includes\helpers\MerchrHubHelpersSetup;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubActivator
{
	use MerchrHubDatabaseInteraction;
	
	/**
	 * Assign $wpdb.
	 */
	public function __construct() 
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
	}
	
	/**
	 * Method called on plug-in activation.
	 */
	public function activate() 
	{
		// Test for WooCommerce plug-in first, 
		// prevent activation if not present or active
		$this->isWooCommerceActive();
		
		// Create import tables
		$this->createTables();
		
		// Create Merchr Hub Options
		$this->createMerchrHubOptions();
		
		// If encryption key does not exists, create it
		$this->createEncryptionKey();
	}
	
	/**
	 * Is Woo CommerceActive.
	 */
	public function isWooCommerceActive() 
	{
		if(!MerchrHubHelpersSetup::checkWooCommerceActive()) {
			$install_url = admin_url('plugin-install.php?s=WooCommerce&tab=search&type=term');
			$install_text = __('click here to install WooCommerce', 'merchr');
			$install_link = '<a href="' . $install_url . '" target="_parent">' . $install_text . '</a>';
			$error_msg = sprintf(__('WooCommerce is required for the Merchr Plug-in, %s', 'merchr'), $install_link);
			die(__('WooCommerce Plugin has NOT been activated: ', 'merchr') . $error_msg);
		}
	}
	
	/**
	 * Method called on plug-in activated.
	 *
	 * @param string
	 */
	public function activated(string $plugin) 
	{
		if($plugin == MERCHR_PLUGIN_BASENAME) {
			exit(wp_redirect(admin_url('admin.php?page=merchr-hub-setup')));
		}
	}
	
	/**
	 * Method for creating the needed database tables.
	 */
	private function createTables() 
	{
		// Set table charset and collate and table names
		$table_charset_collate   = $this->wpdb->get_charset_collate();
		$import_products_table   = $this->merchr_tables->products;
		$import_taxonomies_table = $this->merchr_tables->taxonomies;
		$orders_table            = $this->merchr_tables->orders;
		$order_items_table       = $this->merchr_tables->order_items;
		$import_queue_table      = $this->merchr_tables->queue;
		$failed_imports_table    = $this->merchr_tables->failed_imports;
		
		// Imported products table
		$sql = "CREATE TABLE IF NOT EXISTS `{$import_products_table}` (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`import_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
			`merchr_product_id` bigint(20) UNSIGNED NOT NULL,
			`merchr_store_product_id` bigint(20) UNSIGNED DEFAULT NULL,
			`merchr_product_sku` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} DEFAULT NULL,
			`market_place_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
			`product_type` enum('merchr','store') CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
			`product_title` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
			`product_description` text CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
			`product_price` decimal(10, 2) NOT NULL,
			`product_cost` decimal(10, 2) NOT NULL,
			`product_fee` decimal(10, 2) NOT NULL,
			`product_img` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
			`product_customisable` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			`product_categories` longtext CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
			`product_collections` longtext CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
			`product_industries` longtext CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
			`accept_negative_profit` int(1) UNSIGNED NOT NULL DEFAULT 0,
			`json_content` longtext CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
			`imported` int(1) UNSIGNED NOT NULL DEFAULT 0,
			`import_queued` int(1) UNSIGNED NOT NULL DEFAULT 0,
			`created_at` datetime NOT NULL,
			`imported_at` datetime NULL DEFAULT NULL,
			`updated_at` datetime NULL ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`) USING BTREE,
			INDEX `merchr_product_id`(`merchr_product_id`) USING BTREE,
			INDEX `merchr_store_product_id`(`merchr_store_product_id`) USING BTREE,
			INDEX `merchr_product_sku`(`merchr_product_sku`) USING BTREE,
			INDEX `market_place_id`(`market_place_id`) USING BTREE
		) {$table_charset_collate};";
		
		// Product taxonomies from the Merchr Hub
		$sql2 = "CREATE TABLE IF NOT EXISTS `{$import_taxonomies_table}` (
		  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `taxonomy_id` bigint(20) UNSIGNED NOT NULL,
		  `parent_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
		  `type` enum('category','collection','industry') CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
		  `name` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
		  PRIMARY KEY (`id`) USING BTREE,
		  INDEX `taxonomy_id`(`taxonomy_id`, `type`) USING BTREE
		) {$table_charset_collate};";
		
		// Orders table
		$sql3 = "CREATE TABLE IF NOT EXISTS `{$orders_table}`  (
		  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `order_id` bigint(20) UNSIGNED NOT NULL,
		  `customer_id` bigint(20) UNSIGNED NULL,
          `merchr_order_id` bigint(20) UNSIGNED NULL,
		  `merchr_customer_id` bigint(20) UNSIGNED NULL,
		  `merchr_customer_address_id` bigint(20) UNSIGNED NULL,
		  `processed` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `success` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `failed` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `retried` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `cancelled` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `completed` int(1) UNSIGNED NOT NULL DEFAULT 0,
		  `order_total` double(8, 2) UNSIGNED NOT NULL,
		  `status` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL DEFAULT 'new',
		  `notes` text NULL,
		  `date_created` datetime NOT NULL,
		  `date_updated` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`) USING BTREE,
		  UNIQUE INDEX `order_id`(`order_id`) USING BTREE,
		  INDEX `success`(`success`) USING BTREE
		) {$table_charset_collate};";
		
		// Order items
		$sql4 = "CREATE TABLE IF NOT EXISTS `{$order_items_table}`  (
		  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `order_id` bigint(20) UNSIGNED NOT NULL,
		  `merchr_order_id` bigint(20) UNSIGNED NOT NULL,
		  `order_item_id` bigint(20) UNSIGNED NOT NULL,
		  `merchr_order_item_id` bigint(20) UNSIGNED NOT NULL,
		  `date_created` datetime NOT NULL,
		  PRIMARY KEY (`id`) USING BTREE
		) {$table_charset_collate};";
		
		// Import queue table
		$sql5 = "CREATE TABLE IF NOT EXISTS `{$import_queue_table}`  (
		  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `iniator_email` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
		  `initiator_name` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
		  `products_json` longtext CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
		  `product_quantity` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
		  `product_quantity_processed` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
		  `processed` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
		  `processing` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
		  `failed` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
		  `created_at` datetime NOT NULL,
		  `updated_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`) USING BTREE
		) {$table_charset_collate};";
		
		$sql6 = "CREATE TABLE IF NOT EXISTS `{$failed_imports_table}`  (
		  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  `import_id` bigint(20) UNSIGNED NOT NULL,
		  `product_import_id` bigint(20) UNSIGNED NOT NULL,
		  `product_name` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NOT NULL,
		  `failed_reason` varchar(255) CHARACTER SET {$this->wpdb->charset} COLLATE {$this->wpdb->collate} NULL DEFAULT NULL,
		  `created_at` datetime NOT NULL,
		  PRIMARY KEY (`id`) USING BTREE
		) {$table_charset_collate};";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		dbDelta($sql2);
		dbDelta($sql3);
		dbDelta($sql4);
		dbDelta($sql5);
		dbDelta($sql6);
	}
	
	/**
	 * Method for creating the Merchr Hub Options.
	 *
	 * We pass over an array of options in which WordPress 
	 * automatically serialises the data on save and 
	 * de-serialises the data and returns an array when 
	 * calling get_option.
	 */
	private function createMerchrHubOptions() 
	{
		$option_name = 'merchr_hub_options';
		if(!get_option($option_name)) {
			$options = [
				'merchr_hub_version'                  => MERCHR_PLUGIN_VERSION,
				'merchr_hub_version_db'               => MERCHR_PLUGIN_DATABASE_VERSION,
				'merchr_hub_remove_data_on_uninstall' => 'no',
				'merchr_hub_connected'     	          => 'no',
				'merchr_hub_products_imported'        => 'no',
				'merchr_hub_connection_method'        => 'cURL',
				'merchr_hub_api_key'                  => '',
				'merchr_hub_store_id'                 => '',
				'merchr_hub_user_id'                  => '',
				'merchr_hub_prices_include_tax'       => 'yes',
				'merchr_hub_description_location'     => 'full',
			];
			add_option($option_name, $options);
		}
	}
	
	/**
	 * Method for creating the Merchr Hub encryption key.
	 */
	private function createEncryptionKey() 
	{
		// Check if we have a key file
		$key_file = MERCHR_HUB_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'MerchrHubKey.php';
		
		// Check file exists
		if(!file_exists($key_file)) {
			$backup = get_option('merchr_hub_backup');
            
            if($backup === false) {
                $key_file_template = MERCHR_HUB_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'merchr_hub_key.php';
                $key = Key::createNewRandomKey();
                $asciiSafeKey = $key->saveToAsciiSafeString();
                $key_file_contents = @file_get_contents($key_file_template);
                $new_key_file_contents = str_replace('$key = \'\';', '$key = \'' . $asciiSafeKey . '\';', $key_file_contents);
                @file_put_contents($key_file, $new_key_file_contents, LOCK_EX);
                
                // Delete backup
                delete_option('merchr_hub_backup');
            } else {
                @file_put_contents($key_file, base64_decode($backup), LOCK_EX);
            }
		} else {
			$stored_key = MerchrHubKey::returnAsciiSafeKey();
			if($stored_key === '') {
				$key = Key::createNewRandomKey();
				$asciiSafeKey = $key->saveToAsciiSafeString();
				$key_file_contents = @file_get_contents($key_file);
				$new_key_file_contents = str_replace('$key = \'\';', '$key = \'' . $asciiSafeKey . '\';', $key_file_contents);
				@file_put_contents($key_file, $new_key_file_contents, LOCK_EX);
			}
		}
	}
}
