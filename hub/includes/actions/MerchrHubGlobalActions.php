<?php
/**
 * Merchr Hub Global Actions Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use Defuse\Crypto\Key;
use MerchrHub\includes\MerchrHubActions;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubGlobalActions extends MerchrHubActions
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
    /**
	 * Set cURL Timeouts.
	 */
	public function setCurlTimeouts($handle)
	{
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
	}
}
