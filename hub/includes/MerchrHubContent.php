<?php
/**
 * Merchr Hub Content Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

use MerchrHub\includes\helpers\MerchrHubHelpersSetup;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubContent extends MerchrCore
{
	protected $connected; // @var bool
	protected $imported; // @var bool
	
	/**
	 * Set up the class.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Set if connected
		$this->connected = MerchrHubHelpersSetup::merchrHubStoreConnected($this->options);
		
		// Set if products have been imported
		$this->imported = MerchrHubHelpersSetup::merchrHubProductsImported($this->options);
	}
}
