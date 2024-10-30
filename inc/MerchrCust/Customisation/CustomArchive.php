<?php

namespace MerchrCust\Customisation;

use MerchrCust\Customisation\CustomHelperStatic;

class CustomArchive {
	
	public function __construct()
	{
	}
	
	public static function getProductThumbnail( $size = 'shop_catalog' )
	{
		global $post, $woocommerce;
		$post_id = get_the_ID();
		$merch_json = CustomHelperStatic::getProducMerchrcustData($post_id);
		if ($merch_json){
			$output = '<div class="js__merchrAddText merch-image-has-canvas" data-merchrcust=\'' . $merch_json . '\'>';
		}else{
			$output = '<div>';
		}
		if ( has_post_thumbnail() ) {               
			$output .= get_the_post_thumbnail( $post->ID, $size );
		} else {
			 $output .= wc_placeholder_img( $size );
		}                       
		$output .= '</div>';
		return $output;
	}
	
}
