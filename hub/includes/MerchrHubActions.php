<?php
/**
 * Merchr Hub Actions Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use MerchrHub\includes\traits\MerchrHubAuth;
use MerchrHub\includes\traits\MerchrHubResponse;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubActions extends MerchrCore
{
	use MerchrHubAuth;
	use MerchrHubResponse;
	
	protected $result; // @var array
	protected $nonce_names; // @var array
	
	/**
	 * Set up the class.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Verify Request.
	 *
	 * @param mixed (string/array)
	 * @param bool optional
	 */
	protected function verifyRequest($nonce, bool $pulic = false)
	{
		// Nonce Name and Fieldname
		list($req, $name) = $this->getNonceRequestNameAndFieldname($nonce);
		
		// Verify nonce
		if(!wp_verify_nonce($req, $name)) {
			$this->result['msg'] = __('Sorry something went wrong, your session may have expired, refresh the page and try again!', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Admin only
		if(!$pulic) {
			// Check user is logged in
			if(!$this->isUserLoggedIn()) {
				$this->result['msg'] = __('You must be logged in to access this feature!', 'merchr');
				$this->sendAJAXContent($this->result);
			}
			
			// Verify user is an admin
			if(!$this->hasAdminAccess()) {
				$this->result['msg'] = __('You need administration privileges to access this feature!', 'merchr');
				$this->sendAJAXContent($this->result);
			}
		}
	}
	
	/**
	 * Get Nonce Request Name and Fieldname
	 *
	 * @param mixed (string/array)
	 *
	 * @return array
	 */
	private function getNonceRequestNameAndFieldname($nonce)
	{
		// Check if page or form nonce
		if(is_array($nonce)) { // Form
			$req  = $_REQUEST[$nonce[1]];
			$name = $nonce[0];
		} else { // Page
			$req  = $_REQUEST['nonce'];
			$name = $nonce;
		}
		
		return [$req, $name];
	}
}
