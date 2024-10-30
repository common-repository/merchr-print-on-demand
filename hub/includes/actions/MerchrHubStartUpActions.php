<?php
/**
 * Merchr Hub Start Up Actions Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use Defuse\Crypto\Key;
use MerchrHub\includes\MerchrHubActions;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubStartUpActions extends MerchrHubActions
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Check Key File.
	 *
	 * @param int
	 */
	public function checkKeyFile()
	{
		// Set key file
		$key_file = MERCHR_HUB_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'MerchrHubKey.php';
        
        // Check if we have a key file
        if(!file_exists($key_file)) {
            // Check if we have a back up
            $backup = get_option('merchr_hub_backup');
            
            if($backup !== false) {
                @file_put_contents($key_file, base64_decode($backup), LOCK_EX);
                
                // Delete backup
                delete_option('merchr_hub_backup');
            } else {
                $key_file_template = MERCHR_HUB_INCLUDE_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'merchr_hub_key.php';
                $key = Key::createNewRandomKey();
                $asciiSafeKey = $key->saveToAsciiSafeString();
                $key_file_contents = @file_get_contents($key_file_template);
                $new_key_file_contents = str_replace('$key = \'\';', '$key = \'' . $asciiSafeKey . '\';', $key_file_contents);
                @file_put_contents($key_file, $new_key_file_contents, LOCK_EX);
            }
        }
        
        // Do a final check to ensure we have a key
        $stored_key = \MerchrHub\includes\MerchrHubKey::returnAsciiSafeKey();
		if($stored_key === '') {
			$key = Key::createNewRandomKey();
			$asciiSafeKey = $key->saveToAsciiSafeString();
			$key_file_contents = @file_get_contents($key_file);
			$new_key_file_contents = str_replace('$key = \'\';', '$key = \'' . $asciiSafeKey . '\';', $key_file_contents);
			@file_put_contents($key_file, $new_key_file_contents, LOCK_EX);
		}
        
        return;
	}
    
    /**
	 * Check options.
	 */
	public function checkOptions()
	{
		$update = false;
		$options = get_option('merchr_hub_options');
		if(!isset($options['merchr_hub_prices_include_tax'])) {
			$options['merchr_hub_prices_include_tax'] = 'yes';
			$update = true;
		}
		if(!isset($options['merchr_hub_description_location'])) {
			$options['merchr_hub_description_location'] = 'full';
			$update = true;
		}
		
		if($update) {
			update_option('merchr_hub_options', $options);
		}
	}
 }
