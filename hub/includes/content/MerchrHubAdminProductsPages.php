<?php
/**
 * Merchr Hub Products Pages.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\data\MerchrHubGetProductsAndAssociatedData;
use MerchrHub\includes\data\MerchrHubTaxonomies;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminProductsPages extends MerchrHubContent
{
	use MerchrHubDatabaseInteraction;
	
	protected $request; // @var MerchrHubGetProductsAndAssociatedData
	protected $merchr_taxonomies; // @var MerchrHubTaxonomies
	protected $store_products; // @var array
	
	/**
	 * Set up class.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Setup db
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
		
		// Set product request and setup connection
		$this->request = new MerchrHubGetProductsAndAssociatedData();
		$this->request->setupConnection();
		
		// Set Merchr Taxonomies
		$this->merchr_taxonomies = new MerchrHubTaxonomies();
	}
	
	/**
	 * Get Products.
	 *
	 * @param string
	 * @param array
	 * @param bool optional
	 * @param array optional
	 * @param array optional
	 *
	 * @return string
	 */
	public function getProducts(string $type, array $options, $first = false, array $stores = [], array $nonce_names = []) 
	{
		$products_content = '';
		
		// If first time, need to check stores and process
		if($type === 'store' && $first === true) {
			$store_count = count($stores);
			if($store_count > 1) {
				// We need to ask the user to select a store
				// Build options for select
				$options = [];
				$options[] = '<option value="">' . esc_attr__('Select your store here...','merchr') . '</option>';
				foreach($stores as $store) {
					// Check for remote hosted stores only
					if($store['hosting'] !== 'remote') {
						continue;
					}
					
					// Create option
					$id = (int) $store['id'];
					$store_name = esc_attr($store['store_name']);
					$store_url  = esc_attr($store['store_url']);
					$options[]  = '<option value="' . $id . '">' . $store_name . ' ' . $store_url . '</option>';
				}
				
				// Set replacements
				$replacements = [
					'title'             => esc_html__('You have more than 1 store!', 'merchr'),
					'message'           => esc_html__('Select the store you wish to connect too below.', 'merchr'),
					'action'            => esc_url(admin_url('admin-ajax.php?action=merchr_select_store')),
					'options'           => implode("\n", $options),
					'store_field_title' => esc_attr__('Select your store here', 'merchr'),
					'the_stores'        => esc_attr(json_encode($stores)),
					'nonce'             => wp_nonce_field($nonce_names['select_store'][0], $nonce_names['select_store'][1], true, false),
					'submit_title'      => esc_attr__('Proceed to Products', 'merchr'),
					'submit_value'      => esc_attr__('Proceed to Products', 'merchr'),
				];
				$template = MerchrHubHelpersTemplates::fetchTemplateContents('select_your_store.tpl', $this->templates_path);
				$content = MerchrHubHelpersTemplates::parseStringReplacements($template, $replacements);
				
				return $content;
			}
		}
		
		// Proceed with fetching products
		$this->getProductsFromMerchrHub($options);
		
		// Save imported products
		$this->saveProductsFromMerchrHub();
		
		$products_content = $this->parseProductsAndDisplay($options, $nonce_names);
		
		return $products_content;
	}
	
	/**
	 * Get Products From Merchr Hub.
	 *
	 * @var array
	 */
	protected function getProductsFromMerchrHub(array $options)
	{
		$store_id = (int) $options['merchr_hub_store_id'];
		$this->store_products  = json_decode($this->request->makeRequestForStoreProducts($options, $store_id), true);
	}
	
	/**
	 * Save Products From Merchr Hub.
	 *
	 *
	 * @return int
	 */
	protected function saveProductsFromMerchrHub()
	{
		// Fetch and Sort imported products
		$sorted_imported_products = [];
		$imported_products = $this->wpdb->get_results("SELECT `merchr_product_id`, `merchr_store_product_id`, `product_type` FROM {$this->merchr_tables->products}", ARRAY_A);
		foreach($imported_products as $imported_product) {
			$merchr_product_id       = (int) $imported_product['merchr_product_id'];
			$merchr_store_product_id = (int) $imported_product['merchr_store_product_id'];
			$product_type            = $imported_product['product_type'];
			if($product_type == 'merchr') {
				$sorted_imported_products['merchr'][$merchr_product_id] = $merchr_product_id;
			} else {
				$sorted_imported_products['store'][$merchr_store_product_id] = $merchr_store_product_id;
			}
		}
		
		// Get last import number
		$import_id = $this->wpdb->get_var("SELECT MAX(`import_id`) AS max FROM {$this->merchr_tables->products}");
		$new_import_id = $import_id + 1;
		
		// Save Store products
		if(isset($this->store_products['data'])) {
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
				
				// Validate, minimum requirements
				if($merchr_store_product_id == '' || 
				   $merchr_product_sku == '' || 
				   $product_title == '' || 
				   $product_price == '' || $product_price <= 0 || 
				   $product_cost == '' || $product_cost <= 0 
				) {
					continue;
				}
				
				// Check has not already been imported
				if(isset($sorted_imported_products['store'][$merchr_store_product_id])) {
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
			}
		}
		
		return $new_import_id;
	}
	
	/**
	 * Parse Products and Display.
	 *
	 * @var array
	 *
	 * @return string
	 */
	protected function parseProductsAndDisplay($options, $nonce_names)
	{
		$content = '';
		
		// Fetch imported product details
		$query_string = "SELECT 
			`id`, `merchr_product_id`, `merchr_store_product_id`, `merchr_product_sku`, `product_type`, 
			`product_title`, `product_description`, `product_price`, `product_cost`, `product_fee`, `product_img`, 
			`product_categories`, `product_collections`, `product_industries`, `imported`, `import_queued` 
			FROM 
			{$this->merchr_tables->products} 
		";
		$imported_products = $this->wpdb->get_results($query_string);
		$number_rows = $this->wpdb->num_rows;
		
		// Prepare data
		$store_products = [];
		foreach($imported_products as $product) {
			$id                      = (int) $product->id;
			$merchr_product_id       = (int) $product->merchr_product_id;
			$merchr_store_product_id = (int) $product->merchr_store_product_id;
			$merchr_product_sku      = esc_html($product->merchr_product_sku);
			$product_type            = $product->product_type;
			$product_title           = esc_html($product->product_title);
			$product_description     = $product->product_description;
			$product_price           = esc_html($product->product_price);
			$product_cost            = (float) $product->product_cost;
			$product_fee             = (float) $product->product_fee ;
			$product_img             = esc_attr(trim($product->product_img));
			$product_categories      = $product->product_categories;
			$product_collections     = $product->product_collections;
			$product_industries      = $product->product_industries;
			$imported                = (int) $product->imported;
			$import_queued           = (int) $product->import_queued;
			
            if($product_type !== 'merchr') {
                $store_products[$id] = [
					'id'                      => $id,
					'merchr_product_id'       => $merchr_product_id,
					'merchr_store_product_id' => $merchr_store_product_id,
					'merchr_product_sku'      => $merchr_product_sku,
					'product_type'            => $product_type,
					'product_title'           => $product_title,
					'product_description'     => $product_description,
					'product_price'           => $product_price,
					'product_cost'            => $product_cost,
					'product_fee'             => $product_fee,
					'product_img'             => $product_img,
					'product_categories'      => $product_categories,
					'product_collections'     => $product_collections,
					'product_industries'      => $product_industries,
					'imported'                => $imported,
					'import_queued'           => $import_queued,
				];
            }
        }
            
		// Now lets display the products
		
		// Get product card template contents
		$card_template = MerchrHubHelpersTemplates::fetchTemplateContents('product_card.tpl', $this->templates_path);
		
		// Preset image placeholder
		$img_placeholder = esc_url($this->admin_images_url . 'img-placeholder-sqr.jpg');
		
		// Process Store Products
		$merchr_store_products_output = [];
		foreach($store_products as $product) {
			// Check we have an image
			if($product['product_img'] == '') {
				$product['product_img'] = $img_placeholder;
			}
			
			// Check if importing and apply classes if so
			$importing_class = '';
			if($product['import_queued'] === 1) {
				$importing_class = ' merchr-hub-product-importing';
			}
			
			// Check if imported and apply classes if so
			$imported_class = '';
			if($product['imported'] === 1) {
				$imported_class = ' merchr-hub-product-imported';
			}
			
			// Set product cost and markup
			//$cost = number_format($product['product_cost'] + $product['product_fee'], 2);
			//$markup = number_format(($product['product_price'] - $cost), 2);
			//$markup_percentage = number_format((($markup / $cost) * 100), 2);
            $cost = 0.00;
			$markup = 0.00;
			$markup_percentage = 0;
			
			// Format description
			$description = trim(substr(nl2br(strip_tags($product['product_description'])), 0, 52)) . '...';
			$description_field = strip_tags(str_replace(['<br>','<br />','<br/>'], "\n", $product['product_description']));
			
			// Process categories, collections and industries
			list($categories, $collections, $industries) = $this->processTaxonomyDataAttributes(
				$product['product_categories'], 
				$product['product_collections'], 
				$product['product_industries']
			);
			
			// Set replacements
			$replacements = [
				'id'                              => $product['id'],
				'type'                            => 'store',
				'title'                           => $product['product_title'],
				'img'                             => $product['product_img'],
				'cost'                            => $cost,
				'price'                           => $product['product_price'],
				'description'                     => esc_html($description),
				'description_field'               => esc_attr($description_field),
				'select_button_text'              => esc_html__('SELECT', 'merchr'),
				'edit_button_text'                => esc_html__('EDIT', 'merchr'),
				'select_alt_text'                 => esc_html__('DESELECT', 'merchr'),
				'imported'                        => esc_html__('IMPORTED!', 'merchr'),
				'importing'                       => esc_html__('IMPORTING!', 'merchr'),
				'merchr_product_imported_class'   => $imported_class,
				'merchr_product_importing_class'  => $importing_class,
				'categories'                      => '[' . implode(",", $categories) . ']',
				'collections'                     => '[' . implode(",", $collections) . ']',
				'industries'                      => '[' . implode(",", $industries) . ']',
				'markup'						  => $markup,
				'markup_percentage'				  => $markup_percentage,
				'save'							  => esc_html__('Save Changes', 'merchr'),
				'product_name_title'              => esc_html__('Product Name', 'merchr'),
				'product_description_title'       => esc_html__('Product Description', 'merchr'),
				'product_selling_price_title'     => esc_html__('Your Selling Price', 'merchr'),
				'product_rrp_title'               => esc_html__('RRP', 'merchr'),
				'product_cost_title'              => esc_html__('Your Cost', 'merchr'),
				'product_markup_title'            => esc_html__('Your Markup', 'merchr'),
				'product_markup_percentage_title' => esc_html__('Your Markup %', 'merchr'),
				'accept_negative_profit'          => esc_html__('Click to accept negative profit', 'merchr'),
			];
			
			$merchr_store_products_output[] = MerchrHubHelpersTemplates::parseStringReplacements($card_template, $replacements);
		}
		
		// If no store products hide title
		$store_products_display = '';
		if(empty($merchr_store_products_output)) {
			$store_products_display = ' style="display:none;"';
		}
		
		// Get Merchr Taxonomies
		list($category_options, $collection_options, $industry_options) = $this->processMerchrTaxonomySelectOptions();
		
		// Return full display
		$content = MerchrHubHelpersTemplates::parseStringReplacements(
			MerchrHubHelpersTemplates::fetchTemplateContents('merchr_hub_product_container.tpl', $this->templates_path), 
			[
				'store_products'         => implode("\n", $merchr_store_products_output),
				'store_title'            => esc_html__('Your Store Products', 'merchr'),
				'store_products_display' => esc_attr($store_products_display),
				'category_options'       => implode("\n", $category_options),
				'collection_options'     => implode("\n", $collection_options),
				'industry_options'       => implode("\n", $industry_options),
				'reset_button'           => esc_html__('Reset Filter', 'merchr'),
				'nonce'                  => wp_nonce_field('merchr_import_products', '_nonce_import_products', true, false),
				'url'					 => esc_url(admin_url('admin-ajax.php?action=merchr_import_products')),
			]
		);
		
		return $content;
	}
	
	/**
	 * Process Taxonomy Data Attributes.
	 *
	 * @var array
	 * @var array
	 * @var array
	 *
	 * @return array
	 */
	protected function processTaxonomyDataAttributes($cats, $cols, $inds)
	{
		$categories = [];
		$collections = [];
		$industries = [];
		
		$product_categories = json_decode($cats);
		foreach($product_categories as $val) {
			$id = (int) trim($val->id);
			$categories[] = '"' . $id . '"';
		}
		$product_collection = json_decode($cols);
		foreach($product_collection as $val) {
			$id = (int) trim($val->id);
			$collections[] = '"' . $id . '"';
		}
		$product_industries = json_decode($inds);
		foreach($product_industries as $val) {
			$id = (int) trim($val->id);
			$industries[] = '"' . $id . '"';
		}
		
		return [
			$categories,
			$collections,
			$industries
		];
	}
	
	/**
	 * Process Merchr Taxonomy Select Options.
	 *
	 * @return array
	 */
	protected function processMerchrTaxonomySelectOptions()
	{
		$merchr_taxonomies = $this->merchr_taxonomies->getMerchrTaxonomies();
		
		// Create select options
		$category_options = [];
		$collection_options = [];
		$industry_options = [];
		$category_options[] = '<option value="">' . esc_attr__("Filter by category...") . '</option>';
		$collection_options[] = '<option value="">' . esc_attr__("Filter by collection...") . '</option>';
		$industry_options[] = '<option value="">' . esc_attr__("Filter by industry...") . '</option>';
		foreach($merchr_taxonomies as $taxonomy) {
			$taxonomy_id = (int) $taxonomy->taxonomy_id;
			$name = esc_attr($taxonomy->name);
			$type = $taxonomy->type;
			
			if($type === 'category') {
				$category_options[] = '<option value="' . $taxonomy_id . '">' . $name . '</option>';
			} else if($type === 'collection') {
				$collection_options[] = '<option value="' . $taxonomy_id . '">' . $name . '</option>';
			} else if($type === 'industry') {
				$industry_options[] = '<option value="' . $taxonomy_id . '">' . $name . '</option>';
			}
		}
		
		return [
			$category_options,
			$collection_options,
			$industry_options
		];
	}
}
