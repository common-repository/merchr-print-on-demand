<?php
/**
 * Merchr Hub Order Schedules Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\scheduler;

use MerchrHub\includes\MerchrHubScheduler;
use MerchrHub\includes\actions\MerchrHubAdminOrders;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubOrderSchedules extends MerchrHubScheduler
{
	protected $merchrHubAdminOrders; // @var MerchrHubAdminOrders
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->merchrHubAdminOrders = new MerchrHubAdminOrders();
	}
	
	/**
	 * Run the paid order schedule.
	 */
	public function runPaidOrderSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubAdminOrders->processPaidOrders();
		return;
	}
	
	/**
	 * Run the order status schedule.
	 */
	public function runOrderStatusSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubAdminOrders->processOrderStatus();
		return;
	}
}
