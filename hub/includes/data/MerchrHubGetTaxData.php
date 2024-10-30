<?php
/**
 * Merchr Hub Get Tax Data.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/data
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\data;

use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubRequest;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubGetTaxData
{
	use MerchrHubRequest;
	
	/**
	 * Make Request For Tax Regions.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForTaxRegions(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_tax_regions'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_tax_regions'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Tax Region By ID.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForTaxRegionById(array $options, int $region_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_tax_region_by_id'][0], ['region_id' => $region_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_tax_region_by_id'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Tax Classes.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForTaxClasses(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_tax_classes'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_tax_classes'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Tax Class By ID.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForTaxClassById(array $options, int $class_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_tax_class_by_id'][0], ['class_id' => $class_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_tax_class_by_id'][1], 
			$options,
			[]
		);
	}
}
