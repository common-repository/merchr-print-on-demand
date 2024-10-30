<?php
/**
 * Merchr Hub Helpers Upgrade Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersUpgrade
{
	/**
	 * Check For And Run Upgrades
	 *
	 * @Param string version (plug-in)
     * @param string version (db)
	 *
	 * @return bool
	 */
	public static function checkForAndRunUpgrades(string $version, string $version_db)
	{
        global $wpdb;
		$prefix = $wpdb->prefix;
        
        // Set charset and collate
        $charset_collate = '';
        if(!empty($wpdb->charset)) {
            $charset_collate = "CHARACTER SET {$wpdb->charset}";
        }
        if(!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
        
        $version_check = str_replace('.', '_', $version);
        $version_db_check = str_replace('.', '_', $version_db);
        
        // Check for version update files
        $upgrade_path = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR;
        
        // Plug-in Updates
        if(file_exists("{$upgrade_path}version_{$version_check}.php")) {
            require_once("{$upgrade_path}version_{$version_check}.php");
            $plugin_upgrade = new \MerchrHub\includes\updates\MerchrUpgrade(
                $wpdb, 
                $prefix, 
                $charset_collate
            );
            $plugin_upgrade->run();
        }
        
        // Database Updates
        if(file_exists("{$upgrade_path}db_{$version_db_check}.php")) {
            require_once("{$upgrade_path}db_{$version_db_check}.php");
            $database_upgrade = new \MerchrHub\includes\updates\MerchrUpgradeDatabase(
                $wpdb, 
                $prefix, 
                $charset_collate
            );
            $database_upgrade->run();
        }
        
        return true;
    }
}
