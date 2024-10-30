<?php

namespace MerchrCust\Content;

class ProductDataTab{
	
    public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'addDataTab' ] );
		add_action( 'admin_head', [ $this, 'addStyle' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'addDataFields' ] );
		add_action( 'woocommerce_process_product_meta_simple', [ $this, 'saveData' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ $this, 'saveData' ] );		
	}

	public function addDataTab( $product_data_tabs ) {
		$product_data_tabs['ice-extra-tab'] = array(
			'label' => esc_html__( 'Information', 'merchr' ),
			'target' => 'ice_extra_product_data',
			'priority' => 10,
			//'class'		 => array( 'show_if_simple' ),
		);
		return $product_data_tabs;
	}

	/** CSS To Add Custom tab Icon 
	 * https://github.com/woocommerce/woocommerce-icons/blob/master/demo.html
	 */
	public function addStyle() {
		?>
		<style>
		#woocommerce-product-data ul.wc-tabs li.ice-extra-tab_options a:before { font-family: WooCommerce; content: '\e600'; }
		</style>
		<?php 
	}

	// functions you can call to output text boxes, select boxes, etc.

	public function addDataFields() {
		global $post;
		$post_id = get_the_ID();
		// Note: id needs to match target parameter set in add_addDataTab
		?> 
		<div id="ice_extra_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
		<?php

		woocommerce_wp_text_input(
			array(
				'id' => 'merchrcust_leadtime',
				'label' => esc_html__( 'Leadtime', 'merchr' ),
				'value'	=> esc_attr(get_post_meta( $post_id, 'merchrcust_leadtime', true )),
			)
		);

		woocommerce_wp_radio(
			array(
				'id' => 'merchrcust_leadtime_units',
				'label' => esc_html__( 'Units', 'merchr' ),
				'options' => ContentConfigStatic::leadtime_units,
				'value'	=> esc_attr(get_post_meta( $post_id, 'merchrcust_leadtime_units', true )),
			)
		);
		?>
			</div>
		</div>
		<?php
	}

	// Hook callback function to save extra fields data
	public function saveData($post_id) {
        $post_id = (int) $post_id;
        
		$texts = [
			'merchrcust_leadtime',
			'merchrcust_leadtime_units',
		];

		$checkboxes = [
		];

		foreach ($texts as $field){
			if (isset($_POST[$field])){
				update_post_meta($post_id, $field, sanitize_text_field(($_POST[$field])));
			}else{
				delete_post_meta($post_id, $field);
			}
		}

		foreach ($checkboxes as $field){
			if (isset($_POST[$field])){
				update_post_meta($post_id, $field, 'yes');
			}else{
				delete_post_meta($post_id, $field);
			}
		}
	}
}
