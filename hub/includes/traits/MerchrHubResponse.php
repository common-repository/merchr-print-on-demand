<?php
/**
 * Merchr Hub Response Trait.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/traits
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\traits;


// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

trait MerchrHubResponse
{
	/**
	 * Send AJAX Content
	 * 
	 * @param array
	 * @param bool optional
	 */
	public final function sendAJAXContent(array $content, bool $raw = false)
	{
		if($raw) {
			// Echo raw response
			echo $content;
		} else {
			// Echo JSON encoded content
			echo json_encode($content);
		}
		
		// With WordPress AJAX requests you must exit or a trailing 
		// 0 will be passed along with your response
		exit;
	}
}
