<?php
/**
 * Merchr Hub Order Requests.
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

class MerchrHubOrderRequests
{
	use MerchrHubRequest;
	
	/**
	 * Make Request to Get Orders.
	 *
	 * @var array
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToGetOrders(array $options, int $store_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_orders'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_orders'][1], 
			$options
		);
	}
	
	/**
	 * Make Request to Create Order.
	 *
	 * @var array
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateOrder(array $options, int $store_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['create_new_order'][0], ['store_id' => $store_id]);
        
        // Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_new_order'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Create Order Item.
	 *
	 * @var array
	 * @var int
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateOrderItem(array $options, int $store_id, int $order_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['create_new_order_item'][0], ['store_id' => $store_id, 'order_id' => $order_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_new_order_item'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Update Order Item.
	 *
	 * @var array
	 * @var int
	 * @var int
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToUpdateOrderItem(array $options, int $store_id, int $order_id, int $order_item_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$replacements = [
			'store_id' => $store_id, 
			'order_id' => $order_id,
			'order_item_id' => $order_item_id
		];
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['update_order_item'][0], $replacements);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['update_order_item'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Get Order Item Status'.
	 *
	 * @var array
	 * @var int
	 * @var int
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToGetOrderItemStatus(array $options, int $store_id, int $order_id, int $order_item_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$replacements = [
			'store_id' => $store_id, 
			'order_id' => $order_id,
			'order_item_id' => $order_item_id
		];
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_order_item_status'][0], $replacements);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_order_item_status'][1], 
			$options
		);
	}
	
	/**
	 * Make Request to Create Customer.
	 *
	 * @var array
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateCustomer(array $options, int $store_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['create_customer'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_customer'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Create Customer Address.
	 *
	 * @var array
	 * @var int
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateCustomerAddress(array $options, int $store_id, int $customer_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['create_customer_address'][0], ['store_id' => $store_id, 'customer_id' => $customer_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_customer_address'][1], 
			$options,
			$payload
		);
	}
}
