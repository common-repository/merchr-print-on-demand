<?php
/**
 * Merchr Hub Get Products and Associated Data.
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

class MerchrHubGetProductsAndAssociatedData
{
	use MerchrHubRequest;
	
	/**
	 * Make Request For Products.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForProducts(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_products'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_products'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Product By ID.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForProductById(array $options, int $product_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_product_by_id'][0], ['product_id' => $product_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_product_by_id'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Store Products.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForStoreProducts(array $options, int $store_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_store_products'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_store_products'][1], 
			$options,
			[]
		);
	}
    
    /**
	 * Make Request For Store Products New.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForStoreProductsNew(array $options, int $store_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_store_products_new'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_store_products_new'][1], 
			$options,
			[]
		);
	}
    
    /**
	 * Make Request For Store Products Deleted.
	 *
	 * @var array
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForStoreProductsDeleted(array $options, int $store_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_store_products_deleted'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_store_products_deleted'][1], 
			$options,
			[]
		);
	}
    
    
    /**
	 * Make Request For Product Stocks.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForProductStocks(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_product_stocks'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_product_stocks'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Store Product By ID.
	 *
	 * @var array
	 * @var int
	 * @var int
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForStoreProductByID(array $options, int $store_id, int $store_product_id)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['get_store_product'][0], ['store_id' => $store_id, 'store_product_id' => $store_product_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_store_product'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Categories.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForCategories(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_categories'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_categories'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Collections.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForCollections(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_collections'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_collections'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request For Industries.
	 *
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestForIndustries(array $options)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . $endpoints['get_industries'][0];
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['get_industries'][1], 
			$options,
			[]
		);
	}
	
	/**
	 * Make Request to Create Store Product.
	 *
	 * @var array
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToCreateStoreProduct(array $options, int $store_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['create_store_product'][0], ['store_id' => $store_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['create_store_product'][1], 
			$options,
			$payload
		);
	}
	
	/**
	 * Make Request to Update Store Product.
	 *
	 * @var array
	 * @var int
	 * @var int
	 * @var array
	 *
	 * @return mixed string/bool
	 */
	public function makeRequestToUpdateStoreProduct(array $options, int $store_id, int $store_product_id, array $payload)
	{
		// Get endpoints
		$endpoints = $this->getApiEndpoints();
		
		// Set final endpoint
		$endpoint = $this->getFullHubApiUrl() . MerchrHubHelpersTemplates::parseStringReplacements($endpoints['update_store_product'][0], ['store_id' => $store_id, 'store_product_id' => $store_product_id]);
		
		// Make request and return response
		return $this->makeApiRequest(
			$endpoint, 
			$endpoints['update_store_product'][1], 
			$options,
			$payload
		);
	}
}
