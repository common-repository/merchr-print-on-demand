<?php
/**
 * Merchr Hub Admin Hooks Processes Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/hooks
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\hooks;

use MerchrHub\includes\MerchrHubHooks;
use MerchrHub\includes\content\MerchrHubAdminContent;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminHooksProcesses extends MerchrHubHooks
{
	protected $admin_content; // @var MerchrAdminContent
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Instantiate admin content class
		$this->admin_content = new MerchrHubAdminContent($this->setup_slug);
	}
	
	/**
	 * Process Enqueue Styles.
	 */
	public function processEnqueueStyles() 
	{
		wp_enqueue_style('featherlight', $this->admin_css_url . 'featherlight.css', [], '1.7.14', 'all');
		wp_enqueue_style('jquery-ui', $this->admin_css_url . 'jquery-ui.min.css', [], '1.13.2', 'all');
		wp_enqueue_style($this->plugin_name, $this->admin_css_url . 'admin.css', ['jquery-ui'], $this->version, 'all');
	}
	
	/**
	 * Process Enqueue Scripts.
	 */
	public function processEnqueueScripts() 
	{
		wp_enqueue_script('featherlight', $this->admin_js_url . 'featherlight.js', ['jquery'], '1.7.14', true);
		wp_enqueue_script($this->plugin_name, $this->admin_js_url . 'admin.js', ['jquery', 'jquery-ui-tooltip'], $this->version, true);
		wp_enqueue_script($this->plugin_name . '-products', $this->admin_js_url . 'products.js', ['jquery', 'featherlight', 'merchr'], $this->version, true);
		wp_enqueue_script($this->plugin_name . '-orders', $this->admin_js_url . 'orders.js', ['jquery', 'merchr'], $this->version, true);
		
		// Localise the script
		$data = [
			'errorMsg'              => esc_html__('Sorry something went wrong, please try again! (AJAX)', 'merchr'),
			'fieldsRequired'        => esc_html__('The fields highlighted in red are required!', 'merchr'),
			'pleaseWaitMsg'         => esc_html__('Please wait...', 'merchr'),
			'acceptNegativeProfit'  => esc_html__('You must accept the negative profit to save the changes!', 'merchr'),
			'noProductsSelected'    => esc_html__('No products have been selected!', 'merchr'),
			'importFailed'          => esc_html__('The product import failed, please contact us!', 'merchr'),
			'orderSentSuccessfully' => esc_html__('The order has been successfully re-scheduled to send to Merchr!', 'merchr'),
			'orderFailedToSend'     => esc_html__('The order failed to be re-scheduled, please contact us!', 'merchr')
		];
		wp_localize_script($this->plugin_name, 'merchrTranslations', $data);
	}
	
	/**
	 * Process Add Admin Menu Items.
	 */
	public function processAddAdminMenuItems() 
	{
		$uac = 'manage_options';
		
		// Add top level menu item
		add_menu_page(
			__('Merchr Hub', 'merchr'),
			__('Merchr Hub', 'merchr'),
			$uac,
			$this->setup_slug,
			false,
			$this->admin_images_url . 'merchr-menu-icon.png',
			4
		);
		
		// Add sub menu items
		add_submenu_page($this->setup_slug, esc_html__('Merchr Hub', 'merchr'), __('Start Here', 'merchr'), $uac, $this->setup_slug, [$this->admin_content, 'merchrHubMainContent']);
		add_submenu_page($this->setup_slug, esc_html__('Products', 'merchr'), __('Products', 'merchr'), $uac, 'merchr-hub-products', [$this->admin_content, 'merchrHubProductsContent'] );
		add_submenu_page($this->setup_slug, esc_html__('Orders', 'merchr'), __('Orders', 'merchr'), $uac, 'merchr-hub-orders', [$this->admin_content, 'merchrHubOrdersContent'] );
		add_submenu_page($this->setup_slug, esc_html__('Settings', 'merchr'), __('Settings', 'merchr'), $uac, 'merchr-hub-settings', [$this->admin_content, 'merchrHubSettingsContent'] );
		add_submenu_page($this->setup_slug, esc_html__('Customisation', 'merchr'), __('Customisation', 'merchr'), $uac, admin_url('edit.php?post_type=product&page=merchrcust'), false );
	}
	
	/**
	 * Process Add the admin bar menu items.
	 *
	 * @param object
	 */
	public function processAddAdminBarMenuItems(\WP_Admin_Bar $admin_bar)
	{
		// Check if WooCommerce is not active
		if(!$this->woocommerce_active) {
			$admin_bar->add_menu([
				'id'    => 'merchr-hub',
				'title' => '<span class="merchr-warning">' . esc_html__('Merchr Needs WooCommerce', 'merchr') .'</span>',
				'href'  => admin_url('plugin-install.php?s=WooCommerce&tab=search&type=term'),
				'meta'  => [
					'title' => esc_html__('Merchr Needs WooCommerce', 'merchr'),            
				],
			]);
		} else if(!$this->setup_complete) { // Check if not connected and/or products imported
			$admin_bar->add_menu([
				'id'    => 'merchr-hub',
				'title' => esc_html__('Setup Merchr', 'merchr'),
				'href'  => admin_url('admin.php?page=' . $this->setup_slug),
				'meta'  => [
					'title' => esc_html__('Setup Merchr', 'merchr'),            
				],
			]);
		}
	}
	
	/**
	 * Add the Merchr Hub Dashboard Widget.
	 *
	 * This method tries to ensure the 
	 * Merchr Hub widget shows first.
	 */
	public function processMerchrDashboardWidget()
	{
		global $wp_meta_boxes;
		$widget_id = 'merchr-hub-dashboard-widget';
		$widget_title = esc_html__('Merchr Hub and Personalisation', 'merchr');
		
		// Check if Woo active and setup complete and set appropriate method
		$method = 'merchrHubDashboardWidgetContent';
		if(!$this->woocommerce_active) {
			$method = 'merchrHubNoWooDashboardWidgetContent';
		} else if(!$this->setup_complete) {
			$method = 'merchrHubSetupDashboardWidgetContent';
		}
		
		// Add dashboard widget
		wp_add_dashboard_widget(
			$widget_id, 
			$widget_title, 
			[
				$this->admin_content,
				$method
			]
		);
		
		// Get the regular dashboard widgets array, our widget appended
		$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		
		// Backup and remove our new dashboard widget
		$widget = [
			$widget_id => $dashboard[$widget_id]
		];
		unset($dashboard[$widget_id]);
	 
		// Merge the two arrays together so our widget is at the beginning
		$sorted_dashboard = array_merge($widget, $dashboard);
		
		// Save the sorted array back into the original metaboxes 
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
}
