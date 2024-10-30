<?php
/**
 * Merchr Hub Helpers Templates Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersTemplates
{
	/**
	 * Fetch Template Contents.
	 *
	 * @param string
	 * @param string
	 *
	 * @return string
	 */
	public static function fetchTemplateContents(string $template, string $path) 
	{
		$contents = '';
		$template_path = $path . $template;
		
		if(is_readable($template_path)) {
			$contents = @file_get_contents($template_path);
		}
		
		return $contents;
	}
	
	/**
	 * Replace placeholder contents of a passed string.
	 *
	 * @param string
	 * @param array
	 * @param array optional
	 *
	 * @return string
	 */
	public static function parseStringReplacements(string $string, array $replacements, array $placeholders = ['{$', '}'])
	{
		$string = trim($string);
		if($string === '' || empty($replacements)) {
            return $string;
        }
		
		$needles = [];
        $replace = [];
        foreach($replacements as $key => $value) {
            $needles[] = $placeholders[0] . $key . $placeholders[1];
			$replace[] = $value;
        }
		
        return str_ireplace($needles, $replace, $string);
	}
	
	/**
	 * Fetch Logged In Users Name.
	 *
	 * @return string
	 */
	public static function fetchLoggedInUsersName() 
	{
		$user_name = '';
		$current_user = wp_get_current_user();
		$first_name = trim($current_user->user_firstname);
		$display_name = trim($current_user->display_name);
		
		if($first_name !== '') {
			$user_name = esc_html(self::mbUcfirst($current_user->user_firstname));
		} else if($display_name !== '') {
			$user_name = esc_html(self::mbUcfirst($current_user->display_name));
		}
		
		return $user_name;
	}
	
	/**
	 * Multibyte ucfirst
	 *
	 * @param string
	 *
	 * @return string
	 */
	public static function mbUcfirst(string $string)
	{
		return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	}
	
	/**
	 * Return Page Wrap and Title.
	 *
	 * @param string
	 * @param string
	 */
	public static function returnPageWrapAndTitle(string $title, string $content) 
	{
		return '<div class="wrap merchr-wrap">
			<h2 class="merchr-title">' . esc_html($title) . '</h2>
			' . $content . '
		</div>'; 
	}
	
	/**
	 * Get Custom Logo URL.
	 *
	 * @return string
	 */
	public static function getCustomLogoUrl()
	{
		$result = '';
		$custom_logo_id = get_theme_mod('custom_logo');
		$image = wp_get_attachment_image_src($custom_logo_id, 'full');
		if(isset($image[0])) {
			$result = $image[0];
		}
		return $result;
	}
}
