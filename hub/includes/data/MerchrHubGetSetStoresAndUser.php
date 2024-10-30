<?php
/**
 * Merchr Hub Set Stores And User.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/data
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\data;

use MerchrHub\includes\traits\MerchrHubRequest;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubGetSetStoresAndUser
{
	use MerchrHubRequest;
	
	/**
	 * Make Request For Stores
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForStores(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['me']['my_store_list'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['me']['my_store_list'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request to Create Store
	 *
	 * @var array
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateStore(array $options, array$payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['create_store'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_store'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Create User
	 *
	 * @var array
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateUser(array $options, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['create_user'][0];
		
		// No bearer token required for create user
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_user'][1], 
			$options,
			$payload,
			false
		);
	}
}
