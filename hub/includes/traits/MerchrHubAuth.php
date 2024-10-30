<?php
/**
 * Merchr Hub Auth Trait.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/traits
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\traits;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

trait MerchrHubAuth
{
	/**
	 * Is User Logged In.
	 *
	 * @return bool
	 */
	public final function isUserLoggedIn()
	{
		$auth = false;
		if(is_user_logged_in()) {
			$auth = true;
		}
		return $auth;
	}
	
	/**
	 * Has Admin Access.
	 *
	 * @return bool
	 */
	public final function hasAdminAccess()
	{
		$auth = false;
		if(current_user_can('manage_options')) {
			$auth = true;
		}
		return $auth;
	}
}
