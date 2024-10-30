<?php
/**
 * Merchr Hub Admin Product Updater.
 *
 * @since      1.0.2
 * @package    Merchr
 * @subpackage Merchr/includes/actions
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\actions;

use MerchrHub\includes\helpers\MerchrHubHelpersCurrencies;
use MerchrHub\includes\helpers\MerchrHubHelpersCustomisation;
use MerchrHub\includes\helpers\MerchrHubHelpersProducts;
use MerchrHub\includes\helpers\MerchrHubHelpersTax;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminProductUpdater extends MerchrHubAdminProductImporter
{
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
        
        // Set maximum update limit
        $this->import_limit = 24;
		
		// Need to require these files
		if(!function_exists('media_handle_sideload')) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
		}
	}
    
    /**
	 * Process products for update.
	 */
	public function processProductsForDelete()
	{
        // Fetch deleted store products
		$merchr_options = get_option('merchr_hub_options');
		$store_id = (int) $merchr_options['merchr_hub_store_id'];
		$store_products = json_decode($this->request->makeRequestForStoreProductsDeleted($merchr_options, $store_id), true);
        
        if(isset($store_products['data'])) {
            foreach($store_products['data'] as $product) {
                $merchr_store_product_id = (int) $product['id'];
                $market_place_id = trim($product['marketplace_id']);
                
                // If market place ID present, use that
                if($market_place_id !== null && $market_place_id !== '') {
                    $this->deleteProduct($market_place_id);
                } else {
                    // Check database for market place ID
                    $fetched_id = $this->wpdb->get_var("SELECT `market_place_id` FROM {$this->merchr_tables->products} WHERE `merchr_store_product_id`='{$merchr_store_product_id}'");
                    if($fetched_id !== null) {
                        $fetched_id = (int) $fetched_id;
                        $this->deleteProduct($fetched_id);
                    }
                }
            }
        }
        
        return;
    }
    
    /**
	 * Process product stocks.
	 */
	public function processProductStocks()
	{
        $merchr_options = get_option('merchr_hub_options');
        $product_stocks = json_decode($this->request->makeRequestForProductStocks($merchr_options), true);
        
        if(isset($product_stocks['data'])) {
            $product_stock_levels = [];
            
            foreach($product_stocks['data'] as $stock) {
                $product_id = (int) $stock['product_id'];
                $variant_id = (int) $stock['product_variant_id'];
                $sku = trim($stock['sku']);
                $in_stock = (int) $stock['in_stock'];
                $stock_level = (int) $stock['stock_level'];
                $threshold = (int) $stock['out_of_stock_threshold'];
                
                // TODO: When stock levels updated on the hub, pass through here
                // For now, hard-set quantity of 100 and threshold of 5
                $product_stock_levels[$sku] = [
                    'in_stock' => $in_stock,
                    'stock_level' => 100,
                    'threshold' => 5,
                ];
            }
            
            // Fetch published products from WC
            $products = wc_get_products(
                [
                    'status' => 'publish',
                    'limit' => -1
                ]
            );
            
            foreach($products as $product) {
                $set = '';
                $id = (int) $product->get_id();
                $type = $product->get_type();
                $manage = (int) $product->get_manage_stock();
                
                // Check for product type
                if($type === 'simple') {
                    $merchr_hub_product_sku = trim(get_post_meta($id, 'merchr_hub_original_sku', true));
                    
                    // Check we have a stock record
                    if(isset($product_stock_levels[$merchr_hub_product_sku])) {
                        // Check manage status, turn on if 0
                        if($manage === 0) {
                            $product->set_manage_stock(true);
                        }
                        
                        $check_in_stock = $product_stock_levels[$merchr_hub_product_sku]['in_stock'];
                        $check_stock_level = $product_stock_levels[$merchr_hub_product_sku]['stock_level'];
                        $check_threshold = $product_stock_levels[$merchr_hub_product_sku]['threshold'];
                        
                        if($check_in_stock === 1) {
                            $product->set_stock_quantity($check_stock_level);
                            $product->set_stock_status('instock');
                            $product->set_low_stock_amount($check_threshold);
                        } else {
                            $product->set_stock_quantity(0);
                            $product->set_stock_status('outofstock');
                            $product->set_low_stock_amount($check_threshold);
                        }
                    }
                } else if($type === 'variable') {
                    // Get children (variants)
                    $variant_ids = $product->get_children(false);
                    
                    foreach($variant_ids as $variant_id) {
                        $variant = wc_get_product_object('variation', $variant_id);
                        $manage = (int) $variant->get_manage_stock();

                        $merchr_hub_product_variant_sku = trim(get_post_meta($variant_id, 'merchr_hub_original_variant_sku', true));
                        
                        // Check we have a stock record
                        if(isset($product_stock_levels[$merchr_hub_product_variant_sku])) {
                            // Check manage status, turn on if 0
                            if($manage === 0) {
                                $variant->set_manage_stock(true);
                            }
                            
                            $check_in_stock = $product_stock_levels[$merchr_hub_product_variant_sku]['in_stock'];
                            $check_stock_level = $product_stock_levels[$merchr_hub_product_variant_sku]['stock_level'];
                            $check_threshold = $product_stock_levels[$merchr_hub_product_variant_sku]['threshold'];
                            
                            if($check_in_stock === 1) {
                                $variant->set_stock_quantity($check_stock_level);
                                $variant->set_stock_status('instock');
                                $variant->set_low_stock_amount($check_threshold);
                            } else {
                                $variant->set_stock_quantity(0);
                                $variant->set_stock_status('outofstock');
                                $variant->set_low_stock_amount($check_threshold);
                            }
                        }
                        
                        // Save variant and clear caches
                        $variant->save();
                    }
                }
                
                // Save product and clear caches
                $product->save();
            }
        }
        
        return;
    }
	
	/**
	 * Process products for update.
	 */
	public function processProductsForUpdate()
	{
		// Fetch store products
		$products = $this->wpdb->get_results(
			"SELECT `id`, `merchr_product_id`, `merchr_store_product_id`, `market_place_id`, `created_at`, `updated_at` FROM `{$this->merchr_tables->products}` WHERE `product_type`='store' AND `imported`='1'"
		);
		$count = count($products);
        
        // Fetch active products ID's
        $args = [
            'numberposts' => -1,
            'fields'      => 'ids',
            'post_status' => ['publish', 'draft'],
            'post_type'   => 'product',
        ];
        $wc_product_ids = get_posts($args);
		
		if($count > 0) {
			$merchr_options = get_option('merchr_hub_options');
			$store_id = (int) $merchr_options['merchr_hub_store_id'];
			$tax_classes = json_decode($this->tax_request->makeRequestForTaxClasses($merchr_options), true);
			$tax_classes = $tax_classes['data'];
            $currencies = json_decode($this->currencies_request->makeRequestForCurrencies($merchr_options), true);
			$currencies = $currencies['data'];
			
			// Loop each store product
            $i = 0;
			foreach($products as $product) {
                $id = (int) $product->id;
				$merchr_product_id = (int) $product->merchr_product_id;
				$merchr_store_product_id = (int) $product->merchr_store_product_id;
				$market_place_id = (int) $product->market_place_id;
				$created_at = trim($product->created_at);
				$updated_at = trim($product->updated_at);
                
                // Check product exists in Woo before making request
                if(!in_array($market_place_id, $wc_product_ids)) {
                    $this->deleteProduct($market_place_id, true);
                    continue;
                }
                
				// Get store product data
				$store_product_data = json_decode($this->request->makeRequestForStoreProductByID($merchr_options, $store_id, $merchr_store_product_id), true);
				
				// Check we have product data
				if(isset($store_product_data['data'])) {
					// If data empty, deleted Product
                    if(empty($store_product_data['data'])) {
                        $this->deleteProduct($market_place_id);
                        continue;
                    }
                    
                    $data = $store_product_data['data'];
                    $store_product_is_republished = (int) $data['is_republished'];
					$store_product_created_at = trim($data['created_at']);
					$store_product_updated_at = trim($data['updated_at']);
                    $store_product_deleted_at = trim($data['deleted_at']);
                    
                    // Check if store product is deleted
                    if($store_product_deleted_at !== null && $store_product_deleted_at !== '') {
                        $this->deleteProduct($market_place_id);
                        continue;
                    }
                    
                    if($store_product_is_republished === 0) {
                        continue;
                    }
                    
                    // Check update limit
                    if($this->update_limit === $i) {
                        break;
                    }
                    
					// We have a product to update, get the product object
					$wc_product = wc_get_product($market_place_id);
                    
                    // Check product object returned
                    if($wc_product === false || $wc_product === null) {
                        continue;
                    }
                    
                    // Set as draft during update
					wp_update_post([
						'ID' => $market_place_id,
						'post_status' => 'draft'
					]);
					
					// Update the product
                    $now = date("Y-m-d H:i:s");
					try {
                        // Tax Handling and price correction if applicable
                        $tax_calcualtaion_value = 1.0;
                        $tax_class_id = (int) $data['product']['tax_class_id'];
                        $remove_tax = MerchrHubHelpersTax::isTaxRemovalRequired($merchr_options);
                        if($remove_tax) {
                            $tax_calcualtaion_value = MerchrHubHelpersTax::returnTaxCalculationValue($tax_class_id, $tax_classes, $tax_calcualtaion_value);
                        }
                        
                        // Format Price (Tax removal if applicable)
                        $selling_price = floatval($data['selling_price']);
                        $product_price = MerchrHubHelpersTax::returnFormattedPrice($selling_price, $tax_calcualtaion_value);
                        
                        // Check for currency mismatch and adjust
                        $product_currency = 'GBP';
                        if(isset($data['product']['currencies'][0]['code'])) {
                            $product_currency = $data['product']['currencies'][0]['code'];
                            list($product_price, $product_price_converted, $product_price_rate) = MerchrHubHelpersCurrencies::checkAndConvertCurrency(
                                $currencies, 
                                $this->store_base_currency, 
                                $product_currency, 
                                $product_price
                            );
                            
                            // Update currency postmeta
                            if($product_price_converted) {
                                update_post_meta($market_place_id, 'merchr_hub_converted_from', $product_currency);
                                update_post_meta($market_place_id, 'merchr_hub_converted_to', $this->store_base_currency);
                                update_post_meta($market_place_id, 'merchr_hub_converted_rate', $product_price_rate);
                            }
                        }
                        
                        // Check for country restrictions
                        MerchrHubHelpersProducts::checkAndSetCountryRestrictions($wc_product, $data['product']);
                        
                        // Update original product SKU
                        $wc_product->update_meta_data('merchr_hub_original_sku', $data['product']['sku']);
                        
                        // Update customisable
                        $product_customisable = (int) $data['product']['customisable'];
                        $wc_product->update_meta_data('merchrcust_customisable', $product_customisable);
                        
                        // Update basic details
						$this->updateBasicDetails($wc_product, $market_place_id, $data['product_name'], $product_price, $data['description'], $tax_class_id, $merchr_options);
						
						// Update main image
						$this->updateMainImage($wc_product, $data);
						
						// Check if we have to process design and customisation data
						$this->processDesignData($wc_product, $market_place_id, $data);
						
						// Check if variable product and process variants
						if($wc_product->is_type('variable')) {
                            $this->updateProductVariants(
                                $wc_product, 
                                $product_price, 
                                $id, 
                                $data, 
                                $tax_calcualtaion_value, 
                                $currencies, 
                                $product_currency, 
                                $merchr_options
                            );
						}
                        
                        // Set product slug
                        $product_slug = get_permalink($market_place_id);
						
						// Update the hub
						$hub = $this->createUpdateStoreProductOnHub(
							'store', 
							$merchr_product_id,
							$merchr_store_product_id,
							$market_place_id,
							$data['product_name'],
							$data['description'],
							$product_price,
                            $product_slug
						);
						
						// Update updated_at field in import table
						$this->updateProductImportTable($id);
						
						// Final save of product
						$wc_product->save();
					} catch (\Exception $e) {
						error_log("{$now} Product ({$market_place_id}) failed to update:\n{$e}", 0);
					}
                    
                    // Restore as published
					wp_update_post([
						'ID' => $market_place_id,
						'post_status' => 'publish'
					]);
				}
                
                $i++;
			}
		}
		
		return;
	}
	
	/**
	 * Update Basic Details.
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
     * @param int
	 * @param string
	 * @param string
	 * @param string
	 * @param int
	 * @param float
	 * @param array
	 */
	protected function updateBasicDetails(
        $product, 
        int $market_place_id, 
        string $product_title, 
        string $product_price, 
        string $product_description, 
        int $tax_class_id, 
        array $merchr_options
    )
	{
		// Update details
		$product->set_name($product_title);
		$product->set_price($product_price);
		$product->set_regular_price($product_price);
		
		// Set description(s) depending on setting
		if($merchr_options['merchr_hub_description_location'] == 'short') {
			$product->set_short_description(nl2br($product_description));
			$product->set_description('');
		} else if($merchr_options['merchr_hub_description_location'] == 'full') {
			$product->set_description(nl2br($product_description));
			$product->set_short_description('');
		} else { // Both
			$product->set_description(nl2br($product_description));
			$product->set_short_description(nl2br($product_description));
		}
		
		// Save product
		$product->save();
	}
	
	/**
	 * Update Main Image.
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param array
	 */
	protected function updateMainImage($product, array $data)
	{
		$main_image = $data['product_image']['url']['large_abs_url'];
		$main_image_name = $data['product_image']['image'];
        
        // First check for store product variant main image
		$main_found = false;
        if(isset($data['store_product_variants']) && !empty($data['store_product_variants'])) {
            foreach($data['store_product_variants'] as $store_image) {
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
            if(isset($data['store_product_images']) && !empty($data['store_product_images'])) {
                foreach($data['store_product_images'] as $store_image) {
                    if($store_image['is_main_image'] == true) {
                        $main_image = $store_image['save_path_image_url'];
                        $main_image_parts = explode('/', $main_image);
                        $main_image_name = end($main_image_parts);
                    }
                }
            }
        }
		
		// Upload image and save
		$image = [];
		$image['name'] = $main_image_name;
		$image['tmp_name'] = \download_url($main_image);
		$image_attachment_id = \media_handle_sideload($image);
		$product->set_image_id($image_attachment_id);
		$product->save();
	}
	
	/**
	 * Process Design Data.
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
     * @param int
	 * @param array
	 */
	protected function processDesignData($product, int $market_place_id, array $data)
	{
		$hide_editor = false;
		
		// Check embossed/engraved
        if(isset($data['product']['is_engraved_embossed'])) {
			$embossed_engraved = (int) $data['product']['is_engraved_embossed'];
            $product->update_meta_data('merchrcust_emboss_engraved', $embossed_engraved);
			$product->save_meta_data();
		}
        
        // Set no editor and delete design ID metadata if no design
		if(empty($data['design'])) {
			$product->update_meta_data('merchrcust_placement_editor_type', 'none');
			@delete_post_meta($market_place_id, 'merchr_hub_design_id');
			$product->save_meta_data();
			return;
		}
		
		// Update meta data
		$design = $data['design'];
		update_post_meta($market_place_id, 'merchr_hub_design_id', $design['id']);
		$product->save_meta_data();
		
		// Check if customisable
		if($data['product']['customisable'] == 0) {
			// Set no editor
			$product->update_meta_data('merchrcust_placement_editor_type', 'none');
			$product->save_meta_data();
			return;
		}
		
		// Check for an editable area
		$editable_text_areas = trim($design['editable_text_areas']);
		if($editable_text_areas !== '' && $editable_text_areas !== null) {
			$editable_text_areas = json_decode($editable_text_areas, true);
			if(is_array($editable_text_areas) && !empty($editable_text_areas)) {
				// As we have an editable area, set image preview data
				$image_preview = [];
				if(isset($data['product_image']['url']['image_preview'])) {
					$image_preview = $data['product_image']['url']['image_preview'];
				}
				
				// Process customisation data
				MerchrHubHelpersCustomisation::processCustomisationData(
					$product, 
					$editable_text_areas, 
					$image_preview,
					$design
				);
			} else {
				$hide_editor = true;
			}
		} else {
			$hide_editor = true;
		}
		
		// Set no editor and save
		if($hide_editor) {
			$product->update_meta_data('merchrcust_placement_editor_type', 'none');
			$product->save_meta_data();
		}
	}
    
    /**
	 * Update Product Variants
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param float
	 * @param int
	 * @param array
	 * @param float
     * @param array
     * @param string
	 * @param array
	 */
	protected function updateProductVariants(
        $product, 
        float $product_price, 
        int $import_id, 
        array $data, 
        float $tax_calcualtaion_value, 
        array $currencies,
        string $product_currency,
        array $merchr_options
    )
	{
        // Check we have variant data
		if(!isset($data['product']['variants'])) {
			return;
		}
        
        // Check for new variant object store_product_variants
        if(!isset($data['store_product_variants']) || empty($data['store_product_variants'])) {
            // Process with legacy method
            $this->updateProductVariantsLegacy(
                $product, 
                $product_price, 
                $import_id, 
                $data, 
                $tax_calcualtaion_value, 
                $merchr_options
            );
            return;
        }
		
		$product_data = $data['product'];
		$product_options = array_reverse($product_data['options']);
		$product_variants = $product_data['variants'];
        $store_product_variants = $data['store_product_variants'];
		$product_design = $data['design'];
        
        // Get children ID's (variants)
        $variation_ids = $product->get_children();
        
        // Get variant array keyed by variant ID
        $keyed_variants = MerchrHubHelpersProducts::returnIdKeyedVariantArray($product_variants);
        
        // Get attributes list
        $attributes_list = MerchrHubHelpersProducts::returnAttributesList($product_options);
        
        // Update product attributes
        $attribute_names = [];
        $attribute_values = [];
        foreach($attributes_list as $attributes) {
            foreach($attributes as $attribute) {
                $name = trim($attribute[0]);
                $value = trim($attribute[1]);
                $attribute_names[$name] = $name;
                $attribute_values[$name][] = $value;
            }
        }
        
        // Create attribute update array
        $attribute_update_data = [];
        foreach($attribute_names as $attribute_name) {
            if(isset($attribute_values[$attribute_name])) {
                $values = implode(" | ", $attribute_values[$attribute_name]);
                $attribute_update_data[$attribute_name] = [
                    'name'         => $attribute_name,
                    'value'        => $values,
                    'position'     => 0,
                    'is_visible'   => 1,
                    'is_variation' => 1,
                    'is_taxonomy'  => 0
                ];
            }
        }
        
        // Update
        if(count($attribute_update_data) > 0) {
            update_post_meta($product->get_id(), '_product_attributes', $attribute_update_data);
        }
        
        // Prepare remote variant data
        $variant_data = [];
		foreach($store_product_variants as $variant) {
			// The import ID is appended to the variant SKU when saved to allow the same products with different 
			// designs to be added, WC won't duplicate SKU's so we make an adjustment here for the check
			
            // Set SKU
            if(isset($keyed_variants[$variant['product_variant_id']])) {
                $variant_sku = $keyed_variants[$variant['product_variant_id']]['sku'];
            } else { // Need SKU so skip
                continue;
            }
            
            // Check variant has values
            if(empty($keyed_variants[$variant['product_variant_id']]['product_variant_values'])) {
                continue;
            }

            $variant_data[$variant['id']] = [
				'id' => (int) $variant['id'],
                'original_sku' => $variant_sku,
                'store_product_id' => (int) $variant['store_product_id'],
                'product_variant_id' => (int) $variant['product_variant_id'],
                'name' => $variant['product_name'],
                'is_show' => (int) $variant['is_show'],
                'price' => floatval($variant['selling_price']),
                'editable_text_area_colour' => $variant['editable_text_area_colour'],
                'image' => $variant['store_product_image_url'],
			];
		}
        
		// Loop through product variants
        $i = 1;
        $remove = [];
		foreach($variation_ids as $variant_id) {
			$variation = wc_get_product_object('variation', $variant_id);
			$variation_id = $variation->get_id();
			$sku = $variation->get_sku();
            $store_product_variant_id = get_post_meta($variation_id, 'merchr_hub_store_product_variant_id', true);
            
			// Check we have a match and update
            if(isset($variant_data[$store_product_variant_id])) {
				// Check if this is to be shown
                if($variant_data[$store_product_variant_id]['is_show'] === 0) {
                    $variation->delete();
                    $remove[] = $store_product_variant_id;
                    $i++;
                    continue;
                }
                
                $image_url = '';
                $image_name = '';
				
				// Format price
                $variant_price = MerchrHubHelpersTax::returnFormattedPrice($variant_data[$store_product_variant_id]['price'], $tax_calcualtaion_value);
                
                // Check for currency mismatch and adjust
                list($variant_price, $variant_price_converted, $variant_price_rate) = MerchrHubHelpersCurrencies::checkAndConvertCurrency(
                    $currencies, 
                    $this->store_base_currency, 
                    $product_currency, 
                    $variant_price
                );
                
                // Update original SKU
                update_post_meta($variation_id, 'merchr_hub_original_variant_sku', $variant_data[$store_product_variant_id]['original_sku']);
                
                // Update currency post meta
                if($variant_price_converted) {
                    update_post_meta($variation_id, 'merchr_hub_converted_from', $product_currency);
                    update_post_meta($variation_id, 'merchr_hub_converted_to', $this->store_base_currency);
                    update_post_meta($variation_id, 'merchr_hub_converted_rate', $variant_price_rate);
                }
                
                // Set random chars
                $chars = MerchrHubHelpersProducts::randomChars(8);
                
                // Set updates
                $variation->set_sku($variant_data[$store_product_variant_id]['original_sku'] . "-{$i}-{$chars}");
				$variation->set_name($variant_data[$store_product_variant_id]['name']);
                $variation->set_price($variant_price);
                $variation->set_regular_price($variant_price);
                update_post_meta($variation_id, '_price', $variant_price);
				
				// Check we have an image
                if($variant_data[$store_product_variant_id]['image'] !== null) {
                    $image_url = $variant_data[$store_product_variant_id]['image'];
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
				
				// Save variation changes
				$variation->save();
				
				if(isset($variant_data[$store_product_variant_id]['editable_text_area_colour']) && trim($variant_data[$store_product_variant_id]['editable_text_area_colour']) != '') {
                    update_post_meta($variation_id, 'merchrcust_variation_text_color', $variant_data[$store_product_variant_id]['editable_text_area_colour']);
                } else {
					@delete_post_meta($variation_id, 'merchrcust_variation_text_color');
				}
				
				// Set design data if present
				if(!empty($data['design'])) {
					update_post_meta($variation_id, 'merchr_hub_design_id', $data['design']['id']);
				} else {
					@delete_post_meta($variation_id, 'merchr_hub_design_id');
				}
                
                // Remove from update
                $remove[] = $store_product_variant_id;
			}
            
            $i++;
		}
        
        // Remove processed variants from data
        foreach($remove as $key) {
            unset($variant_data[$key]);
        }
        
        // If count greater than 0, new variants to add
        $count = count($variant_data);
        foreach($variant_data as $sku => $variant) {
            $image_name = '';
			$image_url = '';
            
            // Check if we are to add this variant
            if($variant['is_show'] === 0) {
                continue;
            }
            
            // Format price
            $variant_price = floatval($variant['price']);
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
			$variation->set_sku($sku . "-{$i}");
			$variation->set_name($variant['name']);
            $variation->set_price($variant_price);
			$variation->set_regular_price($variant_price);
			$variation->set_stock_status('instock');
            
             // Check we have an image
			if($variant['image'] !== null) {
				$image_url = $variant['image'];
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
            if(isset($keyed_variants[$variant['product_variant_id']])) {
                $attribute_metadata = [];
                
                foreach($keyed_variants[$variant['product_variant_id']]['product_variant_values'] as $key => $value) {
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
			if(isset($keyed_variants[$variant['product_variant_id']])) {
                foreach($product_options as $option) {
                    $found = false;
                    
                    // Set option id and name
                    $option_id = $option['id'];
                    $name = strtolower($option['product_option_name']);
                    
                    // Loop assigned values, if not found, add blank meta
                    foreach($keyed_variants[$variant['product_variant_id']]['product_variant_values'] as $key => $value) {
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
			add_post_meta($variation_id, 'merchr_hub_product_id', $keyed_variants[$variant['product_variant_id']]['product_id']);
			add_post_meta($variation_id, 'merchr_hub_store_product_id', $variant['store_product_id']);
            add_post_meta($variation_id, 'merchr_hub_store_product_variant_id', $variant['id']);
			add_post_meta($variation_id, 'merchr_hub_original_variant_sku', $keyed_variants[$variant['product_variant_id']]['sku']);
            
            $i++;
        }
        
        // Final product save
		$product->save();
		
		return;
	}
	
	/**
	 * Update Product Variants Legacy
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param float
	 * @param int
	 * @param array
	 * @param float
	 * @param array
	 */
	protected function updateProductVariantsLegacy(
        $product, 
        float $product_price, 
        int $import_id, 
        array $data, 
        float $tax_calcualtaion_value, 
        array $merchr_options
    )
	{
		// Check we have variant data
		if(!isset($data['product']['variants'])) {
			return;
		}
        
        $variation_ids = $product->get_children();
		
		// Prepare remote variant data
        $variant_data = [];
		$remote_variant_data = $data['product']['variants'];
		foreach($remote_variant_data as $variant) {
			// The import ID is appended to the variant SKU when saved to allow the same products with different 
			// designs to be added, WC won't duplicate SKU's so we make an adjustment here for the check
			$variant_data["{$variant['sku']}-{$import_id}"] = [
				'id' => $variant['id'],
				'name' => $variant['product_variant_name'],
				'price' => floatval($variant['rrp']),
				'images' => $variant['images'],
			];
			
			if(isset($variant['custom_text_colour'])) {
				$variant_data["{$variant['sku']}-{$import_id}"]['custom_text_colour'] = $variant['custom_text_colour'];
			}
		}
		
		// Loop through product variants
		foreach($variation_ids as $variant_id) {
			$variation = wc_get_product_object('variation', $variant_id);
			$variation_id = $variation->get_id();
			$sku = $variation->get_sku();
            $hyphen_count = substr_count($sku, '-');
            
            // Backward compatibility
            if($hyphen_count === 1) {
                $sku_check = $sku;
            } else {
                $parts = explode('-', $sku);
                array_pop($parts);
                $sku_check  = implode('-', $parts);
            }
			
			// Check we have a match and update
			if(isset($variant_data[$sku_check])) {
				$image_name = '';
				$image_url = '';
				$variant_merchr_id = $variant_data[$sku_check]['id'];
				$name = $variant_data[$sku_check]['name'];
				$price = floatval($variant_data[$sku_check]['price']);
                
                // Set updates
				$variation->set_name($name);
                $variation->set_price($product_price);
                $variation->set_regular_price($product_price);
                update_post_meta($variation_id, '_price', $product_price);
				
				// Process image
				if($variant_data[$sku_check]['images']['product_image'] !== null) {
					$image_url = $variant_data[$sku_check]['images']['product_image']['url']['large_abs_url'];
					$image_name = $variant_data[$sku_check]['images']['product_image']['url']['image'];
				} else {
					$image_url = $data['product_image']['url']['large_abs_url'];
					$image_name = $data['product_image']['url']['image'];
				}
				
				// We need to check if this store product has a design
				if(isset($data['store_product_images']) && !empty($data['store_product_images'])) {
					foreach($data['store_product_images'] as $store_image) {
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
				
				// Save variation changes
				$variation->save();
				
				// Check for variant custom colour
				if(isset($variant_data[$sku_check]['custom_text_colour']) && trim($variant_data[$sku_check]['custom_text_colour']) != '') {
					update_post_meta($variation_id, 'merchrcust_variation_text_color', $variant['custom_text_colour']);
				} else {
					@delete_post_meta($variation_id, 'merchrcust_variation_text_color');
				}
				
				// Set design data if present
				if(!empty($data['design'])) {
					update_post_meta($variation_id, 'merchr_hub_design_id', $data['design']['id']);
				} else {
					@delete_post_meta($variation_id, 'merchr_hub_design_id');
				}
			}
		}
		
		return;
	}
    
    /**
	 * Delete Product.
	 *
     * @param int
     * @param bool
	 */
	protected function deleteProduct(int $id, bool $merchr_table_only = false)
	{
        $product = wc_get_product($id);
        
        // Delete from Merchr Database
        $this->wpdb->delete(
            $this->merchr_tables->products,
            [
                'market_place_id' => $id,
            ]
        );
        
        if($merchr_table_only === false) {
            // Check we have a product
            if($product === false || $product === null) {
                return;
            }
            
            // Check is not already trashed
            if($product->get_status() === 'trash') {
                return;
            }
            
            // Delete the product (trash)
            $product->delete();
        }
    }
	
	/**
	 * Process products for update.
	 *
	 * @param int
	 */
	protected function updateProductImportTable(int $id)
	{
		$this->wpdb->update( 
			$this->merchr_tables->products,
			[
				'updated_at' => current_time('mysql')
			],
			['id' => $id],
			['%s'],
			['%d']
		);
	}
}
