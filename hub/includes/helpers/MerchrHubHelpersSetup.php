<?php
/**
 * Merchr Hub Helpers Setup Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersSetup
{
	public static $woocommerce_plugin_path = 'woocommerce/woocommerce.php';
	public static $active_sitewide_plugins_key = 'active_sitewide_plugins';
	
	/**
	 * Is Merchr Plug-in Active.
	 *
	 * The functions are not available for our init.php 
	 * script, so we need to check for this manually.
	 *
	 * @return bool
	 */
	public static function isMerchrHubPluginActive()
	{
		$result = false;
		$plugin_path = trailingslashit(WP_PLUGIN_DIR) . MERCHR_PLUGIN_BASENAME;
		$active_network_plugins = get_site_option(self::$active_sitewide_plugins_key, []);
		
		if(
			in_array($plugin_path, wp_get_active_and_valid_plugins())
			|| in_array($plugin_path, $active_network_plugins)
		) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * Simple check to see if Merchr Hub connected and products imported.
	 *
	 * @param array
	 *
	 * @return bool
	 */
	public static function merchrHubSetupCompleted(array $options)
	{
		$result = true;
		if($options['merchr_hub_connected'] == 'no' || $options['merchr_hub_products_imported'] == 'no') {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * Check If Store Connected.
	 *
	 * @param array
	 *
	 * @return bool
	 */
	public static function merchrHubStoreConnected(array $options)
	{
		$result = true;
		if($options['merchr_hub_connected'] == 'no') {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * Check If Products Imported.
	 *
	 * @param array
	 *
	 * @return bool
	 */
	public static function merchrHubProductsImported(array $options)
	{
		$result = true;
		if($options['merchr_hub_products_imported'] == 'no') {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * Check if WooCommerce Plug-in is active
	 *
	 * @return bool
	 */
	public static function checkWooCommerceActive()
	{
		$result = false;
		
		// Test to see if WooCommerce is active (including network activated).
		$plugin_path = trailingslashit(WP_PLUGIN_DIR) . self::$woocommerce_plugin_path;
		$active_network_plugins = get_site_option(self::$active_sitewide_plugins_key, []);
		
		// Check single site active plug-ins
		// We need to also check active network plug-ins, 
		// we can't use WordPress' built in function 
		// wp_get_active_network_plugins() as we call this method
		// before it is available in WordPress, so we will check
		// for it manually.
		if(
			in_array($plugin_path, wp_get_active_and_valid_plugins())
			|| in_array($plugin_path, $active_network_plugins)
		) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * Is First Store Owner Login
	 *
	 * @param array
	 */
	public static function isFirstStoreOwnerLogin(array $options)
	{
		$user_id = get_current_user_id();
		if(isset($options['merchr_internal_install'])) {
			if($options['merchr_internal_install'] == 1 && $user_id > 1) {
				$options['merchr_internal_install'] = 0;
				update_option('merchr_hub_options', $options);
				exit(wp_redirect(admin_url('admin.php?page=' . $this->setup_slug)));
			}
		}
	}
    
    /**
	 * Check For New Version
	 *
	 * @param array
     * @return array
	 */
	public static function checkForNewVersion(array $options, string $version, string $version_db)
	{
		if(
            version_compare($version, $options['merchr_hub_version'], '>') 
            || version_compare($version_db, $options['merchr_hub_version_db'], '>')
        ) {
            // Before versioning up, check for upgrades
            MerchrHubHelpersUpgrade::checkForAndRunUpgrades($version, $version_db);
            
            $options['merchr_hub_version'] = $version;
            $options['merchr_hub_version_db'] = $version_db;
            update_option('merchr_hub_options', $options);
        }
        return $options;
	}
}
