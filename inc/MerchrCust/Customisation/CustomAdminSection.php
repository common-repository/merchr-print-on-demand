<?php

namespace MerchrCust\Customisation;

use MerchrCust\Customisation\CustomHelperStatic;

class CustomAdminSection {
	
    public function __construct() 
	{
		// Hook into the admin menu
		add_action( 'admin_menu', [ $this, 'createPluginSettingsPage' ] );
		add_action( 'admin_init', [ $this, 'setupSections' ] );
		add_action( 'admin_init', [ $this, 'setupFields' ] );
	}
	
	public function createPluginSettingsPage() 
	{
		// Add the menu item and page
		$page_title = 'Product Customisation';
		$menu_title = 'Customisation';
		$capability = 'manage_options';
		$contents = [ $this, 'pluginSettingsPageContent' ];

		add_submenu_page(
			CustomConfigStatic::admin_page_menu_slug,
			$page_title,
			$menu_title,
			$capability,
			CustomConfigStatic::admin_page_slug,
			$contents
		);
	}
	
	public function pluginSettingsPageContent() 
	{ 
		$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'defaults';
		// reduce to only allowed values only
		switch ($active_tab){
			case 'defaults':
			case 'settings':
				break;
		default;
			$active_tab = 'defaults';
			break;
		}
		$url = CustomConfigStatic::admin_page_menu_slug . '&page=' . CustomConfigStatic::admin_page_slug . '&tab=';
		?>
		<div class="wrap">
			<h2 class="merchr-title">Product Customisation Settings</h2>
			<form method="post" action="options.php">
				<div class="wrap">
					<h2 class="nav-tab-wrapper">
						<a href="<?php echo esc_url($url . 'defaults'); ?>" class="nav-tab <?php echo $active_tab === 'defaults' ? 'nav-tab-active': '' ?>">Defaults</a>
						<a href="<?php echo esc_url($url . 'settings'); ?>" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active': '' ?>">Settings</a>
					</h2>
					<?php
						settings_fields( 'page_' . $active_tab );
						do_settings_sections( 'page_' . $active_tab );
						submit_button();
					?>
				</div>
			</form>
		</div> <?php
	}

	public function setupSections() 
	{
		add_settings_section( 'section_defaults', 'Defaults', [ $this, 'sectionCallback' ], 'page_defaults' );
		add_settings_section( 'section_settings', 'Settings', [ $this, 'sectionCallback' ], 'page_settings' );
	}

	public function sectionCallback( $arguments ) 
	{
		return;
		switch( $arguments['id'] ){
			case 'section_defaults':
				//echo 'Description of section';
				break;
			case 'section_settings':
				//echo 'Description of section';
				break;
		}
	}
	
	public function setupFields() 
	{
		$this->setupFieldsDefaults();
		$this->setupFieldsSettings();
	}
	
	private function setupFieldsDefaults() 
	{
		$fields = [
			[
				'uid' => CustomConfigStatic::optionkey_default_custom_label,
				'label' => esc_html__('Default label', 'merchr'),
				'section' => 'section_defaults',
				'type' => 'text',
				'options' => false,
				//'placeholder' => '##user_name##',
				'helper' => esc_html__('The default label above the custom text field. This can be overridden per product.', 'merchr'),
				'default' => CustomConfigStatic::default_custom_label,
			],
			[
				'uid' => CustomConfigStatic::optionkey_default_custom_text, 
				'label' => esc_html__('Default text', 'merchr'),
				'section' => 'section_defaults',
				'type' => 'textarea',
				'options' => false,
				'helper' => esc_html__('The default text in the custom text field. This can be overridden per product.', 'merchr'),
				'supplemental' => CustomConfigStatic::autofill_codes,
				'default' => CustomConfigStatic::default_custom_text,
				'class'	=> 'js__codeToTextarea',
			],
			[
				'uid' => CustomConfigStatic::optionkey_default_custom_fallback_text, 
				'label' => esc_html__('Default fallback text', 'merchr'),
				'section' => 'section_defaults',
				'type' => 'text',
				'options' => false,
				'helper' => esc_html__('When the default text renders empty.', 'merchr'),
				'default' => CustomConfigStatic::default_custom_fallback_text,
			],
			[
				'uid' => CustomConfigStatic::optionkey_default_customise_all_label, 
				'label' => esc_html__('Customise All Label', 'merchr'),
				'section' => 'section_defaults',
				'type' => 'text',
				'helper' => esc_html__('The label that appears before the input field to customise all products on a page.', 'merchr'),
				'default' => CustomConfigStatic::default_customise_all_label,
				'class'	=> 'js__codeToTextarea',
			],
		];
		$this->registerSettingsFields($fields, 'page_defaults');
	}
	
	private function setupFieldsSettings() 
	{
		$fields = [
			[
				'uid'		     => 'merchrcust_public_api_key', 
				'label'		     => esc_html__('Public API Key', 'merchr'),
				'checkbox_label' => esc_html__('Public API Key', 'merchr'),
				'section'	     => 'section_settings',
				'type'		     => 'text',
				'default'	     => '',
			],
		];
		$this->registerSettingsFields($fields, 'page_settings');
	}
	
	private function registerSettingsFields($fields, $page)
	{
		foreach( $fields as $field ){
			add_settings_field( $field['uid'], $field['label'], [ $this, 'fieldCallback' ], $page, $field['section'], $field );
			register_setting( $page, $field['uid'] );
		}
		
	}
	
	public function fieldCallback( $arguments ) 
	{
		$value = get_option( $arguments['uid'] ); // Get the current value, if there is one
		if (!$value && $arguments['type'] !== 'checkbox') { // If no value exists
			$value = $arguments['default']; // Set to our default
		}

		if (!isset($arguments['placeholder'])){
			$arguments['placeholder'] = '';
		}
		
		// Check which type of field we want
		switch( $arguments['type'] ){
			case 'checkbox': // If it is a text field
				$checked = '';
				if ($value === $arguments['checkbox_value']){
					$checked = 'checked';
				}
				printf( '<label><input name="%1$s" id="%1$s" type="%2$s" value="%3$s" %5$s /> %4$s</label>', esc_attr($arguments['uid']), $arguments['type'], esc_attr($arguments['checkbox_value']), esc_html($arguments['checkbox_label']), $checked );
				break;
			case 'text': // If it is a text field
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', esc_attr($arguments['uid']), $arguments['type'], $arguments['placeholder'], esc_attr($value) );
				break;
			case 'textarea': // If it is a textarea
				printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', esc_attr($arguments['uid']), $arguments['placeholder'], esc_attr($value) );
				break;
			case 'select': // If it is a select dropdown
				if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
					$options_markup = '';
					foreach( $arguments['options'] as $key => $label ){
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( esc_attr($value), esc_attr($key), false ), $label );
					}
					printf( '<select name="%1$s" id="%1$s">%2$s</select>', esc_attr($arguments['uid']), $options_markup );
				}
				break;
		}

		// If there is help text
		if(isset($arguments['helper'])){
			printf( '<span class="helper"> %s</span>', esc_html($arguments['helper']) ); // Show it
		}

		// If there is supplemental text
		if(isset($arguments['supplemental'])){
			printf(
				'<p class="description">%s</p>', 
				wp_kses(
					$arguments['supplemental'], 
					[
						'span' => [], 
						'code' => [ 
							'style' => []
						]
					]
				) 
			);
		}
	}
}
