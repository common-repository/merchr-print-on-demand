<?php
/**
 * Merchr Hub Admin Product Importer.
 *
 * @since      1.0.2
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use MerchrHub\includes\MerchrHubActions;
use MerchrHub\includes\data\MerchrHubGetCurrencyData;
use MerchrHub\includes\data\MerchrHubGetProductsAndAssociatedData;
use MerchrHub\includes\data\MerchrHubGetTaxData;
use MerchrHub\includes\helpers\MerchrHubHelpersCurrencies;
use MerchrHub\includes\helpers\MerchrHubHelpersCustomisation;
use MerchrHub\includes\helpers\MerchrHubHelpersProducts;
use MerchrHub\includes\helpers\MerchrHubHelpersTax;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminProductImporter extends MerchrHubActions
{
	use MerchrHubDatabaseInteraction;
	
	protected $request; // @var MerchrHubGetProductsAndAssociatedData
	protected $tax_request; // @var MerchrHubGetTaxData
    protected $currencies_request; // @var MerchrHubGetCurrencyData
    protected $import_limit; // @var int
    protected $store_base_currency; // @var string
    protected $store_base_country; // @var string
    protected $store_products; // @var array
    
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() 
	{
		parent::__construct();
        
        // Increase timeout limit
        // Some products could have dozens of variants
        // and WordPress creates multiple image sizes
        // for each variants image. Most hosts set a 30
        // or 60 second limit and can timeout before
        // processing all the variant images.
        @set_time_limit(3600);
        
        // Increase memory
        // Helps when processing so many images using
        // the imagick library (default for WP)
        @ini_set('memory_limit','512M');
        
        // We need to limit to a max of 10,
        // any more than that then we have
        // and we risk a timeout on external
        // WordPress stores as we have no 
        // control over their hosting, it is
        // common for cli to timeout at just 
        // 30 seconds on many shared hosts.
        $this->import_limit = 10;
        
        // Set store base currency
        $this->store_base_currency = MerchrHubHelpersCurrencies::getStoreCurrency();
        
        // Set store base country
        $this->store_base_country = MerchrHubHelpersProducts::cleanStoreBaseCountryString(get_option('woocommerce_default_country'));
		
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
		
		// Set product request and setup connection
		$this->request = new MerchrHubGetProductsAndAssociatedData();
		$this->request->setupConnection();
		
		// Set tax request and setup connection
		$this->tax_request = new MerchrHubGetTaxData();
		$this->tax_request->setupConnection();
        
        // Set currencies request and setup connection
		$this->currencies_request = new MerchrHubGetCurrencyData();
		$this->currencies_request->setupConnection();
	}
	
	/**
	 * Import Products.
	 */
	public function importProducts()
	{
		// Check we have products
		if(!isset($_POST['products']) || trim($_POST['products']) === '') {
			$this->result['status'] = 'error';
			$this->result['msg'] = __('No products have been selected!', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Clean and prepare the chosen products
		//$products = stripslashes(html_entity_decode($_POST['products']));
        $products = stripslashes($_POST['products']);
		$products = preg_replace('/[[:cntrl:]]/', ' ', $products);
		$products_cleaned = $this->prepareProductImportForSchedule($products);
		if($products_cleaned === false) {
			$this->result['status'] = 'error';
			$this->result['msg'] = __('Sorry something went wrong, please try again.', 'merchr');
			$this->sendAJAXContent($this->result);
		}
		
		// Create success content
		$message = esc_html__('The products have been successfully queued for import!', 'merchr');
		$message .= '<br><br>';
		$message .= esc_html__('Your products will now be imported in the background, you are free to use the site while this is happening.', 'merchr');
		$replacements = [
			'products_link'       => admin_url('edit.php?post_type=product'),
			'trigger_url'         => admin_url('index.php?merchr_process_imported_products=true'),
			'shop_link'           => get_site_url(),
			'image_src'           => $this->admin_images_url . 'success-green-check-mark.png',
			'image_title'         => esc_html__('Product Import Successfully Queued!', 'merchr'),
			'congratulations'     => esc_html__('Congratulations!', 'merchr'),
			'message'             => $message,
			'view_store_btn'      => esc_html__('View your store...', 'merchr'),
			'manage_products_btn' => esc_html__('Manage Your Products...', 'merchr'),
		];
		$template = MerchrHubHelpersTemplates::fetchTemplateContents('import_success.tpl', $this->templates_path);
		$content = MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
		
		// Send content
		$this->result['status'] = 'success';
		$this->result['payload'] = $content;
		$this->sendAJAXContent($this->result);
	}
	
	/**
	 * Process products chosen for import.
	 */
	public function processProducts()
	{
		// Fetch import details
		$tasks = $this->wpdb->get_results(
			"SELECT `id`, `iniator_email`, `initiator_name`, `products_json`, `created_at` FROM `{$this->merchr_tables->queue}` WHERE `processed`='0' AND `processing`='0' AND `failed`='0' LIMIT 1"
		);
		
		// Loop each import task
		foreach($tasks as $task) {
			$id = (int) $task->id;
			$iniator_email = trim($task->iniator_email);
			$initiator_name = trim($task->initiator_name);
			$products_chosen = json_decode($task->products_json, true);
			$created_at = $task->created_at;
			
			// Set queue is processing
			$this->updateImportTableProcessstarted($id);
			
			// Pre-process
			list($products, $ids) = $this->preProcessChosenProducts($products_chosen);
			
			// Get full products data
			$full_product_data = $this->getFullProductData($ids);
			
			// Save the products
			try {
				if($this->saveProducts($full_product_data, $products)) {
					$this->sendImportEmail(1, $created_at, $iniator_email, $initiator_name);
					$this->updateImportTables(1, $id, $products_chosen);
				} else {
					$this->sendImportEmail(0, $created_at, $iniator_email, $initiator_name);
					$this->updateImportTables(0, $id, $products_chosen);
				}
			} catch(\Exception $e) {
				$this->sendImportEmail(0, $created_at, $iniator_email, $initiator_name);
				$this->updateImportTables(0, $id, $products_chosen);
			}
		}
	}
	
	
	/**
	 * Process new products.
	 */
	public function processNewProducts()
	{
		$new_products = [];
		
		// Fetch store products
		$merchr_options = get_option('merchr_hub_options');
		$store_id = (int) $merchr_options['merchr_hub_store_id'];
		$this->store_products = json_decode($this->request->makeRequestForStoreProductsNew($merchr_options, $store_id), true);
		
		// Fetch and Sort imported products
		$sorted_imported_products = [];
		$imported_products = $this->wpdb->get_results("SELECT `merchr_store_product_id` FROM {$this->merchr_tables->products} WHERE `product_type`='store'", ARRAY_A);
		foreach($imported_products as $imported_product) {
			$merchr_store_product_id = (int) $imported_product['merchr_store_product_id'];
			$sorted_imported_products[$merchr_store_product_id] = $merchr_store_product_id;
		}
		
		// Get last import number
		$import_id = $this->wpdb->get_var("SELECT MAX(`import_id`) AS max FROM {$this->merchr_tables->products}");
		$new_import_id = $import_id + 1;
		
		// Save Store products
		if(isset($this->store_products['data'])) {
            $count = 0;
            
            foreach($this->store_products['data'] as $product) {
                $merchr_product_id       = (int) $product['product_id'];
				$merchr_store_product_id = (int) $product['id'];
				$merchr_product_sku      = trim($product['product']['sku']);
				$product_title           = trim($product['product_name']);
				$product_description     = trim($product['description']);
				$product_price           = (float) $product['selling_price'];
				$product_cost            = (float) $product['product']['cost'];
				$product_fee             = (float) $product['product']['processing_fee'];
				$product_img             = $product['product_image']['url']['medium_abs_url'];
				$product_customisable    = (int) $product['product']['customisable'];
				$json_content            = json_encode($product);
                
                // Check has not already been imported
				if(isset($sorted_imported_products[$merchr_store_product_id])) {
					continue;
				}
                
                if($count === $this->import_limit) {
                    break;
                }
				
				// Validate, minimum requirements
				if($merchr_store_product_id == '' || 
				   $merchr_product_sku == '' || 
				   $product_title == '' || 
				   $product_price == '' || $product_price <= 0 || 
				   $product_cost == '' || $product_cost <= 0
				) {
					continue;
				}
				
				// If the description is empty, use title instead.
				// WC requires a description and import fails without it.
				if($product_description == '') {
					$product_description = $product_title;
				}
				
				// Check if we have a store product image with a design
				if(isset($product['store_product_images']) && !empty($product['store_product_images'])) {
					foreach($product['store_product_images'] as $store_image) {
						if($store_image['is_main_image'] == true) {
							$product_img = $store_image['save_path_image_url'];
						}
					}
				}
				
				// Sort categories, collections and industries
				$product_categories = json_encode($product['product']['categories']);
				$product_collections = json_encode($product['product']['collections']);
				$product_industries = json_encode($product['product']['industries']);
				
				$this->wpdb->insert(
					$this->merchr_tables->products, 
					[
						'import_id'                  => $new_import_id,
						'merchr_product_id'          => $merchr_product_id,
						'merchr_store_product_id'    => $merchr_store_product_id,
						'merchr_product_sku'  	     => $merchr_product_sku,
						'product_type'               => 'store',
						'product_title'              => $product_title,
						'product_description'        => $product_description,
						'product_price'              => $product_price,
						'product_cost'               => $product_cost,
						'product_fee'                => $product_fee,
						'product_img'                => $product_img,
						'product_customisable'       => $product_customisable,
						'product_categories'         => $product_categories,
						'product_collections'        => $product_collections,
						'product_industries'         => $product_industries,
						'json_content'               => $json_content,
						'created_at'                 => current_time('mysql'),
					],
					['%d','%d','%d','%s','%s','%s','%s','%f','%f','%f','%s','%d','%s','%s','%s','%s','%s']
				);
				$insert_id = $this->wpdb->insert_id;
				
				$new_products[] = [
					"id" => $insert_id,
					"name" => $product_title,
					"description" => $product_description,
					"price" => $product_price
				];
                
                // Increment limit count
                $count++;
			}
			
			if(!empty($new_products)) {
				$new_products = json_encode($new_products);
				$this->prepareProductImportForSchedule($new_products);
			}
		}
		
		return;
	}
    
    /**
	 * Process admin products.
	 */
	public function processAdminProducts()
	{
        $count = 0;
        $new_products = [];
        
        $products = $this->wpdb->get_results(
			"SELECT `id`, `product_title`, `product_description`, `product_price` 
			FROM `{$this->merchr_tables->products}` WHERE `product_type`='store' 
            AND `imported`='0' AND `import_queued`='0';"
		);
        
        if(count($products) > 0 ) {
            foreach($products as $product) {
                if($count === $this->import_limit) {
                    break;
                }
                
                $new_products[] = [
					"id" => $product->id,
					"name" => $product->product_title,
					"description" => $product->product_description,
					"price" => $product->product_price
				];
                
                // Increment limit count
                $count++;
            }
        }
        
        if(!empty($new_products)) {
            $new_products = json_encode($new_products);
		    $this->prepareProductImportForSchedule($new_products);
	    }
        
        return;
    }
	
	/**
	 * Prepare Product Import For Schedule.
	 *
	 * @param string JSON
	 * 
	 * @return mixed
	 */
	protected function prepareProductImportForSchedule(string $products)
	{
		// Get current logged in users details
		$current_user = wp_get_current_user();
		$current_user_email = $current_user->user_email;
		$current_user_name = "{$current_user->user_firstname} {$current_user->user_lastname}";
		
		// Convert products to array and do a count
		$products_array = json_decode($products, true);
		$product_count = count($products_array);
		
		// Set ID's to update products in the import queue
		$queued_ids = [];
        $cleaned_products = [];
		foreach($products_array as $product) {
			// Add to queued ID's array
            $queued_ids[] = (int) $product['id'];
            
            // Escape values
            $cleaned_products[] = [
                'id' => (int) $product['id'],
                'name' => sanitize_text_field($product['name']),
                'description' => sanitize_textarea_field($product['description']),
                'price' => sanitize_text_field($product['price']),
            ];
		}
        
        // Update product import table
		$processed_queued_ids = implode(',', $queued_ids);
		$this->wpdb->query("UPDATE {$this->merchr_tables->products} SET `import_queued`='1' WHERE id IN({$processed_queued_ids});");
		
		// Save information of import request
		return $this->wpdb->insert(
			$this->merchr_tables->queue, 
			[
				'iniator_email'    => $current_user_email,
				'initiator_name'   => $current_user_name,
				'products_json'    => json_encode($cleaned_products),
				'product_quantity' => $product_count,
				'created_at'       => current_time('mysql'),
			],
			[
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);
	}
	
	/**
	 * Pre-process chosen products.
	 * Sort the products so ID is the key
	 *
	 * @param array
	 * 
	 * @return mixed
	 */
	protected function preProcessChosenProducts(array $products)
	{
		$sorted = [];
		$ids = [];
		foreach($products as $values) {
			$id = (int) $values['id'];
			$name = html_entity_decode($values['name']);
			$description = html_entity_decode($values['description']);
			$price = number_format($values['price'], 2);
			
			// Add to arrays
			$sorted[$id] = [
				'name' => $name,
				'description' => $description,
				'price' => $price,
			];
			$ids[] = $id;
		}
		
		return [$sorted, $ids];
	}
	
	/**
	 * Get Full Product Data.
	 *
	 * @param array
	 * 
	 * @return array
	 */
	protected function getFullProductData(array $ids)
	{
		if(empty($ids)) {
			return [];
		}
		$ids = implode(",", $ids);
		
		return $this->wpdb->get_results(
			"SELECT `id`, `merchr_product_id`, `merchr_store_product_id`, `merchr_product_sku`, `product_type`, `product_title`, `product_description`, `product_price`, 
			`product_cost`, `product_customisable`, `product_categories`, `product_collections`, `product_industries`, `json_content` 
			FROM `{$this->merchr_tables->products}` WHERE `id` IN ({$ids})"
		);
	}
	
	/**
	 * Get Original Merchr Product JSON.
	 *
	 * @param int
	 * 
	 * @return string
	 */
	protected function getMerchrProductJSON(int $id)
	{	
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare( "SELECT `json_content` FROM `{$this->merchr_tables->products}` WHERE `merchr_product_id` = %d", $id)
		);
		if($result->json_content !== null) {
			return $result->json_content;
		} else {
			return '';
		}
	}
	
	/**
	 * Save Products.
	 *
	 * @param object 
	 * @param array
	 * 
	 * @return mixed
	 */
	protected function saveProducts($full_product_data, array $products)
	{
		$results = [];
		
		// Fetch tax classes and format
		$merchr_options = get_option('merchr_hub_options');
		$tax_classes = json_decode($this->tax_request->makeRequestForTaxClasses($merchr_options), true);
		$tax_classes = $tax_classes['data'];
        $currencies = json_decode($this->currencies_request->makeRequestForCurrencies($merchr_options), true);
	    $currencies = $currencies['data'];
		
		// Loop through each product
		if($full_product_data !== null) {
			// Need to require these files
			if(!function_exists('media_handle_sideload')) {
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				require_once(ABSPATH . 'wp-admin/includes/media.php');
			}
			
			// Loop products and save
			foreach($full_product_data as $values) {
				$id                         = $values->id;
				$merchr_product_id          = $values->merchr_product_id;
				$merchr_store_product_id    = $values->merchr_store_product_id;
				$merchr_product_sku         = $values->merchr_product_sku;
				$product_type               = $values->product_type;
				$product_title              = $values->product_title;
				$product_description        = $values->product_description;
				$product_price              = floatval($values->product_price); 
				$product_cost               = floatval($values->product_cost); 
				$product_customisable       = $values->product_customisable; 
				$product_categories         = $values->product_categories;
				$product_collections        = $values->product_collections; 
				$product_industries         = $values->product_industries;
				$json_content               = $values->json_content;
				$design                     = [];
				
                /*
				// Use updated data from import edit form
				if(isset($products[$id])) {
					$product_title = $products[$id]['name'];
					$product_description = $products[$id]['description'];
					$product_price = floatval($products[$id]['price']);
				}
                */
				
				// Extract JSON data
				$extracted_content = json_decode($json_content, true);
				
				// Tax Handling and price correction if applicable
                $tax_calcualtaion_value = 1.0;
                $tax_class_id = (int) $extracted_content['product']['tax_class_id'];
                $remove_tax = MerchrHubHelpersTax::isTaxRemovalRequired($merchr_options);
                if($remove_tax) {
                    $tax_calcualtaion_value = MerchrHubHelpersTax::returnTaxCalculationValue($tax_class_id, $tax_classes, $tax_calcualtaion_value);
                }
                
				// If store product we need to grab the original product ID
				// also check is a variable product and set categories
				$is_variable_product = false;
				if($product_type === 'store') {
					$merchr_product_id = $extracted_content['product']['id'];
					if(!empty($extracted_content['product']['variants'])) {
						$is_variable_product = true;
					}
					$categories = $extracted_content['product']['categories'];
				} else {
					$merchr_store_product_id = 0;
					if(!empty($extracted_content['variants'])) {
						$is_variable_product = true;
					}
					$categories = $extracted_content['categories'];
				}
				
				// Set main image
				$image_preview = []; // THis is design coordinate data
				if($product_type === 'store') {
					// The main image is always set
                    $main_image = $extracted_content['product_image']['url']['large_abs_url'];
					$main_image_name = $extracted_content['product_image']['image'];
					if(isset($extracted_content['product_image']['url']['image_preview'])) {
						$image_preview = $extracted_content['product_image']['url']['image_preview'];
					}
					
					// First check for store product variant main image
					$main_found = false;
                    if(isset($extracted_content['store_product_variants']) && !empty($extracted_content['store_product_variants'])) {
						foreach($extracted_content['store_product_variants'] as $store_image) {
							if($store_image['is_main'] == true) {
								$main_image = $store_image['store_product_image_url'];
								$main_image_parts = explode('/', $main_image);
								$main_image_name = end($main_image_parts);
                                $main_found = true;
							}
						}
					}
                    
                    // If no main variant image found, check store product images
                    if(!$main_found) {
                        if(isset($extracted_content['store_product_images']) && !empty($extracted_content['store_product_images'])) {
                            foreach($extracted_content['store_product_images'] as $store_image) {
                                if($store_image['is_main_image'] == true) {
                                    $main_image = $store_image['save_path_image_url'];
                                    $main_image_parts = explode('/', $main_image);
                                    $main_image_name = end($main_image_parts);
                                }
                            }
                        }
                    }
				} else {
					foreach($extracted_content['images'] as $images) {
						if($images['url']['sort'] == 0) {
							$main_image = $images['url']['large_abs_url'];
							$main_image_name = $images['image'];
							if(isset($images['url']['image_preview'])) {
								$image_preview = $images['url']['image_preview'];
							}
						} else {
							continue;
						}
					}
				}
				
				// Upload image
				$image = [];
				$image['name'] = $main_image_name;
				$image['tmp_name'] = \download_url($main_image);
				$image_attachment_id = \media_handle_sideload($image);
				
				// Create slug from title
				$slug = str_replace([' ', '  ', '_'], '-', strtolower($product_title));
				
				// Save categories
				$category_names = [];
				foreach($categories as $category) {
					wp_insert_term($category['name'], 'product_cat');
					$category_names[] = $category['name'];
				}
				
				// Get all product categories
				$all_product_categories = get_terms('product_cat', ['orderby' => 'name', 'order' => 'asc', 'hide_empty' => false]);
				
				// Init variable or simple product
				if($is_variable_product) { 
					$product = new \WC_Product_Variable();
				} else { // Simple Product
					$product = new \WC_Product_Simple();
				}
                
                // Format Price (Tax removal if applicable)
                $product_price = MerchrHubHelpersTax::returnFormattedPrice($product_price, $tax_calcualtaion_value);
                
                // Check for currency mismatch and adjust
                $product_currency = 'GBP';
                if(isset($extracted_content['product']['currencies'][0]['code'])) {
                    $product_currency = $extracted_content['product']['currencies'][0]['code'];
                    list($product_price, $product_price_converted, $product_price_rate) = MerchrHubHelpersCurrencies::checkAndConvertCurrency(
                        $currencies, 
                        $this->store_base_currency, 
                        $product_currency, 
                        $product_price
                    );
                }
                
				// Set product details
				// Append import ID to SKU, this is so a store owner can have 
				// the same Merchr product but with different designs, Woo 
				// does not allow products with duplicate SKU's
				$product->set_name($product_title);
				$product->set_slug($slug);
                $product->set_price($product_price);
				$product->set_regular_price($product_price);
				$product->set_sku($merchr_product_sku . "-{$id}"); 
				$product->set_stock_status('instock'); 
				$product->set_image_id($image_attachment_id);
				
				// Set description(s) depending on setting
				if($merchr_options['merchr_hub_description_location'] == 'short') {
					$product->set_short_description(nl2br($product_description));
				} else if($merchr_options['merchr_hub_description_location'] == 'full') {
					$product->set_description(nl2br($product_description));
				} else { // Both
					$product->set_description(nl2br($product_description));
					$product->set_short_description(nl2br($product_description));
				}
				
				// Save product
				$product->save();
				
				// Set product marketplace id
				$marketplace_id = $product->get_id();
                
                // Set currency postmeta
                if(isset($product_price_converted) && $product_price_converted === true) {
                    add_post_meta($marketplace_id, 'merchr_hub_converted_from', $product_currency);
                    add_post_meta($marketplace_id, 'merchr_hub_converted_to', $this->store_base_currency);
                    add_post_meta($marketplace_id, 'merchr_hub_converted_rate', $product_price_rate);
                }
                
                // Check embossed/engraved
                $embossed_engraved = 0;
                if(isset($extracted_content['product']['is_engraved_embossed'])) {
                    $embossed_engraved = (int) $extracted_content['product']['is_engraved_embossed'];
                }
				
				// Set design data if present, first set defaults
                $product->update_meta_data('merchrcust_customisable', $product_customisable);
				$product->update_meta_data('merchrcust_font_family', 'Patrick Hand');
				$product->update_meta_data('merchrcust_text_color', '#0ca1cc');
				$product->update_meta_data('merchrcust_placement_editor_type', 'none');
				$product->update_meta_data('merchrcust_offset_x', 0);
				$product->update_meta_data('merchrcust_offset_y', 0);
				$product->update_meta_data('merchrcust_width', 0);
				$product->update_meta_data('merchrcust_height', 0);
				$product->update_meta_data('merchrcust_rotate', 0);
                $product->update_meta_data('merchrcust_emboss_engraved', $embossed_engraved);
				$product->save_meta_data();
				
				if(!empty($extracted_content['design'])) {
					$design = $extracted_content['design'];
					
					// Add meta data
					add_post_meta($marketplace_id, 'merchr_hub_design_id', $design['id']);
					
					// Check if customisable
					if($product_customisable == 1) {
						// Check for an editable area
						$editable_text_areas = trim($design['editable_text_areas']);
						if($editable_text_areas !== '' && $editable_text_areas !== null) {
							$editable_text_areas = json_decode($editable_text_areas, true);
							if(is_array($editable_text_areas) && !empty($editable_text_areas)) {
								MerchrHubHelpersCustomisation::processCustomisationData(
									$product, 
									$editable_text_areas, 
									$image_preview,
									$design
								);
							}
						}
					}
				}
				
				// Set original product ID, store product ID and SKU Meta
				add_post_meta($marketplace_id, 'merchr_hub_product_id', $merchr_product_id);
				add_post_meta($marketplace_id, 'merchr_hub_store_product_id', $merchr_store_product_id);
				add_post_meta($marketplace_id, 'merchr_hub_original_sku', $merchr_product_sku);
				
				// Set categories
				foreach($all_product_categories as $saved_category) {
					if(in_array($saved_category->name, $category_names)) {
						wp_set_post_terms($marketplace_id, [$saved_category->term_id], 'product_cat', true);
					}
				}
                
                // Check for country restrictions
                if(is_array($extracted_content['product'])) {
                    MerchrHubHelpersProducts::checkAndSetCountryRestrictions($product, $extracted_content['product']);
                }
                
                // Set product slug
                $product_slug = get_permalink($marketplace_id);
				
				// Update the hub
                $hub_result = $this->createUpdateStoreProductOnHub(
					$product_type, 
					$merchr_product_id,
					$merchr_store_product_id,
					$marketplace_id,
					$product_title,
					$product_description,
					$product_price,
                    $product_slug
				);
				
				// Update import product record
				if($product_type === 'store') {
					// Create variants if required
					if($is_variable_product) { 
						$this->processProductVariants(
                            $product, 
                            $id, 
                            $merchr_product_id, 
                            $merchr_store_product_id, 
                            $product_price, 
                            $tax_calcualtaion_value, 
                            $currencies, 
                            $product_currency, 
                            $hub_result, 
                            $merchr_options
                        );
					}
					
					// Update product import table
					$this->wpdb->update( 
						$this->merchr_tables->products,
						[
							'market_place_id' => $marketplace_id,
							'imported' => 1,
							'import_queued' => 0,
							'imported_at' => current_time('mysql')
						],
						['id' => $id], 
						['%d','%d','%d','%s'], 
						['%d']
					);
				} else {
					// Insert newly created store product and mark as imported
					$merchr_store_product_id = $hub_result['data']['id'];
					$product_cost = $hub_result['data']['product']['cost'];
					$product_fee = $hub_result['data']['product']['processing_fee'];
					$product_img = $hub_result['data']['product']['images'][0]['url']['large_abs_url'];
					$product_customisable = $hub_result['data']['product']['customisable'];
					$this->wpdb->insert(
						$this->merchr_tables->products, 
						[
							'merchr_product_id'          => $merchr_product_id,
							'merchr_store_product_id'    => $merchr_store_product_id,
							'merchr_product_sku'  	     => $merchr_product_sku,
							'market_place_id'  	         => $marketplace_id,
							'product_type'               => 'store',
							'product_title'              => $product_title,
							'product_description'        => $product_description,
							'product_price'              => $product_price,
							'product_cost'               => $product_cost,
							'product_fee'                => $product_fee,
							'product_img'                => $product_img,
							'product_customisable'       => $product_customisable,
							'product_categories'         => $product_categories,
							'product_collections'        => $product_collections,
							'product_industries'         => $product_industries,
							'json_content'               => json_encode($hub_result),
							'created_at'                 => current_time('mysql'),
							'imported_at'                => current_time('mysql'),
							'imported'                   => 1,
						],
						['%d','%d','%s','%d','%s','%s','%s','%f','%f','%f','%s','%d','%s','%s','%s','%s','%s','%s','%s','%d']
					);
					
					// Grab insert ID and update SKU
					$insert_id = $this->wpdb->insert_id;
					if($insert_id !== false) {
						$id = $insert_id;
						$product->set_sku($merchr_product_sku . "-{$id}"); 
						$product->save();
					}
					
					// Create variants if required (with new ID if applicable)
					if($is_variable_product) { 
						$this->processProductVariants(
                            $product, 
                            $id, 
                            $merchr_product_id, 
                            $merchr_store_product_id, 
                            $product_price, 
                            $tax_calcualtaion_value, 
                            $currencies, 
                            $product_currency, 
                            $hub_result, 
                            $merchr_options
                        );
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Create update store product on hub
	 *
	 * @param string
	 * @param int
	 * @param int
	 * @param int
	 * @param string
	 * @param string
	 * @param float
     * @param string
	 * 
	 * @return mixed
	 */
	protected function createUpdateStoreProductOnHub(
        string $product_type,  
        int $merchr_product_id, 
        int $merchr_store_product_id, 
        int $marketplace_id, 
        string $product_title, 
        string $product_description, 
        float $product_price,
        string $product_slug
    )
	{	
		$current_time = current_time('mysql');
        
        // Set payload
        $payload = [];
		$payload['marketplace_id']   = $marketplace_id;
		$payload['product_name']     = $product_title;
		$payload['description']      = $product_description;
		$payload['remote_url']       = $product_slug;
        $payload['is_published']     = 1;
		$payload['ready_to_publish'] = 0;
        $payload['is_republished']   = 0;
		$payload['published_at']     = $current_time;
        $payload['updated_at']       = $current_time;
		
		if($product_type === 'store') { // Update store product
			$response = $this->request->makeRequestToUpdateStoreProduct($this->options, $this->options['merchr_hub_store_id'], $merchr_store_product_id, $payload);
		} else { // Add store product
			$payload['product_id'] = $merchr_product_id;
			$payload['selling_price']  = $product_price;
			$response = $this->request->makeRequestToCreateStoreProduct($this->options, $this->options['merchr_hub_store_id'], $payload);
		}
		
		return json_decode($response, true);
	}
	
	/**
	 * Send Import Email
	 *
	 * @param int
	 * @param string
	 * @param string
	 * @param string
	 */
	protected function sendImportEmail(int $status, string $created_at, string $iniator_email, string $initiator_name)
	{
		$replacements = [];
		
		// Check we have a name
		if($initiator_name === '') {
			$initiator_name = __('Merchr User', 'merchr');
		}
		
		// Format date and time
		$date = date("l jS", strtotime($created_at));
		$time = date("g:i a", strtotime($created_at));
		$date_and_time = __(sprintf('%s at %s', $date, $time), 'merchr');
		
		// Set site URL 
		$site_url = get_site_url();
		
		// Check status and set appropriate subject and message
		if($status == 1) {
			$subject = __('Merchr Products Successfully Imported!', 'merchr');
			$replacements['intro'] = __(sprintf("Dear %s,", $initiator_name), 'merchr');
			$replacements['status_message'] = __(sprintf("The product import started on %s has successfully completed!", $date_and_time), 'merchr');
			$replacements['view_link'] = $site_url;
			$replacements['view_link_text'] = __('Click here to view your store!', 'merchr');
			$replacements['login_link'] = $site_url . '/wp-admin/';
			$replacements['login_link_text'] = __('Or click here to login to your store', 'merchr');
			$replacements['signature'] = __('Your website', 'merchr');
			$template = MerchrHubHelpersTemplates::fetchTemplateContents('import_success.tpl', $this->templates_path_mail);
		} else {
			$subject = __('Merchr Products Failed to be Imported!', 'merchr');
			$replacements['intro'] = __(sprintf("Dear %s,", $initiator_name), 'merchr');
			$replacements['status_message'] = __(sprintf("The product import failed that was started on %s!", $date_and_time), 'merchr');
			$replacements['login_link'] = $site_url . '/wp-admin/';
			$replacements['login_link_text'] = __('Click here to login and import your products', 'merchr');
			$replacements['signature'] = __('Your website', 'merchr');
			$template = MerchrHubHelpersTemplates::fetchTemplateContents('import_fail.tpl', $this->templates_path_mail);
		}
		
		// Parse body content
		$body = MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
		
		// Set site name and domain	
		$site_name = get_bloginfo('name');
		
		// Set headers and send mail
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			"Reply-To: {$site_name} <no-reply@merchr.com>",
		];
		wp_mail($iniator_email, $subject, $body, $headers);
	}
	
	/**
	 * Update Import Tables
	 *
	 * @param int
	 * @param int
	 * @param array
	 */
	protected function updateImportTables(int $status, int $id, array $products)
	{
		$failed = 1 - $status;
		$this->wpdb->update( 
			$this->merchr_tables->queue,
			[
				'processed' => 1,
				'processing' => 0,
				'failed' => $failed
			],
			['id' => $id], 
			['%d','%d','%d'], 
			['%d'] 
		);
		
		// Remove import_queued flag from products for this import
		$ids = [];
		foreach($products as $values) {
			$id = (int) $values['id'];
			$ids[] = $id;
		}
		$processed_ids = implode(',', $ids);
		$this->wpdb->query("UPDATE {$this->merchr_tables->products} SET `import_queued`='0' WHERE id IN({$processed_ids});");
		
		// Final action, if success check the Merchr hub import is marked as completed.
		$merchr_options = get_option('merchr_hub_options');
		if($merchr_options['merchr_hub_products_imported'] == 'no') {
			$merchr_options['merchr_hub_products_imported'] = 'yes';
			update_option('merchr_hub_options', $merchr_options);
		}
	}
	
	/**
	 * Update Import Table Process started
	 *
	 * @param int
	 */
	protected function updateImportTableProcessstarted(int $id)
	{
		$this->wpdb->update( 
			$this->merchr_tables->queue,
			[
				'processing' => 1,
			],
			['id' => $id], 
			['%d'], 
			['%d'] 
		);
	}
	
	/**
	 * Process Product Variants
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param int
	 * @param int
	 * @param int
	 * @param float
	 * @param float
	 * @param array
	 * @param array
	 */
	protected function processProductVariants(
        $product, 
        int $id, 
        int $merchr_product_id, 
        int $merchr_store_product_id, 
        float $price, 
        float $tax_calcualtaion_value, 
        array $currencies, 
        string $product_currency, 
        array $hub_result, 
        array $merchr_options
    )
	{
		// Check for new variant object store_product_variants
        if(!isset($hub_result['data']['store_product_variants']) || empty($hub_result['data']['store_product_variants'])) {
            // Process with legacy method
            $this->processProductVariantsLegacy(
                $product, 
                $id, 
                $merchr_product_id, 
                $merchr_store_product_id, 
                $price, 
                $tax_calcualtaion_value, 
                $hub_result, 
                $merchr_options
            );
            return;
        }
        
        $product_data = $hub_result['data']['product'];
		$product_options = array_reverse($product_data['options']);
		$product_variants = $product_data['variants'];
        $store_product_variants = $hub_result['data']['store_product_variants'];
		$product_design = $hub_result['data']['design'];
        
        // Get variant array keyed by variant ID
        $keyed_variants = MerchrHubHelpersProducts::returnIdKeyedVariantArray($product_variants);
        
		// Add product options
		$attributes_list = MerchrHubHelpersProducts::createProductAttributes($product, $product_options);
		
		$i = 1;
		foreach($store_product_variants as $variant) {
            $variant_sku = '';
            $image_name = '';
			$image_url = $variant['store_product_image_url'];
            $variant_name = $variant['product_name'];
            $show = (int) $variant['is_show'];
			$variant_merchr_id = (int) $variant['product_variant_id'];
            $variant_store_merchr_id = (int) $variant['id'];
			$variant_price = floatval($variant['selling_price']);
            
            // Check if we are to add this variant
            if($show === 0) {
                continue;
            }
            
            // Set SKU
            if(isset($keyed_variants[$variant_merchr_id])) {
                $variant_sku = $keyed_variants[$variant_merchr_id]['sku'];
            } else { // Need SKU so skip
                continue;
            }
            
            // Check variant has values
            if(empty($keyed_variants[$variant_merchr_id]['product_variant_values'])) {
                continue;
            }
            
            // Format price
            $variant_price = MerchrHubHelpersTax::returnFormattedPrice($variant_price, $tax_calcualtaion_value);
            
            // Check for currency mismatch and adjust
            list($variant_price, $variant_price_converted, $variant_price_rate) = MerchrHubHelpersCurrencies::checkAndConvertCurrency(
                $currencies, 
                $this->store_base_currency, 
                $product_currency, 
                $variant_price
            );
            
            // Create variant object and assign basic values
            $variation = new \WC_Product_Variation();
			$variation->set_parent_id($product->get_id());
			$variation->set_sku($variant_sku . "-{$id}-{$i}");
			$variation->set_name($variant_name);
            $variation->set_price($variant_price);
			$variation->set_regular_price($variant_price);
			$variation->set_stock_status('instock');
            
            // Check we have an image
			if($image_url !== null) {
                $image_parts = explode('/', $image_url);
				$image_name = end($image_parts);
			}
            
            // Upload image
			if($image_name !== '') {
				$image = [];
				$image['name'] = $image_name;
				$image['tmp_name'] = \download_url($image_url);
				$image_attachment_id = \media_handle_sideload($image);
				$variation->set_image_id($image_attachment_id);
			}
            
            // Assign Attributes to this variation
            if(isset($keyed_variants[$variant_merchr_id])) {
                $attribute_metadata = [];
                
                foreach($keyed_variants[$variant_merchr_id]['product_variant_values'] as $key => $value) {
                    if(!isset($attributes_list[$key][$value])) {
                        continue;
                    }
                    
                    $variant_attributes_name = str_replace(' ', '-', strtolower(trim($attributes_list[$key][$value][0])));
                    $variant_attributes_option_name = trim($attributes_list[$key][$value][1]);
                    
                    if($variant_attributes_name === '' || $variant_attributes_option_name === '') {
                        continue;
                    }
                    
                    $attribute_metadata[$variant_attributes_name] = $variant_attributes_option_name;
                }
                
                $variation->set_attributes($attribute_metadata);
            }
			
			// Save variation
			$variation->save();
            
            // Variation ID
			$variation_id = $variation->get_id();
            
            // Set currency postmeta
            if($variant_price_converted) {
                add_post_meta($variation_id, 'merchr_hub_converted_from', $product_currency);
                add_post_meta($variation_id, 'merchr_hub_converted_to', $this->store_base_currency);
                add_post_meta($variation_id, 'merchr_hub_converted_rate', $variant_price_rate);
            }
			
			// Set attribute postmeta
			foreach($attribute_metadata as $key => $value) {
				$meta_key = strtolower($key);
				add_post_meta($variation_id, "attribute_{$meta_key}", $value);
			}
            
            // Check for attributes set as any.
			// An attribute set like this will not 
			// have any product variant values and 
			// still need to set an empty attribute 
			// meta or will show as product out-of-stock
			if(isset($keyed_variants[$variant_merchr_id])) {
                foreach($product_options as $option) {
                    $found = false;
                    
                    // Set option id and name
                    $option_id = $option['id'];
                    $name = strtolower($option['product_option_name']);
                    
                    // Loop assigned values, if not found, add blank meta
                    foreach($keyed_variants[$variant_merchr_id]['product_variant_values'] as $key => $value) {
                        if($option_id == $key) {
                            $found = true;
                        }
                    }
                    if(!$found) {
                        add_post_meta($variation_id, "attribute_{$name}", '');
                    }
                }
            }
            
            // Check for variant custom colour
			if(isset($variant['editable_text_area_colour']) && trim($variant['editable_text_area_colour']) != '') {
				add_post_meta($variation_id, 'merchrcust_variation_text_color', $variant['editable_text_area_colour']);
			}
            
            // Set design data if present
			if(!empty($product_design)) {
				add_post_meta($variation_id, 'merchr_hub_design_id', $product_design['id']);
			}
            
            // Set product ID, store product ID and original product variant SKU Meta
			add_post_meta($variation_id, 'merchr_hub_product_id', $merchr_product_id);
			add_post_meta($variation_id, 'merchr_hub_store_product_id', $merchr_store_product_id);
            add_post_meta($variation_id, 'merchr_hub_store_product_variant_id', $variant_store_merchr_id);
			add_post_meta($variation_id, 'merchr_hub_original_variant_sku', $variant_sku);
            
            // Save variation
			$variation->save();
            
            $i++;
        }
		
		// Final product save
		$product->save();
	}
    
    /**
	 * Process Product Variants Legacy
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param int
	 * @param int
	 * @param int
	 * @param float
	 * @param float
	 * @param array
	 * @param array
	 */
	protected function processProductVariantsLegacy(
        $product, 
        int $id, 
        int $merchr_product_id, 
        int $merchr_store_product_id, 
        float $price, 
        float $tax_calcualtaion_value, 
        array $hub_result, 
        array $merchr_options
    )
	{
		$product_data = $hub_result['data']['product'];
		$product_images = $product_data['images'];
		$product_options = array_reverse($product_data['options']);
		$product_variants = $product_data['variants'];
		$product_design = $hub_result['data']['design'];
		
		// Add options
		$i = 0;
		$attributes = [];
		$attributes_list = [];
		foreach($product_options as $option) {
			// Set option id and name
			$option_id = $option['id'];
			$name = $option['product_option_name'];
			
			// Loop values and prepare for WC
			$values = [];
			foreach($option['product_assigned_values'] as $value) {
				$values[] = $value['product_option_value_name'];
				$attributes_list[$option_id][$value['id']] = [
					$name,
					$value['product_option_value_name'],
				];
			}
			
			$attribute = new \WC_Product_Attribute();
			$attribute->set_name($name);
			$attribute->set_options($values);
			$attribute->set_position($i);
			$attribute->set_visible(true);
			$attribute->set_variation(true);
			$attributes[] = $attribute;
			$i++;
		}
		$product->set_attributes($attributes);
		$product->save();
		
		// Set variations
        $i = 1;
		foreach($product_variants as $variant) {
			$image_name = '';
			$image_url = '';
			$variant_merchr_id = (int) $variant['id'];
			$variant_name = $variant['product_variant_name'];
			$variant_sku = $variant['sku'];
			$variant_price = floatval($variant['rrp']);
			
			$variation = new \WC_Product_Variation();
			$variation->set_parent_id($product->get_id());
			$variation->set_sku($variant_sku . "-{$id}-{$i}");
			$variation->set_name($variant_name);
            $variation->set_price($price);
			$variation->set_regular_price($price);
			$variation->set_stock_status('instock');
			
			// Check we have an image
			if(!empty($variant['images'])) {
				if($variant['images']['product_image'] !== null) {
					$image_url = $variant['images']['product_image']['url']['large_abs_url'];
					$image_name = $variant['images']['product_image']['url']['image'];
				} else {
					$image_url = $hub_result['data']['product_image']['url']['large_abs_url'];
					$image_name = $hub_result['data']['product_image']['url']['image'];
				}
				
				// We need to check if this store product has a design
				if(isset($hub_result['data']['store_product_images']) && !empty($hub_result['data']['store_product_images'])) {
					foreach($hub_result['data']['store_product_images'] as $store_image) {
						if(isset($store_image['product_variant_id']) && $store_image['product_variant_id'] == $variant_merchr_id) {
							$image_url = $store_image['save_path_image_url'];
							$image_parts = explode('/', $image_url);
							$image_name = end($image_parts);
						}
					}
				}
				
				// Upload image
				if($image_name !== '') {
					$image = [];
					$image['name'] = $image_name;
					$image['tmp_name'] = \download_url($image_url);
					$image_attachment_id = \media_handle_sideload($image);
					$variation->set_image_id($image_attachment_id);
				}
			}
			
			// Assign Attributes to this variation
			$attribute_metadata = [];
			foreach($variant['product_variant_values'] as $key => $value) {
				$variant_attributes = [];
				$variant_attributes_name = str_replace(' ', '-', strtolower(trim($attributes_list[$key][$value][0])));
				$variant_attributes_option_name = $attributes_list[$key][$value][1];
				$variation_attributes[$variant_attributes_name] = $variant_attributes_option_name;
				$variation->set_attributes([$variation_attributes]);
				$attribute_metadata[$variant_attributes_name] = $variant_attributes_option_name;
			}
			
			// Save variation
			$variation->save();
			
			// Variation ID
			$variation_id = $variation->get_id();
			
			// Set attribute postmeta
			foreach($attribute_metadata as $key => $value) {
				$meta_key = strtolower($key);
				add_post_meta($variation_id, "attribute_{$meta_key}", $value);
			}
			
			// Check for attributes set as any.
			// An attribute set like this will not 
			// have any product variant values and 
			// still need to set an empty attribute 
			// meta or will show as product out-of-stock
			foreach($product_options as $option) {
				$found = false;
				
				// Set option id and name
				$option_id = $option['id'];
				$name = strtolower($option['product_option_name']);
				
				// Loop assigned values, if not found, add blank meta
				foreach($variant['product_variant_values'] as $key => $value) {
					if($option_id == $key) {
						$found = true;
					}
				}
				if(!$found) {
					add_post_meta($variation_id, "attribute_{$name}", '');
				}
			}
			
			// Check for variant custom colour
			if(isset($variant['custom_text_colour']) && trim($variant['custom_text_colour']) != '') {
				add_post_meta($variation_id, 'merchrcust_variation_text_color', $variant['custom_text_colour']);
			}
			
			// Set design data if present
			if(!empty($product_design)) {
				add_post_meta($variation_id, 'merchr_hub_design_id', $product_design['id']);
			}
			
			// Set product ID, store product ID and original product variant SKU Meta
			add_post_meta($variation_id, 'merchr_hub_product_id', $merchr_product_id);
			add_post_meta($variation_id, 'merchr_hub_store_product_id', $merchr_store_product_id);
			add_post_meta($variation_id, 'merchr_hub_original_variant_sku', $variant_sku);
            
            $i++;
		}
		
		// Final product save
		$product->save();
	}
}
