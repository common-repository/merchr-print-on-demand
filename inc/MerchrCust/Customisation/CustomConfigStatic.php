<?php

namespace MerchrCust\Customisation;

class CustomConfigStatic {

	const admin_page_menu_slug = 'edit.php?post_type=product';
	const admin_page_slug = 'merchrcust';
	const autofill_codes = '<span>Auto-fill codes:</span> <code>##user_first_name##</code>, <code>##user_last_name##</code>, <code>##user_initials##</code>, <code>##user_initials_with_periods##</code>';

	const default_custom_label = 'Text to appear';
	const optionkey_default_custom_label = 'merchrcust_default_custom_label';

	const default_customise_all_label = 'Personalise everything';
	const optionkey_default_customise_all_label = 'merchrcust_default_customise_all_label';

	const default_custom_text = '##user_first_name##';
	const optionkey_default_custom_text = 'merchrcust_default_custom_text';
	
	const default_custom_fallback_text = '';
	const optionkey_default_custom_fallback_text = 'merchrcust_default_custom_fallback_text';
	
	const productlevel_textfield_default = 'merchrcust_textfield_default';
	const productlevel_textfield_label = 'merchrcust_textfield_label';
	const productlevel_textfield_line_breaks = 'merchrcust_textfield_line_breaks';
	
	const productlevel_allow_images = 'merchrcust_allow_images';
	
	public static function getCustomiseAllLabel(){
		return esc_attr(self::getOption('default_customise_all_label'));
	}
	
	public static function getCustomLabel(){
		return esc_attr(self::getOption('default_custom_label'));
	}
	
	public static function getCustomText(){
		return esc_attr(self::getOption('default_custom_text'));
	}
	
	public static function getFallbackCustomText(){
		return esc_html(self::getOption('default_custom_fallback_text'));
	}
	
	/*
	 * Get default stored in WP options, fallback to default as set in constants
	 */
	private static function getOption($const){
		$class_key = 'optionkey_' . $const; // the const key used in this class
		$option_key = constant("self::$class_key"); // the get_option key
		$option = get_option($option_key);
		if (!empty($option)){
			return $option;
		}
		return constant("self::$const");
		
	}
}
