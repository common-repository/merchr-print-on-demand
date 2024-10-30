<?php

namespace MerchrCust\Customisation;

use MerchrCust\Customisation\FontsStatic;
use MerchrCust\Customisation\PrintTypeStatic;

class CustomHelperStatic {
	
	public static function getTextColor($post_id){
		$color = get_post_meta($post_id, 'merchrcust_text_color', true);
		if (!empty($color)){
			return esc_attr($color);
		}
		return 'black';
	}
	
	public static function getPrintType($post_id){
		$type = get_post_meta($post_id, 'merchrcust_print_type', true);
		if (!empty($type)){
			return esc_attr($type);
		}
		return PrintTypeStatic::typeListDefault();
	}
	
	/*
	 * Parse the text that's shown in the input field on the product detail page
	 * Admin/manager can use some codes to return user information
	 */
	public static function parseDefaultText($string){
		// if there are no codes do nothing else
		if (strpos($string, '##') === false){
			return $string;
		}
		// find codes
		preg_match_all("`##(.*?)##`", $string, $matches);
		// parse each match
		if ($matches){
			// only do each once
			$toreplace = array_unique($matches[0]);
			foreach ($toreplace as $match){
				$string = self::replaceMatchedCode($string, $match);
			}
		}

		return esc_html($string);
	}
	
	/*
	 * Replace all instances of one code
	 */
	private static function replaceMatchedCode($string, $code){
		$user = wp_get_current_user();
		switch ($code){
			case '##user_first_name##':
			case '##user_last_name##':
				$bits = explode('_', str_replace('##', '', $code), 2);
				$replaced = self::replaceUserMetaCode($user, $bits[1]);
				break;
			case '##user_initials_with_periods##':
				$replaced = self::getUserInitials($user, true);
				break;
			case '##user_initials##':
				$replaced = self::getUserInitials($user, false);
				break;
			default: 
				return $string;
		}
		return esc_html(str_replace($code, $replaced, $string));
	}
	
	/*
	 * Return user meta for codes that start with ##user_
	 * NB $code_trail is already truncated, e.g. from ##user_first_name## to first_name
	 */
	private static function getUserInitials($user, $periods){
		$initials = [];
		if ($user->first_name){
			$initials[] = substr($user->first_name, 0, 1);
		}
		if ($user->last_name ){
			$initials[] = substr($user->last_name, 0, 1);
		}
		if ($periods){
			$join = '.';
		}else{
			$join = '';
		}
		return esc_html(implode($join, $initials) . $join);
	}
	/*
	 * Return user meta for codes that start with ##user_
	 * NB $code_trail is already truncated, e.g. from ##user_first_name## to first_name
	 */
	private static function replaceUserMetaCode($user, $code_trail){
		return get_user_meta( $user->ID, $code_trail, true);
	}
	
	/*
	 * return whichever text is not empty first
	 */
	public static function getWhicheverDefaultText($post_id = false){
		if ($post_id){
			$text_set_by_designer = trim(get_post_meta($post_id, 'merchrcust_editable_text', true));
			$productlevel_set_by_admin = trim(get_post_meta($post_id, CustomConfigStatic::productlevel_textfield_default, true));
		}else{
			$productlevel_set_by_admin = '';
		}
		
		$default_set_by_admin = CustomConfigStatic::getCustomText();
		$fallback_set_by_admin = CustomConfigStatic::getFallbackCustomText();
		$absolute_fallback = CustomConfigStatic::default_custom_fallback_text;
		
		// Designers specified text always takes priority
		if($text_set_by_designer !== '') {
			return esc_attr($text_set_by_designer);
		}
		
		$text = self::parseDefaultText($productlevel_set_by_admin);
		if (!empty($text)){
			return esc_attr($text);
		}
		
		$text = self::parseDefaultText($default_set_by_admin);
		if (!empty($text)){
			return esc_attr($text);
		}
		
		if (!empty($fallback_set_by_admin)){
			return esc_attr($fallback_set_by_admin);
		}
		
		return esc_attr($absolute_fallback);
		
	}
	
	public static function getWhicheverFontFamily($post_id){
		$product_level = get_post_meta( $post_id, 'merchrcust_font_family', true );
		$font_list = FontsStatic::fontList();
		$absolute_fallback = array_shift($font_list);
		
		if (!empty($product_level)){
			return esc_attr($product_level);
		}
		
		return esc_attr($absolute_fallback);
	}
	
