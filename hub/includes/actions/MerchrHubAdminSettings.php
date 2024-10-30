<?php
/**
 * Merchr Hub Admin Settings.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use Defuse\Crypto\Crypto;
use MerchrHub\includes\MerchrHubActions;
use MerchrHub\includes\MerchrHubKey;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminSettings extends MerchrHubActions
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Process Paid Orders.
	 */
	public function saveSettings()
	{
		$update = false;
		$api_key = sanitize_text_field(trim($_POST['api_key']));
		$remove_data = sanitize_text_field(trim($_POST['remove_data']));
		$prices_include_tax = sanitize_text_field(trim($_POST['prices_include_tax']));
		$description = sanitize_textarea_field(trim($_POST['description']));
		
		// Get Merchr Options
		$merchr_options = get_option('merchr_hub_options');
		
		// Check if we need to update prices_include_tax
		if($merchr_options['merchr_hub_prices_include_tax'] != $prices_include_tax) {
			$merchr_options['merchr_hub_prices_include_tax'] = $prices_include_tax;
			$update = true;
		}
		
		// Check if we need to update prices_include_tax
		if($merchr_options['merchr_hub_description_location'] != $description) {
			$merchr_options['merchr_hub_description_location'] = $description;
			$update = true;
		}
		
		// Check if we need to update remove data
		if($merchr_options['merchr_hub_remove_data_on_uninstall'] != $remove_data) {
			$merchr_options['merchr_hub_remove_data_on_uninstall'] = $remove_data;
			$update = true;
		}
		
		// Check if we need to update the API key
		if($api_key != '') {
			$encyption_key = MerchrHubKey::returnAsciiSafeKey();
			$encrypted_api_key = Crypto::encrypt($api_key, $encyption_key);
			$merchr_options['merchr_hub_api_key'] = $encrypted_api_key;
			$update = true;
		}
		
		if($update) {
			update_option('merchr_hub_options', $merchr_options);
		}
		
		// Set status and message
		$this->result['status'] = 'success';
		$this->result['msg'] = __('The settings have been saved successfully', 'merchr');
		
		// Send content
		$this->sendAJAXContent($this->result);
	}
}
