<?php

namespace MerchrCust\Customisation;

class CustomMain {

	public function __construct()
	{
		add_action( 'woocommerce_before_main_content', [ $this, 'personaliseBlock' ], 10 );
	}
	
	public static function personaliseBlock()
	{
		if ( is_product_category() || is_product_tag() ){
			echo CustomHelperStatic::personaliseBlock();
		}
	}
	

}
