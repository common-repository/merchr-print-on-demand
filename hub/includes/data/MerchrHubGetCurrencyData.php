<?php
/**
 * Merchr Hub Get Currency Data.
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

class MerchrHubGetCurrencyData
{
	use MerchrHubRequest;
	
	/**
	 * Make Request For Tax Regions.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForCurrencies(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_currencies'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_currencies'][1], 
			$options,
			[]
		);
	}
}
