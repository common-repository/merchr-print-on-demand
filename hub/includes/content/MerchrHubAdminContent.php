<?php
/**
 * Merchr Admin Content Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\content\MerchrHubAdminDashboardContent;
use MerchrHub\includes\content\MerchrHubAdminDashboardWidgetContent;
use MerchrHub\includes\content\MerchrHubAdminOrdersPage;
use MerchrHub\includes\content\MerchrHubAdminProductsPages;
use MerchrHub\includes\content\MerchrHubAdminSettingsPage;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminContent extends MerchrHubContent
{
	protected $dashboard_content; // @var MerchrHubAdminDashboardContent
	protected $dashboard_widget_content; // @var MerchrHubAdminDashboardWidgetContent
	protected $product_pages; // @var MerchrHubAdminProductsPages
	protected $orders_page; // @var MerchrHubAdminOrdersPage
	protected $settings_page; // @var MerchrHubAdminSettingsPage
	
	/**
	 * Set up class.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Instantiate Merchr Dashboard Content
		$this->dashboard_content = new MerchrHubAdminDashboardContent();
		
		// Instantiate Admin Dashboard Widget Content
		$this->dashboard_widget_content = new MerchrHubAdminDashboardWidgetContent($this->setup_slug);
		
		// Instantiate Products Pages Content
		$this->product_pages = new MerchrHubAdminProductsPages();
		
		// Instantiate Orders Page Content
		$this->orders_page = new MerchrHubAdminOrdersPage();
		
		// Instantiate Settings Page Content
		$this->settings_page = new MerchrHubAdminSettingsPage();
	}
	
	/**
	 * Merchr Hub Main Content.
	 */
	public function merchrHubMainContent() 
	{
		echo $this->dashboard_content->getDashboardContents();
	}
	
	/**
	 * Merchr Hub Products Content.
	 *
	 * @param bool optional
	 *
	 * @return string optional
	 */
	public function merchrHubProductsContent($return = false) 
	{
		// Check of store connected
		if($this->options['merchr_hub_connected'] == 'no') {
			// Show start here content
			$this->merchrHubMainContent();
			return;
		}
		
		// Fetch product content
		$products_content = $this->product_pages->getProducts('store', $this->options);
		
		// Create design your own notice
		$content  = '<div id="merchr-design-your-own-notice">';
		$content .= '<h2>' . esc_html__('Want to design your own products?', 'merchr') . '</h2>';
		$content .= '<p>' . esc_html__('Click the button below and login to the Merchr Hub . ', 'merchr').'</p>';
		$content .= '<a href="https://hub.merchr.com" class="merchr-hub-btn" target="_blank">' . esc_html__('Design on the Merchr Hub!', 'merchr') . '</a>';
		$content .= '</div>';
		
		$final_content = MerchrHubHelpersTemplates::parseStringReplacements(
			MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_start_here_wrapper.tpl', $this->templates_path), 
			[
				'logo_src'        => esc_url($this->admin_images_url . 'merchr-logo.png'),
				'logo_alt'        => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'logo_title'      => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'content'         => $content,
				'product_content' => $products_content,
				'all_content'     => '',
			]
		);
		
		if($return) {
			return $final_content;
		} else {
			echo MerchrHubHelpersTemplates::returnPageWrapAndTitle(__( 'Merchr Hub Products', 'merchr' ), $final_content);
		}
		
	}
	
	/**
	 * Merchr Hub Orders Content.
	 */
	public function merchrHubOrdersContent() 
	{
		$final_content = MerchrHubHelpersTemplates::parseStringReplacements(
			MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_start_here_wrapper.tpl', $this->templates_path), 
			[
				'logo_src'        => esc_url($this->admin_images_url . 'merchr-logo.png'),
				'logo_alt'        => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'logo_title'      => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'content'         => '',
				'product_content' => '',
				'all_content'     => $this->orders_page->returnOrders(),
			]
		);
		echo MerchrHubHelpersTemplates::returnPageWrapAndTitle(__( 'Merchr Hub Orders', 'merchr' ), $final_content);
	}
	
	/**
	 * Merchr Hub Settings Content.
	 */
	public function merchrHubSettingsContent() 
	{
		$final_content = MerchrHubHelpersTemplates::parseStringReplacements(
			MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_start_here_wrapper.tpl', $this->templates_path), 
			[
				'logo_src'        => esc_url($this->admin_images_url . 'merchr-logo.png'),
				'logo_alt'        => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'logo_title'      => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
				'content'         => '',
				'product_content' => '',
				'all_content'     => $this->settings_page->returnSettingsForm(),
			]
		);
		echo MerchrHubHelpersTemplates::returnPageWrapAndTitle(__( 'Merchr Hub Settings', 'merchr' ), $final_content);
	}
	
	/**
	 * Merchr Hub Dashboard Widget Content.
	 */
	public function merchrHubDashboardWidgetContent() 
	{
		echo $this->dashboard_widget_content->returnDashboardWidgetContent();
	}
	
	/**
	 * Merchr Hub No WooCommerce Dashboard Widget Content.
	 */
	public function merchrHubNoWooDashboardWidgetContent() 
	{
		echo $this->dashboard_widget_content->returnNoWooDashboardWidgetContent();
	}
	
	/**
	 * Merchr Hub Setup Dashboard Widget Content.
	 */
	public function merchrHubSetupDashboardWidgetContent() 
	{
		echo $this->dashboard_widget_content->returnSetupDashboardWidgetContent();
	}
}
