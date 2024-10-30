<?php
/**
 * Merchr Hub Class, this sets up the plug-in.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use MerchrHub\includes\actions\MerchrHubAdminActions;
use MerchrHub\includes\actions\MerchrHubStartUpActions;
use MerchrHub\includes\actions\MerchrHubGlobalActions;
use MerchrHub\includes\helpers\MerchrHubHelpersSetup;
use MerchrHub\includes\filters\MerchrHubSchedulerFilter;
use MerchrHub\includes\filters\MerchrHubRequestFilter;
use MerchrHub\includes\filters\MerchrHubUpgradeFilter;
use MerchrHub\includes\hooks\MerchrHubAdminHooks;
use MerchrHub\includes\hooks\MerchrHubOrderHooks;
use MerchrHub\includes\scheduler\MerchrHubOrderSchedules;
use MerchrHub\includes\scheduler\MerchrHubProductImporterSchedules;
use MerchrHub\includes\scheduler\MerchrHubProductUpdaterSchedules;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHub extends MerchrCore
{
	protected $loader; // @var MerchrHubLoader
    protected $plugin_startup_actions; // @var MerchrHubStartUpActions
	
	/**
	 * Set up the core functionality of the plugin.
	 */
	public function __construct() 
	{
		parent::__construct();
        
        $this->plugin_startup_actions = new MerchrHubStartUpActions();
		
		// Check Key File
        $this->plugin_startup_actions->checkKeyFile();
        
        // Check options
        $this->plugin_startup_actions->checkOptions();
        
        // Check for first run for store owner
		if(is_admin()) {
			MerchrHubHelpersSetup::isFirstStoreOwnerLogin($this->options);
		}
		
		// Setup the plug-in
		$this->setup();
	}
	
	/**
	 * Setup the class.
	 */
	private function setup()
	{
		// Instantiate loader class
		$this->loader = new MerchrHubLoader();
		
		// Set the plug-ins locale
		$this->setWordPressLocalisation();
		
		// Define filters
		$this->defineFilters();
		
		// Define scheduled actions
		$this->defineSchedulerActions();
		
		// Define Order Hooks
		$this->defineOrderHooks();
        
        // Define global actions
        $this->defineGlobalActions();
		
		// Define admin hooks and actions
		if(is_admin()) {
			$this->defineAdminHooks();
			$this->defineAdminActions();
		}
	}
	
	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function setWordPressLocalisation()
	{
		$this->loader->addAction('plugins_loaded', (new MerchrHubi18n()), 'loadPluginTextDomain');
	}
	
	/**
	 * Register the filters
	 */
	private function defineFilters()
	{
		$this->loader->addFilter('action_scheduler_queue_runner_time_limit', (new MerchrHubSchedulerFilter()), 'increaseTimeLimit');
        $this->loader->addFilter('upgrader_pre_install', (new MerchrHubUpgradeFilter()), 'preUprgrade');
        $this->loader->addFilter('http_request_args', (new MerchrHubRequestFilter()), 'increaseTimeLimit');
	}
	
	/**
	 * Register the scheduler actions
	 */
	private function defineSchedulerActions()
	{
		$plugin_product_schedules = new MerchrHubProductImporterSchedules();
		$plugin_product_updater_schedules = new MerchrHubProductUpdaterSchedules();
		$plugin_order_schedules = new MerchrHubOrderSchedules();
		
		// Assign to init action
		add_action('init', function() use ($plugin_product_schedules, $plugin_product_updater_schedules, $plugin_order_schedules) {
			// Products
			add_action('merchr_process_product_import', [$plugin_product_schedules, 'runProductImportSchedule']);
            add_action('merchr_process_product_admin_imports', [$plugin_product_schedules, 'runCheckAdminImportProductsSchedule']);
			add_action('merchr_process_new_products', [$plugin_product_schedules, 'runNewProductsSchedule']);
			add_action('merchr_process_product_updates', [$plugin_product_updater_schedules, 'runProductUpdateSchedule']);
            add_action('merchr_process_product_deletes', [$plugin_product_updater_schedules, 'runProductDeleteSchedule']);
            add_action('merchr_process_product_stocks', [$plugin_product_updater_schedules, 'runProductStockSchedule']);
			
			// Orders
			add_action('merchr_process_paid_orders', [$plugin_order_schedules, 'runPaidOrderSchedule']);
			add_action('merchr_process_order_status', [$plugin_order_schedules, 'runOrderStatusSchedule']);
			
			// Add schedules that run as soon as possible
			if(as_has_scheduled_action('merchr_process_product_import') === false) {
				as_enqueue_async_action(
					'merchr_process_product_import',
                    [],
                    'merchr-import'
				);
			}
			if(as_has_scheduled_action('merchr_process_paid_orders') === false) {
				as_enqueue_async_action(
					'merchr_process_paid_orders',
                    [],
                    'merchr-orders'
				);
			}
			
			// Add schedules to be run at regular intervals
			if(as_has_scheduled_action('merchr_process_new_products') === false) {
				as_schedule_cron_action(time(), '00,10,20,30,40,50 * * * *', 'merchr_process_new_products'); // Every 10 minutes
			}
			if(as_has_scheduled_action('merchr_process_order_status') === false) {
				as_schedule_cron_action(time(), '05,15,25,35,45,55 * * * *', 'merchr_process_order_status'); // Every 10 minutes
			}
            if(as_has_scheduled_action('merchr_process_product_deletes') === false) {
				as_schedule_cron_action(time(), '03,18,33,48 * * * *', 'merchr_process_product_deletes'); // Every 15 minutes
			}
			if(as_has_scheduled_action('merchr_process_product_updates') === false) {
				$minute = random_int(0, 59);
                as_schedule_cron_action(time(), "{$minute} * * * *", 'merchr_process_product_updates'); // Every 60 minutes
			}
            if(as_has_scheduled_action('merchr_process_product_stocks') === false) {
				$minute = random_int(0, 59);
                as_schedule_cron_action(time(), "{$minute} * * * *", 'merchr_process_product_stocks'); // Every 60 minutes
			}
            if(as_has_scheduled_action('merchr_process_product_admin_imports') === false) {
                as_schedule_cron_action(time(), "01 03 * * *", 'merchr_process_product_admin_imports'); // 3am every morning
			}
		});
	}
	
	/**
	 * Register all of the hooks related to order management.
	 */
	private function defineOrderHooks()
	{
		$plugin_order_hooks = new MerchrHubOrderHooks();
		
		$this->loader->addAction('woocommerce_payment_complete', $plugin_order_hooks, 'preProcessOrderPaid');
		$this->loader->addAction('woocommerce_order_status_processing', $plugin_order_hooks, 'preProcessOrderPaid');
		$this->loader->addAction('woocommerce_order_status_cancelled', $plugin_order_hooks, 'cancelOrder');
	}
	
	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 */
	private function defineAdminHooks() 
	{
		$plugin_admin_hooks = new MerchrHubAdminHooks();
		
		// Enqueue scripts and styles
		$this->loader->addAction('admin_enqueue_scripts', $plugin_admin_hooks, 'enqueueStyles');
		$this->loader->addAction('admin_enqueue_scripts', $plugin_admin_hooks, 'enqueueScripts');
		
		// Always call, the method determines what and if to display
		$this->loader->addAction('admin_bar_menu', $plugin_admin_hooks, 'addAdminBarMenuItems', 9999);
		
		// Dashboard widget always shows, content alters depending on state (setup/woo)
		$this->loader->addAction('wp_dashboard_setup', $plugin_admin_hooks, 'merchrDashboardWidget', 9999);
		
		// Only show setup and menu if WooCommerce is active
		if($this->woocommerce_active) {
			$this->loader->addAction('admin_menu', $plugin_admin_hooks, 'addAdminMenuItems');
		}
	}
	
	/**
	 * Register all of the actions related to the admin area functionality of the plugin.
	 */
	private function defineAdminActions() 
	{
		$plugin_admin_actions = new MerchrHubAdminActions();
		
		// Add store connect actions
		$this->loader->addAction('wp_ajax_show_store_connect', $plugin_admin_actions, 'storeConnectPage');
		$this->loader->addAction('wp_ajax_nopriv_show_store_connect', $plugin_admin_actions, 'storeConnectPage');
		$this->loader->addAction('wp_ajax_merchr_connect_api', $plugin_admin_actions, 'connectViaApi');
		$this->loader->addAction('wp_ajax_nopriv_merchr_connect_api', $plugin_admin_actions, 'connectViaApi');
		$this->loader->addAction('wp_ajax_merchr_connect_account', $plugin_admin_actions, 'connectViaNewAccount');
		$this->loader->addAction('wp_ajax_nopriv_merchr_connect_account', $plugin_admin_actions, 'connectViaNewAccount');
		$this->loader->addAction('wp_ajax_merchr_select_store', $plugin_admin_actions, 'merchrSelectStore');
		$this->loader->addAction('wp_ajax_nopriv_merchr_select_store', $plugin_admin_actions, 'merchrSelectStore');
		
		// Add product import actions
		$this->loader->addAction('wp_ajax_merchr_import_products', $plugin_admin_actions, 'merchrImportProducts');
		$this->loader->addAction('wp_ajax_nopriv_merchr_import_products', $plugin_admin_actions, 'merchrImportProducts');
		
		// Add orders action
		$this->loader->addAction('wp_ajax_merchr_resend_order', $plugin_admin_actions, 'merchrResendOrder');
		$this->loader->addAction('wp_ajax_nopriv_merchr_resend_order', $plugin_admin_actions, 'merchrResendOrder');
		
		// Add settings form action
		$this->loader->addAction('wp_ajax_merchr_save_settings', $plugin_admin_actions, 'merchrSaveSettings');
		$this->loader->addAction('wp_ajax_nopriv_merchr_save_settings', $plugin_admin_actions, 'merchrSaveSettings');
	}
    
    /**
	 * Register all of the global actions.
	 */
	private function defineGlobalActions() 
	{
		$plugin_global_actions = new MerchrHubGlobalActions();
		
		// ADD cURL request action
        $this->loader->addAction('http_api_curl', $plugin_global_actions, 'setCurlTimeouts');
	}
	
	/**
	 * Run the loader to execute all of the hooks and actions with WordPress.
	 */
	public function run() 
	{
		$this->loader->run();
	}
	
	/**
	 * Return the loader.
	 */
	public function getLoader() 
	{
		return $this->loader;
	}
}
