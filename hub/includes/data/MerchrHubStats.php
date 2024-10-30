<?php
/**
 * Merchr Hub Stats Class.
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

class MerchrHubStats
{
	use MerchrHubDatabaseInteraction;
	
	/**
	 * Assign $wpdb.
	 */
	public function __construct() 
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
	}
	
	/**
	 * Get Merchr Hub Dashboard Stats.
	 *
	 * @return array
	 */
	public function getDashboardStats()
	{
		// Fetch totals
		$result = [];
		$result['total_products'] = (int) $this->wpdb->get_var("SELECT COUNT(`id`) FROM {$this->merchr_tables->products} WHERE `imported`='1'");
		$result['total_orders_success'] = (int) $this->wpdb->get_var("SELECT COUNT(`id`) FROM {$this->merchr_tables->orders} WHERE `processed`='1' AND `success`='1'");
		$result['total_orders_failed'] = (int) $this->wpdb->get_var("SELECT COUNT(`id`) FROM {$this->merchr_tables->orders} WHERE `processed`='1' AND `success`='0'");
		$result['total_orders_value'] = (int) $this->wpdb->get_var("SELECT SUM(`order_total`) FROM {$this->merchr_tables->orders} WHERE `processed`='1' AND `success`='1'");
		return $result;
	}
}
