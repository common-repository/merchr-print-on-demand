<?php
/**
 * Merchr Hub Product Importer Schedules Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\scheduler;

use MerchrHub\includes\MerchrHubScheduler;
use MerchrHub\includes\actions\MerchrHubAdminProductImporter;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubProductImporterSchedules extends MerchrHubScheduler
{
	protected $merchrHubProductsImporter; // @var MerchrHubAdminProductImporter
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->merchrHubProductsImporter = new MerchrHubAdminProductImporter();
	}
	
	/**
	 * Run the Product Import Process.
	 */
	public function runProductImportSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubProductsImporter->processProducts();
		return;
	}
	
	/**
	 * Run the New Products Process.
	 */
	public function runNewProductsSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
			return;
		}
		
		$this->merchrHubProductsImporter->processNewProducts();
		return;
	}
    
    /**
	 * Run the Check Admin Import Products Process.
	 */
	public function runCheckAdminImportProductsSchedule()
	{
		if($this->options['merchr_hub_connected'] == 'no') {
            return;
		}
		
		$this->merchrHubProductsImporter->processAdminProducts();
		return;
	}
}
