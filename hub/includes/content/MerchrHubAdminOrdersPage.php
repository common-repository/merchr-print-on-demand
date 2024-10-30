<?php
/**
 * Merchr Hub Orders Page.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminOrdersPage extends MerchrHubContent
{
	use MerchrHubDatabaseInteraction;
	
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
	}
	
	/**
	 * Return Orders.
	 */
	public function returnOrders()
	{
		$content = '';
		
		// Fetch order details
		$orders = $this->wpdb->get_results(
			"SELECT `id`, `order_id`, `order_total`, `merchr_order_id`, `processed`, `success`, `failed`, `retried`, `cancelled`, `completed`, 
			 `status`, `notes`, `date_created` 
			 FROM `{$this->merchr_tables->orders}` 
			 ORDER BY `failed` ASC, `processed` ASC, `date_created` DESC"
		);
		
		// Process if we have orders
		if($orders !== null) {
			$order_rows = [];
			
			// Get date and time formats
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			
			// Get currency symbol
			$currency_symbol = get_woocommerce_currency_symbol();
			
			// Set order resend endpoint and nonce
			$endpoint = admin_url('admin-ajax.php?action=merchr_resend_order');
			$nonce = wp_create_nonce('merchr_resend_order');
			
			// Build table header/footer
			$table_header_footer = [];
			$table_header_footer[] = '<th>' . __('Your Order ID', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Merchr Order ID', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Status', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Date', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Total', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Notes', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('View', 'merchr') . '</th>';
			$table_header_footer[] = '<th>' . __('Actions', 'merchr') . '</th>';
			$table_header_footer = implode("\n", $table_header_footer);
			
			foreach($orders as $order) {
				$id = (int) $order->id;
				$order_id = (int) $order->order_id;
				$order_total = (float) $order->order_total;
				$merchr_order_id = (int) $order->merchr_order_id;
				$processed = (int) $order->processed;
				$success = (int) $order->success;
				$failed = (int) $order->failed;
				$retried = (int) $order->retried;
				$cancelled = (int) $order->cancelled;
				$completed = (int) $order->completed;
				$status = trim($order->status);
				$notes = trim($order->notes);
				$date_created = trim($order->date_created);
				
				// Format date
				if($date_created !== '') {
					$date_created = date("{$date_format} {$time_format}", strtotime($date_created));
				}
				
				// We need to check if order failed to push to the hub or retried is set
				// and adjust the status of this order
				$action = '';
				if($processed === 1 && $failed === 1 && $retried === 0) {
					$status = 'failed';
					
					// Create action button
					$action = '<button class="merchr-hub-btn merchr-hub-btn-sml merchr-hub-btn-green merchr-hub-resend-btn" data-id="' . $id . '" data-nonce="' . $nonce . '" data-endpoint="' . $endpoint . '">' . __('Resend Order', 'merchr') . '</button>';
				}
				if($processed === 0 && $failed === 0 && $retried === 1) {
					$status = 'queued';
				}
				
				// Format Status
				if($status !== '') {
					$display_status = esc_html(ucfirst($status));
				} else {
					$status = 'new';
					$display_status = esc_html__('New', 'merchr');
				}
				$status = esc_attr($status);
				
				// Format Notes
				if($status !== '') {
					$notes = esc_html(nl2br($notes));
				}
				
				// Create view order link
				$link = admin_url("post.php?post={$order_id}&action=edit");
				$view_btn = '<a class="merchr-hub-btn merchr-hub-btn-sml" href="' . $link . '">' . __('View Order', 'merchr') . '</a>';
				
				// Build row
				$order_rows[] = '<tr id="merchr-row-' . $id . '">';
				$order_rows[] = '<td>' . $order_id . '</td>';
				$order_rows[] = '<td>' . $merchr_order_id . '</td>';
				$order_rows[] = '<td class="merchr-row-status merchr-' . $status . '">' . $display_status . '</td>';
				$order_rows[] = '<td>' . esc_html($date_created) . '</td>';
				$order_rows[] = '<td>' . $currency_symbol . $order_total . '</td>';
				$order_rows[] = '<td class="merchr-notes">' . $notes . '</td>';
				$order_rows[] = '<td>' . $view_btn . '</td>';
				$order_rows[] = '<td>' . $action . '</td>';
				$order_rows[] = '</tr>';
			}
			
			// Prepare final content
			$content = MerchrHubHelpersTemplates::parseStringReplacements(
				MerchrHubHelpersTemplates::fetchTemplateContents('wordpress_table.tpl', $this->templates_path), 
				[
					'table_id'     => 'merchr-orders-table',
					'table_header' => $table_header_footer,
					'table_rows'   => implode("\n", $order_rows),
					'table_footer' => $table_header_footer
				]
			);
		} else {
			$content = '<h2>' . __('You have not received any orders... Yet!', 'merchr') . '</h2>';
		}
		
		return $content;
	}
}
