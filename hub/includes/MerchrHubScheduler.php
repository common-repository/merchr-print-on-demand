<?php
/**
 * Merchr Hub Scheduler Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubScheduler extends MerchrCore
{
	/**
	 * Set up the class.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
}
