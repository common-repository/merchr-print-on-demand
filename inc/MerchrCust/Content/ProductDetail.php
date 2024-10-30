<?php

namespace MerchrCust\Content;

/*
 * Add the fields set up in MerchrcustExtraProductDataTab to product detail
 * Show them in the cart
 * Show them on the order
 * 
 * https://wisdmlabs.com/blog/add-custom-data-woocommerce-order-2/
 */

class ProductDetail {
	
	public function __construct()
	{
		add_action( 'woocommerce_after_single_product_summary' , [$this, 'afterProductDetail']);
		// add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'leadTime' ], 11 ); 
	}
	
	public function afterProductDetail(){
		?><div class="merchrcust_after_product_detail">
			<?php do_action( 'merchrcust_after_product_detail' ); ?>
		</div><!--/.merchrcust_after_product_detail-->
		<?php
	}
	
}
