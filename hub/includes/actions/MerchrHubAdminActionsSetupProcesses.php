<?php
/**
 * Merchr Hub Admin Actions Setup Processes Class.
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
use MerchrHub\includes\content\MerchrHubAdminProductsPages;
use MerchrHub\includes\data\MerchrHubGetSetStoresAndUser;
use MerchrHub\includes\data\MerchrHubTaxonomies;
use MerchrHub\includes\data\MerchrHubAdminFormsData;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminActionsSetupProcesses extends MerchrHubActions
{
	protected $request; // @var MerchrHubConnectStore
	protected $forms_data; // @var MerchrHubAdminFormsData
	protected $prodcut_pages; // @var MerchrHubAdminProductsPages
	protected $taxonomies; // @var MerchrHubTaxonomies
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Set store request and setup connection
		$this->request = new MerchrHubGetSetStoresAndUser();
		$this->request->setupConnection();
		
		// Set Admin Forms Data
		$this->forms_data = new MerchrHubAdminFormsData();
		
		// Set product pages
		$this->prodcut_pages = new MerchrHubAdminProductsPages();
		
		// Set taxonomies
		$this->taxonomies = new MerchrHubTaxonomies();
	}
	
	/**
	 * Process Store Connect Page.
	 *
	 * @param array
	 */
	public function processStoreConnectPage(array $nonce_names)
	{
		// Create content
		$replacements = $this->forms_data->getStoreConnectPageFormsData($nonce_names);
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_connect_store.tpl', $this->templates_path);
		$content = MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
		
		// Set status and page content
		$this->result['status'] = 'success';
		$this->result['payload'] = $content;
		
		// Send content
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Process Connect Via Api.
	 *
	 * @param array
	 */
	public function processConnectViaApi(array $nonce_names)
	{
		// Set API Key
		$api_key = trim($_POST['api']);
		
		// Check we have an API key
		if($api_key === '') {
			$this->result['msg'] = __('Please enter your API key.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Encrypt key
		$encyption_key = MerchrHubKey::returnAsciiSafeKey();
		$encrypted_api_key = Crypto::encrypt($api_key, $encyption_key);
		
		// Update API key option
		$this->options['merchr_hub_api_key'] = $encrypted_api_key;
		update_option('merchr_hub_options', $this->options);
		
		// Make request
		$response = $this->request->makeRequestForStores($this->options);
		
		// If false response, return message
		if($response === false) {
			$this->result['msg'] = __('Oops, something went wrong, check your API key and try again.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Decode response and set stores data and count
		$response = json_decode($response, true);
		
		// Check for data
		if(!isset($response['data'])) {
			$this->result['msg'] = $this->request->parseApiErrors($response);
			$this->sendAJAXContent($this->result);
		}
		
		// Set stores data and count
		$stores_data = $response['data'];
		$stores_count = count($stores_data);
		
		// if no store(s), create one
		if($stores_count === 0) {
			$response = $this->createStore();
			$stores_data[0]['user_id'] = $response['data']['user_id'];
			$stores_data[0]['id'] = $response['data']['id'];
			$stores_data[0]['public_api_key_hash'] = $response['data']['public_api_key_hash'];
			$stores_count = 1;
		}
		
		// Save user_id and update connected
		$this->options['merchr_hub_user_id'] = (int) $stores_data[0]['user_id'];
		$this->options['merchr_hub_connected'] = 'yes';
		
		// If store count is 1, update store options also
		if($stores_count === 1) { 
			$this->options['merchr_hub_store_id'] = (int) $stores_data[0]['id'];
			
            // Check public API Key
            $public_api_key = $stores_data[0]['public_api_key_hash'];
            if(strlen($public_api_key) !== 64 || !preg_match('/[^A-Za-z0-9]/', $public_api_key)) {
                $public_api_key = '';
            }
            update_option('merchrcust_public_api_key', $public_api_key);
		}
		update_option('merchr_hub_options', $this->options);
		
		// Setup taxonomies
		$this->taxonomies->taxonomiesDownloadAndSave($this->options);
		
		// Return the products page content
		$products_content = $this->prodcut_pages->getProducts('store', $this->options, true, $stores_data, $nonce_names);
		
		// Send content
		$this->result['status'] = 'success';
		$this->result['payload'] = $products_content;
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Process Connect Via New Account.
	 *
	 * @param array
	 */
	public function processConnectViaNewAccount(array $nonce_names)
	{
		// Basic Validation
		if(
			!isset($_POST['full_name']) && trim($_POST['full_name']) == '' || 
			!isset($_POST['email']) && trim($_POST['email']) == '' || 
			!isset($_POST['password']) && trim($_POST['password']) == '' 
		) {
			$this->result['msg'] = __('All fields are required to create a new account.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Set vars
		$payload = [];
		$payload['name']     = sanitize_text_field(trim($_POST['full_name']));
		$payload['email']    = sanitize_email(trim($_POST['email']));
		$payload['password'] = trim($_POST['password']);
		
		// Check we have a valid email address
		if(!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
			$this->result['msg'] = __('Please enter a valid email address.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Check password is at least 8 characters
		// Further checks run on the Endpoint
		if(strlen($payload['password']) < 8) {
			$this->result['msg'] = __('Please ensure the password is at least 8 characters in length.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Make request to create the user account
		$response = $this->request->makeRequestToCreateUser($this->options, $payload);
		
		// If false response, return message
		if($response === false) {
			$this->result['msg'] = __('Oops, something went wrong, check your API key and try again.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Decode JSON 
		$response = json_decode($response, true);
		
		// Check for data
		if(!isset($response['data'])) {
			$this->result['msg'] = $this->request->parseApiErrors($response);
			$this->sendAJAXContent($this->result);
		}
		
		// Set user id
		$user_id = (int) $response['data']['id'];
		
		// Encrypt key
		$encyption_key = MerchrHubKey::returnAsciiSafeKey();
		$encrypted_api_key = Crypto::encrypt($response['data']['access_token'], $encyption_key);
		
		// Update User ID and API key option
		$this->options['merchr_hub_user_id'] = $user_id;
		$this->options['merchr_hub_api_key'] = $encrypted_api_key;
		update_option('merchr_hub_options', $this->options);
		
		// Proceed to create the store
		$payload2 = [];
		$payload2['store_name']          = sanitize_text_field(trim($_POST['store_name']));
		$payload2['store_url']           = sanitize_url(trim($_POST['store_url']));
		$payload2['store_email_address'] = sanitize_email(trim($_POST['store_email']));
		$payload2['store_logo']          = sanitize_url(trim($_POST['store_logo']));
		$payload2['store_type_id']       = $this->request->getApiStoreType();
		$payload2['currency_id']         = 1;
		
		// Create the store
		$response = $this->createStore($payload2);
		
		// Check for data
		if(!isset($response['data'])) {
			$this->result['msg'] = $this->request->parseApiErrors($response);
			$this->sendAJAXContent($this->result);
		}
		
		// Set store ID 
		$store_id = (int) $response['data']['id'];
        
        // Check public API Key
        $public_api_key = $response['data']['public_api_key_hash'];
        if(strlen($public_api_key) !== 64 || !preg_match('/[^A-Za-z0-9]/', $public_api_key)) {
            $public_api_key = '';
        }
		
		// Update options
		$this->options['merchr_hub_store_id'] = $store_id;
		$this->options['merchr_hub_connected'] = 'yes';
		update_option('merchr_hub_options', $this->options);
		update_option('merchrcust_public_api_key', $public_api_key);
		
		// Setup taxonomies
		$this->taxonomies->taxonomiesDownloadAndSave($this->options);
		
		// Return the products page content
		$products_content = $this->prodcut_pages->getProducts('store', $this->options);
		
		// Send content
		$this->result['status'] = 'success';
		$this->result['payload'] = $products_content;
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Process Merchr Select Store.
	 *
	 * @param array
	 */
	public function processMerchrSelectStore(array $nonce_names)
	{
		// Set Store ID
		$store_id = (int) $_POST['store'];
		
		// Decode store data and fetch public_api_key for chosen store
		$stores = json_decode(stripslashes($_POST['the_stores']), true);
		$public_api_key = '';
		foreach($stores as $store) {
			$id = (int) $store['id'];
			if($id !== $store_id) {
				continue;
			}
			$public_api_key = $store['public_api_key_hash'];
		}
        
        // Check public API Key
        if(strlen($public_api_key) !== 64 || !preg_match('/[^A-Za-z0-9]/', $public_api_key)) {
            $public_api_key = '';
        }
		
		// Update store options
		$this->options['merchr_hub_store_id'] = $store_id;
		update_option('merchr_hub_options', $this->options);
		update_option('merchrcust_public_api_key', $public_api_key);
		
		// Return the products page content
		$products_content = $this->prodcut_pages->getProducts('store', $this->options);
		
		// Send content
		$this->result['status'] = 'success';
		$this->result['payload'] = $products_content;
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Create Store.
	 *
	 * @param array optional
	 *
	 * @return array
	 */
	protected function createStore(array $payload = [])
	{
		// Proceed to create the store
		if(empty($payload)) {
			$payload['store_name']          = trim(get_bloginfo('name'));
			$payload['store_url']           = trim(get_site_url());
			$payload['store_email_address'] = trim(get_bloginfo('admin_email'));
			$payload['store_logo']          = MerchrHubHelpersTemplates::getCustomLogoUrl();
			$payload['store_type_id']       = $this->request->getApiStoreType();
			$payload['currency_id']         = 1;
			$payload['hosting']             = 'remote';
		}
		
		// Make request to create the store
		$response = $this->request->makeRequestToCreateStore($this->options, $payload);
		
		// Decode JSON and return
		return json_decode($response, true);
	}
}
