<?php

namespace MerchrCust\Customisation;

class CustomProductVariations {

	public function __construct() {
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'variationSettingsFields' ], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'saveVariationSettingsFields' ], 10, 2 );
		add_filter( 'woocommerce_available_variation', [ $this, 'loadVariationSettingsFields' ] );
	}
	
	public function variationSettingsFields($loop, $variation_data, $variation)
	{
		woocommerce_wp_text_input(
			[
				'id'            => "merchrcust_variation_text_color_{$loop}",
				'name'          => "merchrcust_variation_text_color[{$loop}]",
				'value'         => esc_attr(get_post_meta( $variation->ID, 'merchrcust_variation_text_color', true )),
				'label'         => esc_html__( 'Customisation Text Color', 'merchr' ),
				'desc_tip'      => true,
				//'description'   => esc_html__( 'Some description.', 'merchr' ),
				'wrapper_class' => 'form-row form-row-full',
			]
		);
		
	}

	public function saveVariationSettingsFields($variation_id, $loop)
	{
		$variation_id = (int) $variation_id;
        $text_field = sanitize_text_field( $_POST['merchrcust_variation_text_color'][ $loop ] );
		if (isset($text_field) && !empty($text_field)) {
			update_post_meta( $variation_id, 'merchrcust_variation_text_color', $text_field );
		}else{
			delete_post_meta( $variation_id, 'merchrcust_variation_text_color' );
		}
	}

	public function loadVariationSettingsFields($variation)
	{
		$variation['merchrcust_variation_text_color'] = self::getTextColor( $variation[ 'variation_id' ] );
		return $variation;
	}
	
	public static function getTextColor($variation_id){
		$variation_id = (int) $variation_id;
        return esc_attr(get_post_meta( $variation_id, 'merchrcust_variation_text_color', true ));
	}

}
