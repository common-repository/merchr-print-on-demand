<?php
/**
 * Merchr Hub i18n Class for handling translations.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubi18n
{
	/**
	 * Load the plugin text domain for translation.
	 */
	public function loadPluginTextDomain() 
	{
		load_plugin_textdomain(
			'merchr',
			false,
			MERCHR_PLUGIN_PATH . 'languages/'
		);
	}
}
