<?php
/**
 * Merchr Hub Database Interaction Trait.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/traits
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\traits;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

trait MerchrHubDatabaseInteraction
{
	protected $wpdb; // @var array
	protected $prefix; // @var array
	protected $merchr_tables; // @var object
	
	/**
	 * Get Merchr Tables.
	 */
	public final function getMerchrTables()
	{
		if($this->merchr_tables !== null) {
			return $this->merchr_tables;
		}
		$this->merchr_tables = new class($this->prefix) {
			public $products;
			public $products_imports;
			public $taxonomies;
			public $orders;
			public $order_items;
            public $queue;
			public $failed_imports;
            
			public function __construct($prefix)
			{
				$this->products = $prefix . 'merchr_hub_import_products';
				$this->taxonomies = $prefix . 'merchr_hub_taxonomies';
				$this->orders = $prefix . 'merchr_hub_orders';
				$this->order_items = $prefix . 'merchr_hub_order_items';
				$this->queue = $prefix . 'merchr_hub_import_list';
				$this->failed_imports = $prefix . 'merchr_hub_failed_import_list';
			}
		};
		return $this->merchr_tables;
	}
}
