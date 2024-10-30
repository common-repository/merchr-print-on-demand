<?php
/**
 * Merchr Hub Admin Actions Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use MerchrHub\includes\MerchrHubActions;
use MerchrHub\includes\actions\MerchrHubAdminActionsSetupProcesses;
use MerchrHub\includes\actions\MerchrHubAdminProductImporter;
use MerchrHub\includes\actions\MerchrHubAdminOrders;
use MerchrHub\includes\actions\MerchrHubAdminSettings;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminActions extends MerchrHubActions
{
	protected $setup_actions; // @var MerchrHubAdminActionsSetupProcesses
	protected $product_importer; // @var MerchrHubAdminProductImporter
	protected $orders; // @var MerchrHubAdminOrders
	protected $settings; // @var MerchrHubAdminSettings
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Setup results and nonces arrays
		$this->setupArrays();
		
		// Set setup actions
		$this->setup_actions = new MerchrHubAdminActionsSetupProcesses();
		
		// Set product importer
		$this->product_importer = new MerchrHubAdminProductImporter();
		
		// Set orders handler
		$this->orders = new MerchrHubAdminOrders();
		
		// Set settings handler
		$this->settings = new MerchrHubAdminSettings();
	}
	
	/**
	 * Store Connect Page.
	 */
	public function storeConnectPage()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['start']);
		
		// Process request
		$this->setup_actions->processStoreConnectPage($this->nonce_names);
	}
	
	/**
	 * Connect via API.
	 */
	public function connectViaApi()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['api']);
		
		// Check we have an API key
		if(!isset($_POST['api']) || trim($_POST['api']) === '') {
			$this->result['msg'] = __('The API key is required!', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Process request
		$this->setup_actions->processConnectViaApi($this->nonce_names);
	}
	
	/**
	 * Connect Via New Account.
	 */
	public function connectViaNewAccount()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['account']);
		
		// Process request
		$this->setup_actions->processConnectViaNewAccount($this->nonce_names);
	}
	
	/**
	 * Merchr Select Store.
	 */
	public function merchrSelectStore()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['select_store']);
		
		// Check we have a store ID
		if(!isset($_POST['store']) || $_POST['store'] === '') {
			$this->result['msg'] = __('Please select the store to proceed!', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Process request
		$this->setup_actions->processMerchrSelectStore($this->nonce_names);
	}
	
	/**
	 * Merchr Import Products.
	 */
	public function merchrImportProducts()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['import_products']);
		
		// Process request
		$this->product_importer->importProducts();
	}
	
	/**
	 * Merchr Import Products.
	 */
	public function merchrResendOrder()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['resend_order']);
		
		// Process request
		$this->orders->processResendOrder();
	}
	
	/**
	 * Merchr Save Settings.
	 */
	public function merchrSaveSettings()
	{
		// Verify request
		$this->verifyRequest($this->nonce_names['save_settings']);
		
		// Process request
		$this->settings->saveSettings();
	}
	
	/**
	 * Setup Arrays.
	 */
	protected function setupArrays()
	{
		// Results array format
		$this->result = [
			'status'  => 'error',
			'msg'     => '',
			'payload' => ''
		];
		
		// Set nonces used in this class
		// Page nonce use action method
		// Form nonce use array with action method first, field name second
		$this->nonce_names = [
			'start'           => 'show_store_connect', // @\content\MerchrHubAdminDashboardContent->getNotConnectedDashboardContents()
			'api'             => ['merchr_connect_api', '_nonce_api'], // @\data\MerchrHubAdminFormsData->getStoreConnectPageFormsData()
			'account'         => ['merchr_connect_account', '_nonce_account'], // @\data\MerchrHubAdminFormsData->getStoreConnectPageFormsData()
			'select_store'    => ['merchr_select_store', '_nonce_select_store'], // @\content\MerchrHubAdminProductsPages->getProducts()
			'import_products' => ['merchr_import_products', '_nonce_import_products'], // @\content\MerchrHubAdminProductsPages->parseProductsAndDisplay()
			'resend_order'    => 'merchr_resend_order', // @\content\MerchrHubAdminOrdersPage->returnOrders()
			'save_settings'   => ['merchr_save_settings', '_nonce_save_settings'], // @\content\MerchrHubAdminAettingsPage->returnSettingsForm()
		];
	}
}
