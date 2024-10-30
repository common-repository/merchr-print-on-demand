<?php
/**
 * Merchr Hub Taxonomies Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/data
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\data;

use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubTaxonomies
{
	use MerchrHubDatabaseInteraction;
	
	protected $request; // @MerchrHubGetProductsAndAssociatedData
	
	/**
	 * Set up connector and $wpdb.
	 */
	public function __construct() 
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
		
		// Set prodiuct request and setup connection
		$this->request = new MerchrHubGetProductsAndAssociatedData();
		$this->request->setupConnection();
	}
	
	/**
	 * Get Merchr Taxonomies.
	 *
	 * @return array
	 */
	public function getMerchrTaxonomies() 
	{
		return $this->wpdb->get_results("SELECT `taxonomy_id`, `parent_id`, `type`, `name` FROM {$this->prefix}merchr_hub_taxonomies");
	}
	
	/**
	 * Taxonomies Download and Save.
	 *
	 * @param array
	 */
	public function taxonomiesDownloadAndSave(array $options) 
	{
		$categories = json_decode($this->request->makeRequestForCategories($options), true);
		$collections = json_decode($this->request->makeRequestForCollections($options), true);
		$industries = json_decode($this->request->makeRequestForIndustries($options), true);
		
		// Save categories
		foreach($categories['data'] as $category) {
			$taxonomy_id = (int) $category['id'];
			$parent_id = (int) $category['parent_id'];
			$name = sanitize_text_field($category['name']);
			
			$this->wpdb->replace( 
				$this->merchr_tables->taxonomies, 
				[
					'taxonomy_id' => $taxonomy_id,
					'parent_id'   => $parent_id,
					'type'        => 'category',
					'name'        => $name
				],
				['%d','%d','%s','%s']
			);
		}
		
		// Save collections
		foreach($collections['data'] as $collection) {
			$taxonomy_id = (int) $collection['id'];
			$parent_id = (int) $collection['parent_id'];
			$name = sanitize_text_field($collection['name']);
			
			$this->wpdb->replace( 
				$this->merchr_tables->taxonomies, 
				[
					'taxonomy_id' => $taxonomy_id,
					'parent_id'   => $parent_id,
					'type'        => 'collection',
					'name'        => $name
				],
				['%d','%d','%s','%s']
			);
		}
		
		// Save industries
		foreach($industries['data'] as $industry) {
			$taxonomy_id = (int) $industry['id'];
			$parent_id = (int) $industry['parent_id'];
			$name = sanitize_text_field($industry['name']);
			
			$this->wpdb->replace( 
				$this->merchr_tables->taxonomies, 
				[
					'taxonomy_id' => $taxonomy_id,
					'parent_id'   => $parent_id,
					'type'        => 'industry',
					'name'        => $name
				],
				['%d','%d','%s','%s']
			);
		}
	}
}