	public static function getProducMerchrcustData($post_id)
	{
		$output = '';
		$width = get_post_meta( $post_id, 'merchrcust_width', true );
		if ($width){
			$placement_editor_type = get_post_meta( $post_id, 'merchrcust_placement_editor_type', true );
			$data = [];
			switch ($placement_editor_type){
				case 'none':
					return false;
				case 'full':
					$data += [
						'preview_coords'	    => esc_attr(json_decode(html_entity_decode(get_post_meta( $post_id, 'merchrcust_preview_coords', true )))),
						'personalisation_areas'	=> esc_attr(json_decode(html_entity_decode(get_post_meta( $post_id, 'merchrcust_personalisation_areas', true )))),
					];
					break;
				case 'simple':
					$data += [
						'offset_x'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_offset_x', true )),
						'offset_y'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_offset_y', true )),
						'width'			=> esc_attr($width),
						'height'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_height', true )),
						'rotate'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_rotate', true )) || 0,
					];
					break;
			}
			$color = get_post_meta( $post_id, 'merchrcust_text_color', true );
			if (empty($color)){
				$color = 'black';
			}
			$data += [
				'placement_editor_type'	=> esc_attr($placement_editor_type),
				'font_family'	        => esc_attr(self::getWhicheverFontFamily( $post_id )),
				'text_color'	        => esc_attr($color),
				'print_type'	        => esc_attr(CustomHelperStatic::getPrintType($post_id)),
				'line_breaks'	        => get_post_meta( $post_id, CustomConfigStatic::productlevel_textfield_line_breaks, true ) === 'yes'? true : false,
				'text'			        => esc_attr(esc_attr(self::getWhicheverDefaultText($post_id))),
				'post_id'		        => (int) $post_id,
				'variation_colors'	    => self::getProductVariationColors($post_id),
			];
			return json_encode($data);
		}else{
			return false;
		}
	}
	
	private static function getProductVariationColors($post_id)
	{
		$_product = wc_get_product($post_id);
		$variation_ids = $_product->get_children();
		$varcols = [];
		foreach ($variation_ids as $id){
			$color = CustomProductVariations::getTextColor($id);
			if (!empty($color)){
				$varcols[$id] = esc_attr($color);
			}
		}
		return $varcols;
	}
	
	public static function personaliseBlock()
	{
		return '';
		$text = self::getWhicheverDefaultText(false);
		$label = CustomConfigStatic::getCustomiseAllLabel();
		$type = 'page';
		if (is_product()){
			$type = 'product';
		}
		if (is_product_category()){
			$type = 'category';
		}
		if (is_home()){
			$type = 'website';
		}
		return '<div id="merchrcust_custom_block" class="col-full">
			<div id="merchrcust_custom_block_share">
				<label class="hidden-product-detail">' . esc_html($label) . '</label>
				<input class="hidden-product-detail js__merchrcustCustomTextInput" type="text" name="merchrcust_custom_text" value="' . esc_attr($text) . '">
				<a id="merchcust-share-button" class="js__merchrcustUpdateCustomLink js__toggleShareCustomLink button merchrcust-sharebutton"><i class="fas fa-share-alt"></i> Share</a>
			</div>
			<div id="merchrcust_custom_block_dropdown" style="display: none">
				<div class="d-flex" id="merchrcust_custom_block_title">
					<p>Share this ' . esc_html($type) . ' with personalisation &quot;<span class="js__merchrcustCustomTextText"></span>&quot;</p>
					<a class="float-right button clearfix js__toggleShareCustomLink">&times;</a>
				</div>
				<div class="d-flex" id="merchrcust_custom_block_links">
					<div id="merchrcust_custom_block_sharebuttons">
						<a class="js__copyToClipboardFromUrl button"><i class="fas fa-copy"></i> Copy URL</a>
						<a class="js__merchrcustShareCustomLink button" data-shareto="twitter"><i class="fab fa-twitter"></i></a>
						<a class="js__merchrcustShareCustomLink button" data-shareto="facebook"><i class="fab fa-facebook"></i></a>
					</div>
				</div>
			</div>
		</div>';
	}
	
}

