<?php
/**
 * Merchr Hub Key Class, stores the encryption key.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use Defuse\Crypto\Key;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubKey
{
	/**
	 * Return ASCII Safe Encryption key.
	 *
	 * @return string
	 */
	public static function returnAsciiSafeKey() 
	{
		$key = '';
		if($key === '') {
			return '';
		} else {
			return Key::loadFromAsciiSafeString($key);
		}
	}
}
