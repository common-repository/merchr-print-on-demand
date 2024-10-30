<?php
/**
 * Merchr Core Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use MerchrHub\includes\helpers\MerchrHubHelpersSetup;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrCore
{
	protected $plugin_name; // @var string
	protected $version; // @var string
	protected $version_db; // @var string
	protected $plugin_url; // @var string
	protected $plugin_path; // @var string
	protected $templates_path; // @var string
	protected $templates_path_mail; // @var string
	protected $admin_css_url; // @var string
	protected $admin_images_url; // @var string
	protected $admin_js_url; // @var string
	protected $options; // @var string
	protected $setup_complete; // @var bool
	protected $woocommerce_active; // @var bool
	protected $setup_slug; // @var string
	
	/**
	 * Set up the core functionality of the plugin.
	 */
	public function __construct() 
	{
		$this->init();
	}
	
	/**
	 * Setup the class.
	 */
	private function init()
	{
		// Set version
		if(defined('MERCHR_PLUGIN_VERSION')) {
			$this->version = MERCHR_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		
		// Set database version
		if(defined('MERCHR_PLUGIN_DATABASE_VERSION')) {
			$this->version_db = MERCHR_PLUGIN_DATABASE_VERSION;
		} else {
			$this->version_db = '1.0.0';
		}
		
		// Set name
		if(defined('MERCHR_PLUGIN_NAME')) {
			$this->plugin_name = MERCHR_PLUGIN_NAME;
		} else {
			$this->plugin_name = 'merchr';
		}
		
		// Set plugin URL 
		if(defined('MERCHR_PLUGIN_URL')) {
			$this->plugin_url = MERCHR_PLUGIN_URL;
		} else {
			$this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));
		}
		
		// Set plugin path 
		if(defined('MERCHR_PLUGIN_PATH')) {
			$this->plugin_path = MERCHR_PLUGIN_PATH;
		} else {
			$this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
		}
		
		// Set templates path and mail path
		$this->templates_path = $this->plugin_path . 'hub' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		$this->templates_path_mail = $this->templates_path . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR;
		
		// Set admin asset URL's
		$this->admin_css_url = $this->plugin_url . 'hub/admin/css/';
		$this->admin_images_url = $this->plugin_url . 'hub/admin/images/';
		$this->admin_js_url = $this->plugin_url . 'hub/admin/js/';
		
		// Get Merchr Hub Options
		$this->options = get_option('merchr_hub_options');
		
		// Check setup is complete
		$this->setup_complete = MerchrHubHelpersSetup::merchrHubSetupCompleted($this->options);
		
		// Check WooCommerce is active
		$this->woocommerce_active = MerchrHubHelpersSetup::checkWooCommerceActive();
		
		// Set setup slug
		$this->setup_slug = 'merchr-hub-setup';
        
        // Check version
        $this->checkVersion();
	}
    
    /**
	 * Check Version.
	 */
	private function checkVersion()
	{
        $this->options = MerchrHubHelpersSetup::checkForNewVersion(
            $this->options,
            $this->version,
            $this->version_db
        );
    }
}
