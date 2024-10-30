<?php
/**
 * Merchr Hub Admin Hooks Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/hooks
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\hooks;

use MerchrHub\includes\MerchrHubHooks;
use MerchrHub\includes\hooks\MerchrHubAdminHooksProcesses;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminHooks extends MerchrHubHooks
{
	protected $admin_hooks_processes; // @var MerchrHubAdminHooksProcesses
	protected $admin_content; // @var MerchrAdminContent
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Instantiate admin hooks processes class
		$this->admin_hooks_processes = new MerchrHubAdminHooksProcesses();
	}
	
	/**
	 * Enqueue the styles for the admin interface.
	 */
	public function enqueueStyles() 
	{
		$this->admin_hooks_processes->processEnqueueStyles();
	}
	
	/**
	 * Enqueue the JavaScript for the admin interface.
	 */
	public function enqueueScripts() 
	{
		$this->admin_hooks_processes->processEnqueueScripts();
	}
	
	/**
	 * Add the admin menu items.
	 */
	public function addAdminMenuItems() 
	{
		$this->admin_hooks_processes->processAddAdminMenuItems();
	}
	
	/**
	 * Add the admin bar menu items.
	 *
	 * @param object
	 */
	public function addAdminBarMenuItems(\WP_Admin_Bar $admin_bar)
	{
		$this->admin_hooks_processes->processAddAdminBarMenuItems($admin_bar);
	}
	
	/**
	 * Add the Merchr Hub Dashboard Widget.
	 *
	 * This method tries to ensure the 
	 * Merchr Hub widget shows first.
	 */
	public function merchrDashboardWidget()
	{
		$this->admin_hooks_processes->processMerchrDashboardWidget();
	}
}
