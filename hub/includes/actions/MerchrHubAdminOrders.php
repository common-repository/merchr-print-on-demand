<?php
/**
 * Merchr Hub Admin Orders.
 *
 * @since      1.0.2
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use MerchrHub\includes\MerchrHubActions;
use MerchrHub\includes\data\MerchrHubGetCurrencyData;
use MerchrHub\includes\data\MerchrHubOrderRequests;
use MerchrHub\includes\helpers\MerchrHubHelpersCurrencies;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminOrders extends MerchrHubActions
{
	use MerchrHubDatabaseInteraction;
	
	protected $request; // @var MerchrHubOrderRequests
    protected $currencies_request; // @var MerchrHubGetCurrencyData
    protected $store_base_currency; // @var string
	
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
        
        // Set store base currency
        $this->store_base_currency = MerchrHubHelpersCurrencies::getStoreCurrency();
		
		// Set request and setup connection
		$this->request = new MerchrHubOrderRequests();
		$this->request->setupConnection();
        
        // Set currencies request and setup connection
		$this->currencies_request = new MerchrHubGetCurrencyData();
		$this->currencies_request->setupConnection();
	}
	
	/**
	 * Process Paid Orders.
	 */
	public function processPaidOrders()
	{
		// Fetch order details
		$orders_todo = $this->wpdb->get_results(
			"SELECT `id`, `order_id`, `order_total`, `merchr_order_id`, `merchr_customer_id`, `merchr_customer_address_id` 
			 FROM `{$this->merchr_tables->orders}` 
			 WHERE `processed`='0' AND `failed`='0' AND `cancelled`='0'"
		);
        
        if(count($orders_todo) > 0) {
            $merchr_options = get_option('merchr_hub_options');
            $currencies = json_decode($this->currencies_request->makeRequestForCurrencies($merchr_options), true);
            $currencies = $currencies['data'];
            
            // Process each order
            foreach($orders_todo as $order_todo) {
                $order_details = [];
                $id                         = (int) $order_todo->id;
                $order_id                   = (int) $order_todo->order_id;
                $merchr_order_id            = trim($order_todo->merchr_order_id);
                $merchr_customer_id         = trim($order_todo->merchr_customer_id);
                $merchr_customer_address_id = trim($order_todo->merchr_customer_address_id);
                $order_total                = $order_todo->order_total;
                
                // Get store ID and user ID
                $order_details['store_id']    = (int) $merchr_options['merchr_hub_store_id'];
                $order_details['user_id'] = (int) $merchr_options['merchr_hub_user_id'];
                
                // Get the WC order
                $order = wc_get_order($order_id);
                
                // General Order Details
                $order_details['order_id']       = $order_id;
                $order_details['order_total']    = $order_total;
                $order_details['order_status']   = $order->get_status();
                $order_details['currency']       = $order->get_currency();
                $order_details['payment_method'] = $order->get_payment_method();
                $order_details['payment_title']  = $order->get_payment_method_title();
                $order_details['date_created']   = $order->get_date_created()->date('Y-m-d H:i:s');;
                $order_details['date_modified']  = $order->get_date_modified()->date('Y-m-d H:i:s');;
                $order_details['discount_total'] = $order->get_discount_total();
                $order_details['discount_tax']   = $order->get_discount_tax();
                $order_details['shipping_total'] = $order->get_shipping_total();
                $order_details['shipping_tax']   = $order->get_shipping_tax();
                $order_details['total']          = $order->get_total();
                $order_details['total_tax']      = $order->get_total_tax();

                // Process currency
                $order_details['currency_id'] = 1;
                foreach($currencies as $currency) {
                    $code = strtoupper(trim($currency['code']));
                    if($code == $this->store_base_currency) {
                        $order_details['currency_id'] = (int) $currency['id'];
                        $order_details['currency_details'] = [
                            'id' => (int) $currency['id'],
                            'code' => $currency['code'],
                            'symbol' => $currency['symbol']
                        ];
                        break;
                    }
                }
                
                // Get the Customer ID (User ID, 0 if guest checkout)
                $order_details['customer_id'] = (int) $order->get_customer_id();
                
                // Get the Customer billing email
                $order_details['email']  = trim($order->get_billing_email());
                
                // Get the Customer billing phone
                $order_details['phone']  = trim($order->get_billing_phone());
                
                // Customer billing information details
                $order_details['billing']['first_name'] = trim($order->get_billing_first_name());
                $order_details['billing']['last_name']  = trim($order->get_billing_last_name());
                $order_details['billing']['company']    = trim($order->get_billing_company());
                $order_details['billing']['address_1']  = trim($order->get_billing_address_1());
                $order_details['billing']['address_2']  = trim($order->get_billing_address_2());
                $order_details['billing']['city']       = trim($order->get_billing_city());
                $order_details['billing']['state']      = trim($order->get_billing_state());
                $order_details['billing']['postcode']   = trim($order->get_billing_postcode());
                $order_details['billing']['country']    = trim($order->get_billing_country());
                
                // Customer shipping information details
                $order_details['shipping']['first_name'] = trim($order->get_shipping_first_name());
                $order_details['shipping']['last_name']  = trim($order->get_shipping_last_name());
                $order_details['shipping']['company']    = trim($order->get_shipping_company());
                $order_details['shipping']['address_1']  = trim($order->get_shipping_address_1());
                $order_details['shipping']['address_2']  = trim($order->get_shipping_address_2());
                $order_details['shipping']['city']       = trim($order->get_shipping_city());
                $order_details['shipping']['state']      = trim($order->get_shipping_state());
                $order_details['shipping']['postcode']   = trim($order->get_shipping_postcode());
                $order_details['shipping']['country']    = trim($order->get_shipping_country());
                
                // The country is not always provided by Woo for the billing details
                // So we check for that here and use shipping country if empty
                if($order_details['billing']['country'] === '') {
                    $order_details['billing']['country'] = $order_details['shipping']['country'];
                }
                
                // Build customer details
                $order_details['customer']['first_name'] = $order_details['shipping']['first_name'] ?: $order_details['billing']['first_name'];
                $order_details['customer']['last_name'] = $order_details['shipping']['last_name'] ?: $order_details['billing']['last_name'];
                $order_details['customer']['company'] = $order_details['shipping']['company'] ?: $order_details['billing']['company'];
                $order_details['customer']['address_1'] = $order_details['shipping']['address_1'] ?: $order_details['billing']['address_1'];
                $order_details['customer']['address_2'] = $order_details['shipping']['address_2'] ?: $order_details['billing']['address_2'];
                $order_details['customer']['city'] = $order_details['shipping']['city'] ?: $order_details['billing']['city'];
                $order_details['customer']['state'] = $order_details['shipping']['state'] ?: $order_details['billing']['state'];
                $order_details['customer']['postcode'] = $order_details['shipping']['postcode'] ?: $order_details['billing']['postcode'];
                $order_details['customer']['country'] = $order_details['shipping']['country'] ?: $order_details['billing']['country'];
                
                // Get order items
                $i = 0;
                $item_types = ['line_item', 'shipping'];
                $none_merchr_items = [];
                foreach($order->get_items($item_types) as $item_id => $item) {
                    $item_type = $item->get_type();
                    if($item_type == 'line_item') {
                        // Product ID
                        $item_product_id = (int) $item->get_product_id();
                        
                        // Set Merchr product and store product ID
                        $merchr_hub_product_id = (int) get_post_meta($item_product_id, 'merchr_hub_product_id', true );
                        $merchr_hub_store_product_id = (int) get_post_meta($item_product_id, 'merchr_hub_store_product_id', true );
                        
                        // Check this is a Merchr product
                        if($merchr_hub_product_id === 0 || $merchr_hub_store_product_id === 0) {
                            // Add to none merchr array
                            $none_merchr_items[] = [
                                'id'       => $item_product_id,
                                'quantity' => $item->get_quantity(),
                                'subtotal' => $item->get_subtotal(),
                                'total'    => $item->get_total()
                            ];
                            
                            // Skip this product
                            continue;
                        }
                        
                        // Set marketplace product ID
                        $order_details['item'][$i]['marketplace_product_id'] = (int) $item_product_id;
                        
                        $order_details['item'][$i]['id']       = $item_id;
                        $order_details['item'][$i]['name']     = trim($item->get_name());
                        $order_details['item'][$i]['quantity'] = $item->get_quantity();
                        $order_details['item'][$i]['subtotal'] = $item->get_subtotal();
                        $order_details['item'][$i]['total']    = $item->get_total();
                        
                        // Get item customisation metadata
                        $order_details['item'][$i]['customisation']['type']      = wc_get_order_item_meta($item_id, '_merchrcust_customisation_type', true);
                        $order_details['item'][$i]['customisation']['thumbnail'] = wc_get_order_item_meta($item_id, '_merchrcust_thumbnail', true);
                        $order_details['item'][$i]['customisation']['text']      = wc_get_order_item_meta($item_id, '_merchrcust_custom_text', true);
                        $order_details['item'][$i]['customisation']['colour']    = wc_get_order_item_meta($item_id, '_merchrcust_text_color', true);
                        $order_details['item'][$i]['customisation']['font']      = wc_get_order_item_meta($item_id, '_merchrcust_font_family', true);
                        
                        // Get design data
                        $order_details['item'][$i]['design']['id']          = get_post_meta($item_product_id, 'merchr_hub_design_id', true);
                        $order_details['item'][$i]['design']['media_id']    = get_post_meta($item_product_id, 'merchr_hub_design_media_id', true);
                        $order_details['item'][$i]['design']['artboard_id'] = get_post_meta($item_product_id, 'merchr_hub_design_media_artboard_id', true);
                        
                        // Get product object and original SKU
                        $product = $item->get_product();
                        $order_details['item'][$i]['marketplace_sku'] = $product->get_sku();
                        $order_details['item'][$i]['product_sku']     = get_post_meta($item_product_id, 'merchr_hub_original_sku', true );
                        
                        // Set Merchr product and store product ID's
                        $order_details['item'][$i]['merchr_hub_product_id']       = $merchr_hub_product_id;
                        $order_details['item'][$i]['merchr_hub_store_product_id'] = $merchr_hub_store_product_id;
                        
                        if($product->is_type('variation')) {
                            $order_details['item'][$i]['is_variation'] = 1;
                            
                            // Variation ID
                            $item_product_variation_id = $item->get_variation_id();
                            $order_details['item'][$i]['marketplace_product_variation_id'] = (int) $item_product_variation_id;
                            
                            // Original Variant SKU
                            $order_details['item'][$i]['variant_sku'] = get_post_meta($item_product_variation_id, 'merchr_hub_original_variant_sku', true);
                            
                            // Get the variation attributes
                            $variation_attributes = $product->get_variation_attributes();
                            foreach($variation_attributes as $attribute_taxonomy => $term_slug) {
                                // Get product attribute name or taxonomy
                                $taxonomy = str_replace('attribute_', '', $attribute_taxonomy);
                                
                                // The label name from the product attribute
                                $attribute_name = wc_attribute_label($taxonomy, $product);
                                
                                // The term name (or value) from this attribute
                                if(taxonomy_exists($taxonomy)) {
                                    $attribute_value = get_term_by('slug', $term_slug, $taxonomy)->name;
                                } else {
                                    $attribute_value = $term_slug; // For custom product attributes
                                }
                                $order_details['item'][$i]['option'][$attribute_name] = trim($attribute_value);
                                
                                // If option set to any in hub, need to do a further check
                                $taxonomy = strtolower($taxonomy);
                                if($order_details['item'][$i]['option'][$attribute_name] == '') {
                                    $order_details['item'][$i]['option'][$attribute_name] = wc_get_order_item_meta($item_id, $taxonomy, true);
                                }
                            }
                            
                            // Process currency details
                            $currency_rate = get_post_meta($item_product_variation_id, 'merchr_hub_converted_rate', true);
                            if($currency_rate !== false && $currency_rate !== '') {
                                $order_details['item'][$i]['currency'] = [
                                    'from' => get_post_meta($item_product_variation_id, 'merchr_hub_converted_from', true),
                                    'to' => get_post_meta($item_product_variation_id, 'merchr_hub_converted_to', true),
                                    'rate' => $currency_rate,
                                ];
                            }
                        } else {
                            // Process currency information for simple product
                            $currency_rate = get_post_meta($item_product_id, 'merchr_hub_converted_rate', true);
                            if($currency_rate !== false && $currency_rate !== '') {
                                $order_details['item'][$i]['currency'] = [
                                    'from' => get_post_meta($item_product_id, 'merchr_hub_converted_from', true),
                                    'to' => get_post_meta($item_product_id, 'merchr_hub_converted_to', true),
                                    'rate' => $currency_rate,
                                ];
                            }
                        }
                    }
                    if($item_type == 'shipping') {
                        $shipping_name = trim($item->get_name());
                        $order_details['shipping']['name']              = $shipping_name;
                        $order_details['shipping']['item']['name']      = $shipping_name;
                        $order_details['shipping']['item']['id']        = $item->get_id();
                        $order_details['shipping']['item']['quantity']  = $item->get_quantity();
                        $order_details['shipping']['item']['total']     = $item->get_total();
                        $order_details['shipping']['item']['total_tax'] = $item->get_total_tax();
                        
                        // Check for tax
                        if($order_details['shipping']['item']['total_tax'] > 0) {
                            $order_details['shipping']['item']['subtotal'] = $order_details['shipping']['item']['total'] - $order_details['shipping']['item']['total_tax'];
                        } else {
                            // 20% VAT on shipping
                            $order_details['shipping']['item']['total_tax'] = round((($order_details['shipping']['item']['total'] / 1.2) * 0.2), 2, PHP_ROUND_HALF_UP);
                            $order_details['shipping']['item']['subtotal'] = round(($order_details['shipping']['item']['total'] - $order_details['shipping']['item']['total_tax']), 2, PHP_ROUND_HALF_DOWN);
                        }
                    }
                    
                    $i++;
                }
                
                // Check if we have none Merchr products and recalculate order totals
                if(!empty($none_merchr_items)) {
                    $adjustment_total = 0.00;
                    $adjustment_tax = 0.00;
                    
                    // Loop none Merchr products and total up
                    $tax = new \WC_Tax();
                    foreach($none_merchr_items as $none) {
                        $adjustment_product_id = $none['id'];
                        $adjustment_product_total = $none['total'];
                        
                        // Get tax rate
                        $adjustment_product = wc_get_product($adjustment_product_id);
                        
                        //Get rates of the product
                        $adjustment_taxes = $tax->get_rates($adjustment_product->get_tax_class());
                        $adjustment_rates = array_shift($adjustment_taxes);
                       
                        // Take only the item rate. 
                        $adjustment_item_rate = array_shift($adjustment_rates);
                        
                        // Calculate the adjustments
                        $adjustment_percentage = sprintf("%02d", $adjustment_item_rate);
                        $adjustment_tax_calcualtaion_value = (float) "1.{$adjustment_percentage}";
                        $adjustment_product_total_with_tax = $adjustment_product_total * $adjustment_tax_calcualtaion_value;
                        $adjustment_product_total_tax = $adjustment_product_total_with_tax - $adjustment_product_total;
                        
                        // Update totals
                        $adjustment_total += $adjustment_product_total_with_tax;
                        $adjustment_tax += $adjustment_product_total_tax;
                    }
                    
                    // Update order total
                    if($adjustment_total > 0) {
                        $order_details['total'] = number_format($order_details['total'] - $adjustment_total, 2, '.', '');
                        $order_details['total_tax'] = number_format($order_details['total_tax'] - $adjustment_tax, 2, '.', '');
                    }
                }
                
                // Set carrier and service ID 
                // TODO Link with carrier services saved on hub and get ID's from there
                if(stripos($shipping_name, 'Standard') !== false) {
                    $order_details['shipping']['carrier_id'] = 1;
                    $order_details['shipping']['carrier_service_id'] = 1;
                } else {
                    $order_details['shipping']['carrier_id'] = 2;
                    $order_details['shipping']['carrier_service_id'] = 2;
                }
                
                // Process the order on the hub, has to be done through separate API endpoints
                
                // 1) Create Customer (Get Customer ID)
                if($merchr_customer_id === '') {
                    // Add customer to Hub
                    $merchr_customer_id = $this->addCustomer($id, $order_id, $order_details);
                    if($merchr_customer_id === false) {
                        continue; // Process next order
                    }
                }
                
                // 2) Create Customer Address (Get Customer Address ID)
                if($merchr_customer_address_id === '') {
                    // Add customer address to Hub
                    $merchr_customer_address_id = $this->addCustomerAddress($id, $order_id, $merchr_customer_id, $order_details);
                    if($merchr_customer_address_id === false) {
                        continue; // Process next order
                    }
                }
                
                // 3) Create Order (Get Order ID)
                if($merchr_order_id === '') {
                    // Add order to Hub
                    $merchr_order_id = $this->addOrder($id, $order_id, $merchr_customer_id, $merchr_customer_address_id, $order_details);
                    if($merchr_order_id === false) {
                        continue; // Process next order
                    }
                }
                
                // 4) Create Order Items
                $merchr_order_items = $this->addOrderItems($id, $order_id, $merchr_order_id, $order_details, $merchr_options);
                if($merchr_order_items === false) {
                    continue; // Process next order
                }
                
                /*
                // 5) Create Order Shipping Item
                $merchr_order_shipping_item = $this->addOrderShippingItem($id, $order_id, $merchr_order_id, $order_details);
                if($merchr_order_shipping_item === false) {
                    continue; // Process next order
                }
                */
                
                // Process complete, update orders table
                $this->updateOrderTable(1, $id, $order_id);
            }
        }
		
		return;
	}
	
	/**
	 * Process Order Status'.
	 */
	public function processOrderStatus()
	{
		// Fetch order details
		$marketplace_orders = $this->wpdb->get_results(
			"SELECT `id`, `order_id`, `merchr_order_id` 
			 FROM `{$this->merchr_tables->orders}` 
			 WHERE `processed`='1' AND `completed`='0' AND `cancelled`='0'"
		);
		
		// Have we orders to check?
		if($marketplace_orders !== null) {
			$orders_to_process = [];
			foreach($marketplace_orders as $order) {
				$id = (int) $order->id;
				$order_id = (int) $order->order_id;
				$merchr_order_id = (int) $order->merchr_order_id;
				$orders_to_process[$merchr_order_id] = [
					'id' => $id,
					'order_id' => $order_id
				];
			}
		
			// Fetch orders from the Merchr Hub
			$response = $this->request->makeRequestToGetOrders($this->options, $this->options['merchr_hub_store_id']);
			$merchr_orders = json_decode($response, true);
			
			// If data received, process
			if(isset($merchr_orders['data'])) {
				foreach($merchr_orders['data'] as $order) {
					$merchr_order_id = (int) $order['id'];
					$order_status = '';
					if(isset($order['order_status']['name'])) {
						$order_status = strtolower(trim($order['order_status']['name']));
					}
					
					// If order status empty, set as new (default)
					if($order_status == '') {
						$order_status = 'new';
					}
					
					// Process if in list
					if(isset($orders_to_process[$merchr_order_id])) {
						$id = $orders_to_process[$merchr_order_id]['id'];
						$order_id = $orders_to_process[$merchr_order_id]['order_id'];
						$this->saveOrderStatus($merchr_order_id, $order_id, $id, $order_status);
					}
				}
			}
		}
	}
	
	/**
	 * Process Resend Order'.
	 */
	public function processResendOrder()
	{	
		// Validate we have an order table record id
		if(!isset($_POST['id'])) {
			$this->result['msg'] = __('Something went wrong, refresh the page and try again!', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		$id = (int) $_POST['id'];
		
		// Update orders table to allow resending of this order
		$this->wpdb->update( 
			$this->merchr_tables->orders,
			[
				'processed' => 0,
				'failed' => 0,
				'retried' => 1,
			],
			['id' => $id], 
			['%d','%d','%d'], 
			['%d']
		);
		
		// Set status and page content
		$this->result['status'] = 'success';
		$this->result['payload'] = __('Queued', 'merchr');
		
		// Send content
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Add Customer.
	 *
	 * @param int
     * @param int
	 * @param array
	 *
	 * @return mixed int/bool
	 */
	protected function addCustomer(int $id, int $order_id, array $order_details)
	{
		// Create payload
		$payload = [
			'store_customer_id' => $order_details['customer_id'],
			'first_name' => $order_details['billing']['first_name'],
			'last_name' => $order_details['billing']['last_name'],
			'company' => $order_details['billing']['company'],
			'address_1' => $order_details['billing']['address_1'],
			'address_2' => $order_details['billing']['address_2'],
			'city' => $order_details['billing']['city'],
			'county' => $order_details['shipping']['state'],
			'country' => $order_details['billing']['country'],
			'postcode' => $order_details['billing']['postcode'],
			'customer_phone' => $order_details['phone'],
			'customer_email' => $order_details['email']
		];
		
		// Make request
		$response = $this->request->makeRequestToCreateCustomer($this->options, $order_details['store_id'], $payload);
		$response = json_decode($response, true);
		
		// Check for customer ID
		if(isset($response['data']['id'])) {
			$merchr_customer_id = (int) $response['data']['id'];
			
			// Update order table
			$this->wpdb->update( 
				$this->merchr_tables->orders,
				[
					'customer_id'        => $order_details['customer_id'],
					'merchr_customer_id' => $merchr_customer_id
				],
				['id' => $id], 
				['%d', '%d'], 
				['%d']
			);
			
			// Return Merchr customer ID
			return $merchr_customer_id;
		} else {
			// Update order table and return false
			$errors = $this->request->getRequestLastErrors();
			$this->updateOrderTable(0, $id, $order_id, "Error Adding Customer:\n{$errors}");
			return false;
		}
	}
	
	/**
	 * Add Customer Address.
	 *
	 * @param int
	 * @param int
     * @param int
	 * @param array
	 *
	 * @return mixed int/bool
	 */
	protected function addCustomerAddress(int $id, int $order_id, int $merchr_customer_id, array $order_details)
	{
		// Create Complete Address
		$full_address = "{$order_details['customer']['address_1']}\n";
		if($order_details['customer']['address_2'] !== '') {
			$full_address .= "{$order_details['customer']['address_2']}\n";
		}
		$full_address .= "{$order_details['customer']['city']}\n";
		if($order_details['customer']['state'] !== '') {
			$full_address .= "{$order_details['customer']['state']}\n";
		}
		$full_address .= "{$order_details['customer']['postcode']}\n";
		$full_address .= "{$order_details['customer']['country']}\n";
		
		// Create payload
		$payload = [
			'address' => $full_address,
            'first_name' => $order_details['customer']['first_name'],
			'last_name' => $order_details['customer']['last_name'],
			'address_1' => $order_details['customer']['address_1'],
			'address_2' => $order_details['customer']['address_2'],
			'city' => $order_details['customer']['city'],
			'county' => $order_details['customer']['state'],
			'postcode' => $order_details['customer']['postcode'],
			'country' => $order_details['customer']['country']
		];
		
		// Make request
		$response = $this->request->makeRequestToCreateCustomerAddress($this->options, $order_details['store_id'], $merchr_customer_id, $payload);
		$response = json_decode($response, true);
		
		// Check for customer address ID
		if(isset($response['data']['id'])) {
			$merchr_customer_address_id = (int) $response['data']['id'];
			
			// Update order table
			$this->wpdb->update( 
				$this->merchr_tables->orders,
				[
					'merchr_customer_address_id' => $merchr_customer_address_id
				],
				['id' => $id], 
				['%d'], 
				['%d']
			);
			
			// Return Merchr customer address ID
			return $merchr_customer_address_id;
		} else {
			// Update order table and return false
			$errors = $this->request->getRequestLastErrors();
			$this->updateOrderTable(0, $id, $order_id, "Error Adding Customer Address:\n{$errors}");
			return false;
		}
	}
	
	/**
	 * Add Order.
	 *
	 * @param int
	 * @param int
	 * @param int
     * @param int
	 * @param array
	 *
	 * @return mixed int/bool
	 */
	protected function addOrder(int $id, int $order_id, int $merchr_customer_id, int $merchr_customer_address_id, array $order_details)
	{
		// Calculate total before tax, not provided by WC
		$total_before_tax = $order_details['total'] - $order_details['total_tax'];
		
		// Create payload
		$payload = [
			'store_id' => $order_details['store_id'],
			'marketplace_id' => $order_details['order_id'],
			'shipping_carrier_id' => $order_details['shipping']['carrier_id'],
			'shipping_carrier_service_id' => $order_details['shipping']['carrier_service_id'],
			'customer_id' => $merchr_customer_id,
			'customer_shipping_address_id' => $merchr_customer_address_id,
			'currency' => $order_details['currency'],
			'currency_id' => $order_details['currency_id'],
			'includes_tax' => 'y',
			'discount' => number_format($order_details['discount_total'], 2, '.', ''),
			'shipping' => number_format($order_details['shipping_total'], 2, '.', ''),
			'total_before_tax' => number_format($total_before_tax, 2, '.', ''),
			'total' => number_format($order_details['total'], 2, '.', ''),
			'total_tax' => number_format($order_details['total_tax'], 2, '.', ''),
			'billing_meta_data' => $order_details['billing'],
			'shipping_meta_data' => $order_details['shipping'],
			'marketplace_order_meta_data' => $order_details,
		];
		
		// Make request
		$response = $this->request->makeRequestToCreateOrder($this->options, $order_details['store_id'], $payload);
		$response = json_decode($response, true);
		
		// Check for order ID
		if(isset($response['data']['id'])) {
			$order_id = (int) $response['data']['id'];
			
			// Update order table
			$this->wpdb->update( 
				$this->merchr_tables->orders,
				[
					'merchr_order_id' => $order_id
				],
				['id' => $id], 
				['%d'], 
				['%d']
			);
			
			// Return Merchr customer ID
			return $order_id;
		} else {
			// Update order table and return false
			$errors = $this->request->getRequestLastErrors();
			$this->updateOrderTable(0, $id, $order_id, "Error in creating order with ID {$order_details['order_id']}:\n{$errors}");
			return false;
		}
	}
	
	/**
	 * Add Order Items.
	 *
	 * @param int
	 * @param int
	 * @param int
	 * @param array
     * @param array
	 *
	 * @return bool
	 */
	protected function addOrderItems(int $id, int $order_id, int $merchr_order_id, array $order_details, array $merchr_options)
	{
		$errors_array = [];
		
		// First get order items already added
		$order_item_list = [];
		$order_items = $this->wpdb->get_results(
			"SELECT `id`, `order_item_id`, `merchr_order_item_id` 
			 FROM `{$this->merchr_tables->order_items}` 
			 WHERE `order_id`='{$order_id}'"
		);
		if($order_items !== null) {
			foreach($order_items as $item) {
				$order_item_list[$item->order_item_id] = $item->merchr_order_item_id;
			}
		}
		
		// Process the passed order items
		foreach($order_details['item'] as $item) {
			// Check this item has not already been added
			if(!isset($order_item_list[$item['id']])) {
				$item_id = (int) $item['id'];
				
				// Adjust for variation product
				if($item['is_variation'] == 1) {
					$sku = trim($item['variant_sku']);
					if($sku === '') {
						$sku = trim($item['product_sku']);
					}
				} else {
					$sku = trim($item['product_sku']);
				}
				
				// Build Metadata
				$customisation = [];
				$options = [];
                $currency = [];
				if(isset($item['customisation']) && !empty($item['customisation'])) {
					$customisation = $item['customisation'];
				}
				if(isset($item['option']) && !empty($item['option'])) {
					$options = $item['option'];
				}
                if(isset($item['currency']) && !empty($item['currency'])) {
					$currency = $item['currency'];
				}
				$metadata = [
					'customisation' => $customisation,
					'options' => $options,
                    'currency' => $currency
				];
				
				// Check design, artboard and media ID's
				if(trim($item['design']['id']) === '') {
					$item['design']['id'] = 0;
				}
				if(trim($item['design']['media_id']) === '') {
					$item['design']['media_id'] = 0;
				}
				if(trim($item['design']['artboard_id']) === '') {
					$item['design']['artboard_id'] = 0;
				}
				
				// Adjust for taxes
                if($order_details['total_tax'] > 0) {
                    // Prices exclude tax
                    $price = number_format(round(($item['total'] / $item['quantity'] * 1.2), 2), 2, '.', '');
                    $price_with_tax = number_format(($item['total'] * 1.2), 2);
                    $total_tax = number_format(round(($price_with_tax - $item['total']), 2, PHP_ROUND_HALF_UP), 2, '.', '');
                    $subtotal = number_format(round($item['subtotal'], 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
                    $total = $price_with_tax;
                } else {
                    // Prices include tax
                    $price = number_format(round(($item['total'] / $item['quantity']), 2), 2, '.', '');
                    $price_with_tax = number_format($item['total'], 2, '.', '');
                    $total_tax = number_format(round((($price_with_tax / 1.2) * 0.2), 2, PHP_ROUND_HALF_UP), 2, '.', '');
                    $subtotal = number_format(round(($item['subtotal'] - $total_tax), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
                    $total = $price_with_tax;
                }
				
				// Build payload for create item request
				$payload = [
					'order_id'           => $order_id,
					'order_item_type_id' => 1, // 1 for product, 2 for shipping
					'sku'                => $sku,
					'product_id'         => $item['merchr_hub_product_id'],
					'store_product_id'   => $item['merchr_hub_store_product_id'],
					'marketplace_id'     => $item_id,
					'name'               => $item['name'],
					'quantity'           => $item['quantity'],
					'price'              => $price,
					'tax_class_id'       => 1,
					'subtotal'           => $subtotal,
					'total'              => $total,
					'total_tax'          => $total_tax,
					'meta_data'          => $metadata,
					'design_id'          => $item['design']['id'],
					'artboard_id'        => $item['design']['media_id'],
					'media_id'           => $item['design']['artboard_id'],
				];
                
				// Add order item to Hub
				// Make request
				$response = $this->request->makeRequestToCreateOrderItem($this->options, $order_details['store_id'], $merchr_order_id, $payload);
				$response = json_decode($response, true);
				
				// Check for order item ID
				if(isset($response['data']['id'])) {
					$order_Item_id = (int) $response['data']['id'];
					
					// Insert item to order item table
					$this->wpdb->insert( 
						$this->merchr_tables->order_items,
						[
							'order_id'             => $order_id,
							'merchr_order_id'      => $merchr_order_id,
							'order_item_id'        => $item_id,
							'merchr_order_item_id' => $order_Item_id,
							'date_created'         => current_time('mysql')
						],
						['%d','%d','%d','%d','%s']
					);
				} else {
					// Add error to array
					$errors = $this->request->getRequestLastErrors();
					$errors_array[$item['id']] = "Error in creating order item with ID {$item_id} and name {$item['name']} for order {$order_id}:\n{$errors}";
				}
			}
		}
		
		// Check for errors
		if(!empty($errors_array)) {
			$errors = implode("\n\n", $errors_array);
			$this->updateOrderTable(0, $id, $order_id, $errors);
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Add Order Shipping Item.
	 *
	 * @param int
	 * @param int
	 * @param int
	 * @param array
	 *
	 * @return bool
	 */
	protected function addOrderShippingItem(int $id, int $order_id, int $merchr_order_id, array $order_details)
	{
		// First get order items already added
		$order_item_list = [];
		$order_items = $this->wpdb->get_results(
			"SELECT `id`, `order_item_id`, `merchr_order_item_id` 
			 FROM `{$this->merchr_tables->order_items}` 
			 WHERE `order_id`='{$order_id}'"
		);
		if($order_items !== null) {
			foreach($order_items as $item) {
				$order_item_list[$item->order_item_id] = $item->merchr_order_item_id;
			}
		}
		
		// Check we have shipping item
		if(!isset($order_details['shipping']['item'])) {
			return true;
		}
		
		// Set Item ID
		$item_id = (int) $order_details['shipping']['item']['id'];
		
		// Check if not already added
		if(isset($order_item_list[$item_id])) {
			return true;
		}
		
		// Set meta data, set carrier ID as 1, default and only service for now
		$metadata = ['shipping_carrier_service_id' => 1];
		
		// Build payload for create item request
		$payload = [
			'order_id'           => $order_id,
			'order_item_type_id' => 2, // 1 for product, 2 for shipping
			'sku'                => '0',
			'marketplace_id'     => $item_id,
			'name'               => $order_details['shipping']['item']['name'],
			'quantity'           => $order_details['shipping']['item']['quantity'],
			'price'              => $order_details['shipping']['item']['total'],
			'tax_class_id'       => 1,
			'subtotal'           => $order_details['shipping']['item']['subtotal'],
			'total'              => $order_details['shipping']['item']['total'],
			'total_tax'          => $order_details['shipping']['item']['total_tax'],
			'meta_data'          => $metadata,
		];
		
		// Add order item to Hub
		// Make request
		$response = $this->request->makeRequestToCreateOrderItem($this->options, $order_details['store_id'], $merchr_order_id, $payload);
		$response = json_decode($response, true);
		
		// Check for order item ID
		if(isset($response['data']['id'])) {
			$order_Item_id = (int) $response['data']['id'];
			
			// Insert item to order item table
			$this->wpdb->insert( 
				$this->merchr_tables->order_items,
				[
					'order_id'             => $order_id,
					'merchr_order_id'      => $merchr_order_id,
					'order_item_id'        => $item_id,
					'merchr_order_item_id' => $order_Item_id,
					'date_created'         => current_time('mysql')
				],
				['%d','%d','%d','%d','%s']
			);
		} else {
			// Save error and return false
			$errors = $this->request->getRequestLastErrors();
			$msg = "Error in creating order shipping item with ID {$item_id} and name {$order_details['shipping']['item']['name']} for order {$order_id}:\n{$errors}";
			$this->updateOrderTable(0, $id, $order_id, $msg);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Update Order Table.
	 *
	 * @param int
	 * @param int
     * @param int
	 * @param string optional
	 */
	protected function updateOrderTable(int $status, int $id, int $order_id, string $notes = '')
	{
		$notes = sanitize_textarea_field($notes);
		$failed = 1 - $status;
		$this->wpdb->update( 
			$this->merchr_tables->orders,
			[
				'processed' => 1,
				'retried'   => 0,
				'success'   => $status,
				'failed'    => $failed,
				'notes'     => $notes
			],
			['id' => $id], 
			['%d','%d','%d','%d','%s'], 
			['%d']
		);
        
        // Add note to order
        if($notes === '') {
            $notes = __("Order has been successfully pushed to the Merchr Hub.");
        }
        $order = wc_get_order($order_id);
        $order->add_order_note($notes);
	}
	
	/**
	 * Save Order Status.
	 *
	 * @param int
	 * @param int
	 * @param int
	 * @param string
	 */
	protected function saveOrderStatus(int $merchr_order_id, int $order_id, int $id, string $status)
	{
		// First check if status is new, just return, nothing to do
		if($status == 'new') {
			return;
		}
		
		// Get the WC order
		$order = wc_get_order($order_id);
		$woo_status = strtolower($order->get_status());
		
		// Check status
		if($status == 'cancelled' || $status == 'completed') {
			// If status is full, mark order as completed
			if($status == 'completed') {
				$this->wpdb->update( 
					$this->merchr_tables->orders,
					[
						'completed' => 1,
						'status'    => __('completed', 'merchr')
					],
					['id' => $id], 
					['%s'], 
					['%d']
				);
				
				// Check if we need to update woo order status
				if($woo_status != 'completed') {
					$order->update_status('completed');
                    $note = __("Order status changed to completed via Merchr order status request.");
                    $order->add_order_note($note);
				}
			} else { // Mark as cancelled
				$this->wpdb->update( 
					$this->merchr_tables->orders,
					[
						'cancelled' => 1,
						'status'    => __('cancelled', 'merchr')
					],
					['id' => $id], 
					['%s'], 
					['%d']
				);
				
				// Check if we need to update woo order status
				if($woo_status != 'cancelled') {
					$order->update_status('cancelled');
                    $note = __("Order status changed to cancelled via Merchr order status request.");
                    $order->add_order_note($note);
				}
			}
		} else {
			// Mark as processing in Merchr order table
			$this->wpdb->update( 
				$this->merchr_tables->orders,
				[
					'status' => __('processing', 'merchr')
				],
				['id' => $id], 
				['%s'], 
				['%d']
			);
			
			// Check if we need to update woo order status
			if($woo_status != 'processing') {
				$order->update_status('processing');
                $note = __("Order status changed to processing via Merchr order status request.");
                $order->add_order_note($note);
			}
		}
	}
}
