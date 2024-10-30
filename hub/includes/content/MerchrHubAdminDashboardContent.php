<?php
/**
 * Merchr Hub Admin Dashboard Content.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminDashboardContent extends MerchrHubContent
{
	/**
	 * Set up class.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Get Dashboard Contents.
	 */
	public function getDashboardContents()
	{
		// Check status of plug-in and load appropriate content
		if(!$this->connected) {
			$content = $this->getNotConnectedDashboardContents();
		} else {
			$content = $this->getStandardDashboardContents();
		}
		return MerchrHubHelpersTemplates::returnPageWrapAndTitle(
				__( 'Merchr Hub Dashboard', 'merchr' ), 
				$content
			);
	}
	
	/**
	 * Get Not Connected Dashboard Content.
	 */
	protected function getNotConnectedDashboardContents()
	{
		$connect_link = admin_url('admin-ajax.php?action=show_store_connect');
		$user_name = MerchrHubHelpersTemplates::fetchLoggedInUsersName();
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('dashboard_start_here.tpl', $this->templates_path);
		$replacements = [
			'logo_src'          => esc_url($this->admin_images_url . 'merchr-logo.png'),
			'logo_alt'          => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'logo_title'        => esc_attr__('Merchr Hub and Personalisation', 'merchr'),
			'congratulate_user' => wp_kses(sprintf(__('Congratulations <span>%s</span> and welcome to the Merchr Experience!', 'merchr'), $user_name), ['span' => []]),
			'welcome_msg'       => esc_html__('The first part of your journey is to connect your store to the Merchr Personalisation Hub, click the button below to take this first step.', 'merchr'),
			'arrow_src'         => esc_url($this->admin_images_url . 'merchr-start-arrow.png'),
			'arrow_alt'         => esc_attr__('Start here', 'merchr'),
			'arrow_title'       => esc_attr__('Start here', 'merchr'),
			'start-btn-href'    => esc_url($connect_link),
			'start-btn-nonce'   => wp_create_nonce('show_store_connect'),
			'start-btn-text'    => esc_html__('Connect Your Store', 'merchr'),
		];
		
		return MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
	}
	
	/**
	 * Get Standard Dashboard Content.
	 */
	protected function getStandardDashboardContents()
	{
		$admin_content = new MerchrHubAdminContent();
		return $admin_content->merchrHubProductsContent(true);
	}
}
