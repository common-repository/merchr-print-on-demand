<?php
/**
 * Merchr Hub Product Updater Schedules Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\scheduler;

use MerchrHub\includes\MerchrHubScheduler;
use MerchrHub\includes\actions\MerchrHubAdminProductUpdater;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubProductUpdaterSchedules extends MerchrHubScheduler
{
	protected $merchrHubProductsUpdater; // @var MerchrHubAdminProductImporter
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->merchrHubProductsUpdater = new MerchrHubAdminProductUpdater();
	}
	
	/**
	 * Run the Product Update Process.
	 */
	public function runProductUpdateSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubProductsUpdater->processProductsForUpdate();
		return;
	}
    
    
    /**
	 * Run the Product Delete Process.
	 */
	public function runProductDeleteSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubProductsUpdater->processProductsForDelete();
		return;
	}
    
    /**
	 * Run the Product Stocks Process.
	 */
	public function runProductStockSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubProductsUpdater->processProductStocks();
		return;
	}
}
