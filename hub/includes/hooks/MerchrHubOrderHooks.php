<?php
/**
 * Merchr Hub Order Hooks Class.
 *
 * @since      1.0.1
 * @package    Merchr
 * @subpackage Merchr/includes/hooks
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\hooks;

use MerchrHub\includes\MerchrHubHooks;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubOrderHooks extends MerchrHubHooks
{
	use MerchrHubDatabaseInteraction;
	
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
	}
	
	/**
	 * Pre-process Order Paid.
	 *
	 * @param int
	 */
	public function preProcessOrderPaid(int $order_id)
	{
		$has_merchr_product = false;
        $order = wc_get_order($order_id);
		$order_total = number_format($order->get_total(), 2);
		
		// We need to check if contains any Merchr Products
        foreach($order->get_items(['line_item']) as $item_id => $item) {
            // Set Merchr product and store product ID
            $item_product_id = (int) $item->get_product_id();
			$merchr_hub_product_id = (int) get_post_meta($item_product_id, 'merchr_hub_product_id', true );
			$merchr_hub_store_product_id = (int) get_post_meta($item_product_id, 'merchr_hub_store_product_id', true );
					
			// Check this is a Merchr product
			if($merchr_hub_product_id !== 0 || $merchr_hub_store_product_id !== 0) {
				$has_merchr_product = true;
                break;
			}
        }
        
        // Add to orders table
        if($has_merchr_product) {
            $this->wpdb->insert(
                $this->merchr_tables->orders, 
                [
                    'order_id'     => $order_id,
                    'order_total'  => $order_total,
                    'date_created' => current_time('mysql'),
                ],
                [
                    '%d',
                    '%f',
                    '%s',
                ]
            );
        }
        
		return;
	}
	
	/**
	 * Cancel Order.
	 *
	 * @param int
	 */
	public function cancelOrder(int $order_id)
	{
		$this->wpdb->update( 
			$this->merchr_tables->orders, 
			['cancelled' => 1], 
			['order_id' => $order_id], 
			['%d'], 
			['%d'] 
		);
        
		return;
	}
}
