<?php
/**
 * Merchr Hub Request Trait.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/traits
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\traits;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use MerchrHub\includes\MerchrHubKey;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

trait MerchrHubRequest
{
	protected $api_url; // @var string
	protected $api_url_fallback; // @var string
	protected $api_version; // var string
	protected $api_store_type; // var int
	protected $request_limit; // var string/int
	protected $api_endpoints; // var array
	protected $api_built_url; // var string
	protected $last_errors; // var string
	private $bearer_token; // var string
	
	/**
	 * Setup Connection.
	 */
	public final function setupConnection()
	{
		// Set vars
		$this->api_url = MERCHR_HUB_API_URL;
		$this->api_url_fallback = MERCHR_HUB_API_URL_FAILOVER;
		$this->api_version = 'v1';
		$this->api_store_type = 1; // WooCommerce
		
		if($this->request_limit === null || $this->request_limit === '') {
			$this->request_limit = 48;
		}
		
		// Build full API URL 
		$this->api_built_url = "{$this->api_url}/api/rest/{$this->api_version}";
			
		// Set all required endpoints and their type
		$this->api_endpoints = [
			'health_check'                  => ['/healthcheck', 'GET'], // No bearer token required
			'get_categories'                => ['/categories?limit=' . $this->request_limit, 'GET'],
			'get_collections'               => ['/collections?limit=' . $this->request_limit, 'GET'],
			'get_industries'                => ['/industries?limit=' . $this->request_limit, 'GET'],
			'get_tax_regions'               => ['/tax-regions?limit=' . $this->request_limit, 'GET'],
			'get_tax_region_by_id'          => ['/tax-regions/{$region_id}?limit=' . $this->request_limit, 'GET'],
			'get_tax_classes'               => ['/tax-classes?limit=' . $this->request_limit, 'GET'],
			'get_tax_class_by_id'           => ['/tax-classes/{$class_id}?limit=' . $this->request_limit, 'GET'],
			'get_product_options'           => ['/product-options?limit=' . $this->request_limit, 'GET'],
			'get_currencies'                => ['/currencies?limit=' . $this->request_limit, 'GET'],
			'get_shipping_carrier_services' => ['/shipping-carriers?limit=' . $this->request_limit, 'GET'],
			'get_design_artboards'          => ['/design-artboards?limit=' . $this->request_limit, 'GET'],
			'get_products'                  => ['/products?limit=' . $this->request_limit, 'GET'],
			'get_product_by_id'             => ['/products/{$product_id}', 'GET'],
			'get_store_by_id'               => ['/stores/{$store_id}', 'GET'],
			'get_store_products'            => ['/stores/{$store_id}/products?limit=' . $this->request_limit, 'GET'],
			'get_store_product'             => ['/stores/{$store_id}/products/{$store_product_id}', 'GET'],
            'get_store_products_new'        => ['/stores/{$store_id}/products?limit=10', 'GET'],
            'get_store_products_deleted'    => ['/stores/{$store_id}/products?deleted=1&limit=all', 'GET'],
			'create_store_product'          => ['/stores/{$store_id}/products', 'POST'],
			'update_store_product'          => ['/stores/{$store_id}/products/{$store_product_id}', 'PUT'],
			'create_store'                  => ['/stores-create', 'POST'],
			'create_user'                   => ['/users', 'POST'], // No bearer token required
			'create_customer'               => ['/stores/{$store_id}/customers', 'POST'],
			'create_customer_address'       => ['/stores/{$store_id}/customers/{$customer_id}/addresses', 'POST'],
			'create_new_order'              => ['/stores/{$store_id}/orders', 'POST'],
			'get_orders'                    => ['/stores/{$store_id}/orders?limit=' . $this->request_limit, 'GET'],
			'create_new_order_item'         => ['/stores/{$store_id}/orders/{$order_id}/items', 'POST'],
			'update_order_item'             => ['/stores/{$store_id}/orders/{$order_id}/items/{$order_item_id}', 'PUT'],
			'get_order_item_status'         => ['/stores/{$store_id}/orders/{$order_id}/items/{$order_item_id}/statuses?limit=' . $this->request_limit, 'PUT'],
            'get_product_stocks'            => ['/product-stocks', 'GET'],
			'me'                            => [
			                                       'who_am_i'            => ['/users', 'GET'],
										           'my_store_list'       => ['/my/stores', 'GET'],
										           'my_create_store'     => ['/my/stores', 'POST'],
										           'my_new_access_token' => ['/my/access-tokens', 'POST'],
			                                   ],
		];
	}
	
	/**
	 * Set Request Limit.
	 *
	 * Call this method before setupConnection().
	 *
	 * @param int
	 */
	public final function setRequestLimit(int $limit)
	{
		$this->request_limit = $limit;
	}
	
	/**
	 * Get Full HUB API URL.
	 */
	public final function getFullHubApiUrl()
	{
		return $this->api_built_url;
	}
	
	/**
	 * Get API Endpoints.
	 */
	public final function getApiEndpoints()
	{
		return $this->api_endpoints;
	}
	
	/**
	 * Get API Store Type.
	 */
	public final function getApiStoreType()
	{
		return $this->api_store_type;
	}
	
	/**
	 * Get Last Errors.
	 */
	public final function getRequestLastErrors()
	{
		return $this->last_errors;
	}
	
	
	/**
	 * Run API Connection Test.
	 *
	 * @return bool
	 */
	public final function runApiConnectionTest()
	{
		return true;
	}
	
	/**
	 * Make API Request.
	 *
	 * @param string
	 * @param string
	 * @param array
	 * @param array optional
	 * @param bool optional
	 * @param bool optional
	 *
	 * @return mixed string/bool
	 */
	public final function makeApiRequest(string $endpoint, string $type, array $options, array $payload = [], bool $use_auth = true, bool $force_json = false)
	{
		$body = '';
		$type = strtoupper($type);
		
		// Header options
		$http_headers = [
			'Cache-Control' => 'no-cache',
			'Connection'    => 'Keep-Alive',
		];
		
		// Check if auth required
		if($use_auth) {
			// Get bearer token
			if(!$token = $this->getBearerToken($options)) {
				return false;
			}
			$http_headers['Authorization'] = "Bearer {$token}";
		}
		
		// Process types, check force JSON first and PUT
		if($force_json || $type === 'PUT') {
			// Send payload as JSON
			$body = json_encode($payload);
			
			// Set appropriate headers
			$http_headers['Content-Type']   = 'application/json';
			$http_headers['Content-Length'] = strlen($body);
		} else if($type === 'POST') {
			// Set array as payload
			$body = $payload;
			
			// Set appropriate header
			$http_headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}
		
		// Lets deal with the natively supported methods POST and GET first.
		if($type === 'POST') {
			$args = [
				'body'    => $payload,
				'headers' => $http_headers,
			];
			$response = wp_remote_post($endpoint, $args);
		} else if($type === 'GET') {
			$args = [
				'headers' => $http_headers,
			];
			$response = wp_remote_get($endpoint, $args);
		} else {
			// Process PUT and DELETE requests
			$args = [
				'method'  => $type,
				'body'    => $body,
				'headers' => $http_headers,
			];
			$response = wp_remote_request($endpoint, $args);
		}
		
		// Set HTTP code
		$http_code = wp_remote_retrieve_response_code($response);
		
		// Get the body content
		$content = wp_remote_retrieve_body($response);
		
		// Parse Errors if any
		// Merchr API always returns JSON content except uncaught exceptions and error 5**
		$this->last_errors = $this->parseApiErrors($content);
		
		// Return data
		return $content;
	}
	
	/**
	 * Parse API Errors.
	 * 
	 * @param mixed
	 * 
	 * @return string
	 */
	public final function parseApiErrors($response)
	{
		$error_msgs = [];
        $response = json_decode($response, true);
        
        if($response !== null) {
            if(isset($response['errors'])) {
                foreach($response['errors'] as $parent_error) {
                    foreach($parent_error as $error) {
                        $error_msgs[] = esc_html($error);
                    }
                }
            }
        } else {
            $error_msgs[] = esc_html($response);
        }
        
		return implode("\n", $error_msgs);
	}
	
	/**
	 * Get Bearer Token.
	 *
	 * @param array
	 *
	 * @return mixed string/bool
	 */
	private final function getBearerToken(array $options)
	{
		$token = $options['merchr_hub_api_key'];
		try {
			$token_decrypted = Crypto::decrypt($token, MerchrHubKey::returnAsciiSafeKey());
		} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
			return false;
		}
		return $token_decrypted;
	}
}
