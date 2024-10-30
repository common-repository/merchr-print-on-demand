<?php
/**
 * Merchr Hub Admin Dashboard Widget Content.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\data\MerchrHubStats;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminDashboardWidgetContent extends MerchrHubContent
{
	protected $setup_slug; // @var string
	
	/**
	 * Set up class.
	 */
	public function __construct(string $setup_slug) 
	{
		parent::__construct();
		$this->setup_slug = $setup_slug;
	}
	
	/**
	 * Return Dashboard Widget Content.
	 */
	public function returnDashboardWidgetContent() 
	{
		// Get dashboard stats
		$stats = new MerchrHubStats();
		$dashboard_stats = $stats->getDashboardStats();
		$user_name = MerchrHubHelpersTemplates::fetchLoggedInUsersName();
		$woo_currency_symbol = get_woocommerce_currency_symbol();
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_widget.tpl', $this->templates_path);
		$replacements = [
			'logo_src'                => esc_url($this->admin_images_url . 'merchr-logo.png'),
			'logo_alt'                => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'logo_title'              => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'welcome_message'         => wp_kses(sprintf(__("Welcome <strong>%s</strong>...", 'merchr'), $user_name), ['strong' => []]),
			'imported_products_title' => esc_html__('Merchr Products', 'merchr'),
			'imported_products'       => number_format($dashboard_stats['total_products'], 0),
			'orders_total_title'      => esc_html__('Merchr Orders', 'merchr'),
			'orders_total'            => number_format($dashboard_stats['total_orders_success'], 0),
			'currency_symbol'         => $woo_currency_symbol,
			'orders_value'            => number_format($dashboard_stats['total_orders_value'], 2),
			'orders_failed_title'     => esc_html__('Failed Orders', 'merchr'),
			'orders_failed'           => number_format($dashboard_stats['total_orders_failed'], 0),
			'failed_class'            => ($dashboard_stats['total_orders_failed'] == 0) ? ' class="merchr-hidden"' : '',
			'failed_link'             => '<a class="merchr-hub-btn merchr-failed-order-link" href="' . admin_url('admin.php?page=merchr-hub-orders') . '">'. __('Re-send Orders!', 'merchr') .'</a>',
			'manage_link'             => esc_url(admin_url('admin.php?page=merchr-hub-products')),
			'manage_link_text'        => esc_html__('Manage Merchr', 'merchr'),
			'manage_link_title'       => esc_attr__('Manage Merchr', 'merchr'),
		];
		
		return MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
	}
	
	/**
	 * Return No WooCommerce Dashboard Widget Content.
	 */
	public function returnNoWooDashboardWidgetContent() 
	{
		$user_name = MerchrHubHelpersTemplates::fetchLoggedInUsersName();
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_widget_no_woo.tpl', $this->templates_path);
		$replacements = [
			'logo_src'          => esc_url($this->admin_images_url . 'merchr-logo.png'),
			'logo_alt'          => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'logo_title'        => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'welcome_message'   => wp_kses(sprintf(__("Welcome <strong>%s</strong>...", 'merchr'), $user_name), ['strong' =>[],]),
			'no_woo_message'    => esc_html__('WooCommerce is required!', 'merchr'),
			'no_woo_link'       => esc_url(admin_url('plugin-install.php?s=WooCommerce&tab=search&type=term')),
			'no_woo_link_text'  => esc_html__('Install WooCommerce', 'merchr'),
			'no_woo_link_title' => esc_attr__('Install WooCommerce', 'merchr'),
		];
		
		return MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
	}
	
	/**
	 * Return Setup Dashboard Widget Content.
	 */
	public function returnSetupDashboardWidgetContent() 
	{
		$user_name = MerchrHubHelpersTemplates::fetchLoggedInUsersName();
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_widget_setup_todo.tpl', $this->templates_path);
		$replacements = [
			'logo_src'         => esc_url($this->admin_images_url . 'merchr-logo.png'),
			'logo_alt'         => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'logo_title'       => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'welcome_message'  => wp_kses(sprintf(__("Welcome <strong>%s</strong>...", 'merchr'), $user_name), ['strong' =>[],]),
			'setup_message'    => esc_html__('Start your journey', 'merchr'),
			'setup_link'       => esc_url(admin_url('admin.php?page=' . $this->setup_slug)),
			'setup_link_text'  => esc_html__('Setup Merchr Here', 'merchr'),
			'setup_link_title' => esc_attr__('Setup Merchr Here', 'merchr'),
		];
		
		return MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
	}
}
