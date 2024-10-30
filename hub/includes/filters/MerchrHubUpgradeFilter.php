<?php
/**
 * Merchr Hub Upgrade Filter Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/hooks
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\filters;

use MerchrHub\includes\MerchrHubFilters;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubUpgradeFilter extends MerchrHubFilters
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Pre Upgrade.
	 *
	 * @param int
	 */
	public function preUprgrade()
	{
		// Set key file
		$key_file = MERCHR_HUB_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'MerchrHubKey.php';
        
        if(file_exists($key_file)) {
            // Extract contents and base 64 encode
            $contents = base64_encode(@file_get_contents($key_file));
        
            // Create option
            add_option('merchr_hub_backup', $contents);
        }
        
        return;
	}
}
