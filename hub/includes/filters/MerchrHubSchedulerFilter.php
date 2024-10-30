<?php
/**
 * Merchr Hub Scheduler Filter Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/hooks
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\filters;

use MerchrHub\includes\MerchrHubFilters;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubSchedulerFilter extends MerchrHubFilters
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Increase Time Limit.
	 *
	 * @param int
	 */
	public function increaseTimeLimit()
	{
		return 3600;
	}
}
