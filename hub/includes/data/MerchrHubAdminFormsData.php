<?php
/**
 * Merchr Hub Admin Forms Data Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/data
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\data;

use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminFormsData
{
	/**
	 * Get Store Connect Page Forms Data.
	 *
	 * @param array
	 *
	 * @return array
	 */
	public function getStoreConnectPageFormsData(array $nonce_names)
	{
		// Create content
		$api_link = esc_url(admin_url('admin-ajax.php?action=merchr_connect_api'));
		$account_link = esc_url(admin_url('admin-ajax.php?action=merchr_connect_account'));
		$replacements = [
			'api_action'                      => $api_link,
			'api_header'                      => esc_html__('Already have an API Key?', 'merchr'),
			'api_message'                     => esc_html__('Enter your API key below and press connect to get started.', 'merchr'),
			'api_field_placeholder'           => esc_attr__('Enter API key here...', 'merchr'),
			'api_field_title'                 => esc_attr__('Enter API key here', 'merchr'),
			'api_submit_title'                => esc_attr__('Connect', 'merchr'),
			'api_submit_value'                => esc_attr__('Connect', 'merchr'),
			'api_no_api_key'                  => wp_kses(__("Don't have an API key?<br><strong>Click here</strong> to create an account.", 'merchr'), ['br' => [], 'strong' => []]),
			'api_nonce'                       => wp_nonce_field($nonce_names['api'][0], $nonce_names['api'][1], true, false),
			'account_action'                  => $account_link,
			'account_header'                  => esc_html__('Create an account here!', 'merchr'),
			'account_message'                 => esc_html__('Fill out the form below to create an account on the Merchr Hub and get started.', 'merchr'),
			'account_name_placeholder'        => esc_attr__('Enter your full name here...', 'merchr'),
			'account_name_title'              => esc_attr__('Enter your full name here', 'merchr'),
			'account_email_placeholder'       => esc_attr__('Enter your email here...', 'merchr'),
			'account_email_title'             => esc_attr__('Enter your email here', 'merchr'),
			'account_password_placeholder'    => esc_attr__('Enter your password here...', 'merchr'),
			'account_password_title'          => esc_attr__('Enter your password here', 'merchr'),
			'account_store_name_placeholder'  => esc_attr__('Enter your store name here...', 'merchr'),
			'account_store_name_title'        => esc_attr__('Enter your store name here', 'merchr'),
			'account_store_url_placeholder'   => esc_attr__('Enter your store URL here...', 'merchr'),
			'account_store_url_title'         => esc_attr__('Enter your store URL here', 'merchr'),
			'account_store_email_placeholder' => esc_attr__('Enter your store email here...', 'merchr'),
			'account_store_email_title'       => esc_attr__('Enter your store email here', 'merchr'),
			'account_logo_placeholder'        => esc_attr__('Select your store logo here...', 'merchr'),
			'account_logo_title'              => esc_attr__('Select your store logo here', 'merchr'),
			'account_store_name_value'        => esc_attr(get_bloginfo('name')),
			'account_store_url_value'         => esc_attr(get_site_url()),
			'account_store_email_value'       => esc_attr(get_bloginfo('admin_email')),
			'account_logo_value'              => esc_url(MerchrHubHelpersTemplates::getCustomLogoUrl()),
			'account_submit_title'            => esc_attr__('Create Account', 'merchr'),
			'account_submit_value'            => esc_attr__('Create Account', 'merchr'),
			'account_has_api_key'             => wp_kses(__('Have an API key?<br><strong>Click here</strong> here to enter your key.', 'merchr'), ['br' => [], 'strong' => []]),
			'account_nonce'                   => wp_nonce_field($nonce_names['account'][0], $nonce_names['account'][1], true, false),
		];
		
		return $replacements;
	}
	
	/**
	 * Get Settings Page Forms Data.
	 *
	 * @param array
	 *
	 * @return array
	 */
	public function getSettingsPageFormsData(array $options)
	{
		// Set options for remove data
		$remove_data = $options['merchr_hub_remove_data_on_uninstall'];
		$select_options = '';
		if($remove_data == 'no') {
			$select_options .= '<option value="no">' . esc_attr__('No', 'merchr') . '</value>';
			$select_options .= '<option value="yes">' . esc_attr__('Yes', 'merchr') . '</value>';
		} else {
			$select_options .= '<option value="yes">' . esc_attr__('Yes', 'merchr') . '</value>';
			$select_options .= '<option value="no">' . esc_attr__('No', 'merchr') . '</value>';
		}
		
		// Set options for prices include tax
		$tax = $options['merchr_hub_prices_include_tax'];
		$tax_select_options = '';
		if($tax == 'no') {
			$tax_select_options .= '<option value="no">' . esc_attr__('No', 'merchr') . '</value>';
			$tax_select_options .= '<option value="yes">' . esc_attr__('Yes', 'merchr') . '</value>';
		} else {
			$tax_select_options .= '<option value="yes">' . esc_attr__('Yes', 'merchr') . '</value>';
			$tax_select_options .= '<option value="no">' . esc_attr__('No', 'merchr') . '</value>';
		}
		
		// Set options for description location
		$description = $options['merchr_hub_description_location'];
		$description_keys = [
			'full' => esc_attr__('Full', 'merchr'),
			'short' => esc_attr__('Short', 'merchr'),
			'both' => esc_attr__('Both', 'merchr'),
		];
		$description_select_options = '';
		if(isset($description_keys[$description])) {
			$description_select_options .= '<option value="' . esc_attr($description) . '">' . $description_keys[$description] . '</value>';
			unset($description_keys[$description]);
			foreach($description_keys as $key => $val) {
				$description_select_options .= '<option value="' . $key . '">' . $val . '</value>';
			}
		} else {
			foreach($description_keys as $key => $val) {
				$description_select_options .= '<option value="' . $key . '">' . $val . '</value>';
			}
		}
		
		// Create content
		$replacements = [
			'settings_action'           => esc_url(admin_url('admin-ajax.php?action=merchr_save_settings')),
			'settings_header'           => esc_html__('Update the Merchr Hub Plugin Setting', 'merchr'),
			'api_key_label'             => esc_html__('Enter a new API key here, otherwise leave blank', 'merchr'),
			'api_key_field_placeholder' => esc_attr__('Enter a new API key here, otherwise leave blank', 'merchr'),
			'api_key_field_title'       => esc_attr__('If you have retrieved a new API Key from the Merchr Hub, enter it here. Only do this if your store has lost connection with the Merchr Hub', 'merchr'),
			'remove_data_label'         => esc_html__('Remove Data on Uninstall?', 'merchr'),
			'remove_data_title'         => esc_attr__('Select if you wish the plugin to remove all saved data on uninstall, this process is not reversible.', 'merchr'),
			'remove_data_options'       => $select_options,
			'tax_data_label'            => esc_html__('Prices include tax?', 'merchr'),
			'tax_data_title'            => esc_attr__('Select if the prices entered for the products on this store include or exclude tax.', 'merchr'),
			'tax_data_options'          => $tax_select_options,
			'description_label'         => esc_html__('Product Description Location', 'merchr'),
			'description_title'         => esc_attr__('When importing our products select if the description is added to the main, short or both description areas in WooCommerce', 'merchr'),
			'description_options'       => $description_select_options,
			'settings_submit_title'     => esc_html__('Save Settings', 'merchr'),
			'settings_submit_value'     => esc_attr__('Save Settings', 'merchr'),
			'settings_nonce'            => wp_nonce_field('merchr_save_settings', '_nonce_save_settings', true, false),
		];
		
		return $replacements;
	}
}
