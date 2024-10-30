<?php

/*
 * Class for modifying other plugins that deal with invoices, packing slips, etc
 */

namespace MerchrCust\Customisation;
use MerchrCust\Customisation\CustomProductDetail;

class CustomInvoices {

	public function __construct() {
		add_action('wpo_wcpdf_after_item_meta', [ $this, 'wpoAddExtraFields' ], 10, 3);

		// webtoffee - add product meta to invoice
		add_filter('wf_pklist_add_product_meta', [ $this, 'wfPackingListAddExtraFields' ], 10, 5);
		// webtoffee - add product meta to packing slip
		add_filter('wf_pklist_add_package_product_meta', [ $this, 'wfPackingListAddPackageProductMeta' ], 10, 5);

		// webtoffee - change thumbnail in packing slips
		add_filter('wf_pklist_alter_package_product_table_columns', [ $this, 'wfChangeThumbnailPackingSlip' ], 10, 6); //Works with Packing List, Shipping Label and Delivery note only
		add_filter('wf_pklist_alter_product_table_columns', [ $this, 'wfChangeThumbnailInvoice' ], 10, 6); // Works with Invoice and Dispatch label only
	}
	
	/*
	 * WooCommerce PDF Invoices & Packing Slips by WP Overnight
	 */
	public function wpoAddExtraFields($template_type, $item, $order)
	{
		echo $this->extraOrderItemDetails($item['item']);
	}	

	/*
	 * WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels by WebToffee
	 */
	public function wfPackingListAddExtraFields($addional_product_meta, $template_type, $_product, $order_item, $order)
	{
		/* add new field to the list */
		$addional_product_meta .= $this->extraOrderItemDetails($order_item);
		return $addional_product_meta;
	}
	
	public function wfPackingListAddPackageProductMeta($item_name, $template_type, $_product, $item, $order)
	{
		return $this->extraOrderItemDetails($item['order_item_id'], false);
	}
	
	private function extraOrderItemDetails($id_or_object, $include_thumbnail = false)
	{
		if (is_int($id_or_object)){
			$item_id = $id_or_object;
		}else{
			/* WC_Order_Item_Product */ 
			$item_id = $id_or_object->get_id();
		}
		$keys = CustomProductDetail::cartItemDataKeys(true);
		$output = "<div><p><b><u>Customisation</u></b></p><div style='margin-left: 20px;'>";
		if ($include_thumbnail){
			$output .= esc_url(CustomProductDetail::getItemThumbnail($item_id));
		}
		$output .= CustomProductDetail::getCustomFieldsHtml($item_id);
		$output .= "</div></div>";
		return $output;	
	}

	//Works with Packing List, Shipping Label and Delivery note only
	public function wfChangeThumbnailPackingSlip($product_row_columns, $template_type, $_product, $item, $order)
	{
		$product_row_columns['image'] = CustomProductDetail::getItemThumbnail($item['order_item_id']);
		return $product_row_columns;
	}
	
	// Works with Invoice and Dispatch label only
	public function wfChangeThumbnailInvoice($product_row_columns, $template_type, $_product, $order_item, $order)
	{
		$product_row_columns['image'] = CustomProductDetail::getItemThumbnail($order_item->get_id(), 100);
		return $product_row_columns;
	}
	
}
