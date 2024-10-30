<?php

namespace MerchrCust\Customisation;

use \WC_Order_Item_Product;
use MerchrCust\Customisation\CustomHelperStatic;
use MerchrCust\Customisation\FontsStatic;
/*
 * Add the fields set up in MerchrcustCustomProductDataTab to product detail
 * Show them in the cart
 * Show them on the order
 * 
 * https://wisdmlabs.com/blog/add-custom-data-woocommerce-order-2/
 */

class CustomProductDetail {
	
	const postkey_label_suffix = '__label';
	private $merch_json;
	
	public function __construct(){
		add_action( 'wp', [ $this, 'init' ], 11 ); 
		add_filter( 'body_class', [ $this, 'bodyClasses' ] );
		add_action( 'woocommerce_before_add_to_cart_quantity', [ $this, 'beforeAddToCartQty' ], 11 ); 
		//add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'beforeAddToCartForm' ], 11 ); 
		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'addCartItemData' ], 10, 3);
		add_filter( 'woocommerce_get_item_data', [ $this, 'showDataInCart' ], 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'addDataToOrder' ], 10, 4 );
		add_action( 'woocommerce_before_main_content', [ $this, 'personaliseBlock' ] );
		add_action( 'woocommerce_order_item_meta_end', [ $this, 'addCustomFieldsToOrderEmail' ], 10, 4 );
		add_filter( 'woocommerce_cart_item_thumbnail', [ $this, 'cartItemThumbnail' ], 10, 2 );
		add_filter( 'woocommerce_order_item_thumbnail', [ $this, 'orderItemThumbnail' ], 10, 2 );
		add_filter( 'woocommerce_email_order_items_args', [ $this, 'orderItemHideDefaultThumbnailFromOrderEmail'], 10, 1 );
		add_action( 'woocommerce_thankyou', [ $this, 'orderReceivedPage' ] );
	}
	
	public function init()
	{
		if (!is_product()){
			return;
		}
		global $post;
		$this->merch_json = CustomHelperStatic::getProducMerchrcustData($post->ID);
	}
	
	function bodyClasses( $classes )
	{
		if ($this->merch_json){
			$classes[] = esc_attr('merchr-has-customisation');
		}
		return $classes;
	}
	
	public function beforeAddToCartForm()
	{
		echo '<h2 class="merchrcust-title-personlise">' . __('Personalise', 'merchr') . '</h2>';
	}
	
	public function beforeAddToCartQty() {
		global $product;
		if (!is_product()){
			return;
		}
		$post_id = $product->get_id();
		$editor = get_post_meta($post_id, 'merchrcust_placement_editor_type', true);
		if (empty($editor) || $editor === 'none'){
			return;
		}
		
		// Show title
		echo '<h2 class="merchrcust-title-personlise">' . __('Personalise', 'merchr') . '</h2>';
		
		//$merch_json = CustomHelperStatic::getProducMerchrcustData($post_id);
		$linebreaks = get_post_meta($post_id, CustomConfigStatic::productlevel_textfield_line_breaks, true);
		$allow_images = get_post_meta($post_id, CustomConfigStatic::productlevel_allow_images, true);
		$text = CustomHelperStatic::getWhicheverDefaultText($post_id);
		$label = get_post_meta($post_id, CustomConfigStatic::productlevel_textfield_label, true);
		$text_color = CustomHelperStatic::getTextColor($post_id);
		$print_type = CustomHelperStatic::getPrintType($post_id);
		$font_family = CustomHelperStatic::getWhicheverFontFamily($post_id);
		if (empty($label)){
			$label = CustomConfigStatic::getCustomLabel();
		}
		?>
		<div id="merchrcust_data" data-merchrcust='<?php echo esc_attr($this->merch_json); ?>'></div>
		<div id="merchrcust-fields-loading">
			Loading customisation...
		</div>
		<div id="merchrcust-fields-wrapper" style="display: none;">
			<div id="merchrcust-customisation-select">
				<label class="label-title">
					Customisation:
				</label>
				<label>
					<input type="radio" id="merchrcust-customisation__none" name="merchrcust_customisation_type" value="none" />
					None
				</label>
				<label>
					<input type="radio" id="merchrcust-customisation__text" checked name="merchrcust_customisation_type" value="text" />
					Text
				</label>
		<?php
		if ($allow_images){
		?>
				<label>
					<input type="radio" id="merchrcust-customisation__image" name="merchrcust_customisation_type" value="image" />
					Image
				</label>
		<?php
		}
		?>
			</div>

			<div class="merchrcust-fields">
				<?php /* Fields to allow artworking to know what to do // Remember to copy any new hidden fields to cartItemDataKeys */ ?>

				<?php /* end of artworking fields */ ?>

				<input hidden type="text" id="merchrcust_custom_image" name="merchrcust_custom_image" value="" />
				<input hidden type="text" id="merchrcust_custom_image_whiteremove" name="merchrcust_custom_image_whiteremove" value="" />
				<textarea hidden name="merchrcust_thumbnail" id="merchrcust_thumbnail_field"></textarea>

				<div class="merchrcust-field-wrapper merchrcust-fields-customisation merchrcust-fields-customisation__text">
					<div class="merchrcust-field-label">
						<label class="label"><?php echo esc_html($label); ?></label>
					</div>
					<div class="merchrcust-field-value">

				<?php
					if ($linebreaks === 'yes'){
						?><textarea class="js__merchrcustCustomTextInput" name="merchrcust_custom_text"><?php echo esc_textarea($text); ?></textarea><?php
					}else{
						?><input class="js__merchrcustCustomTextInput" type="text" name="merchrcust_custom_text" value="<?php echo esc_textarea($text); ?>" /><?php
					}
				?>
					</div>
				</div><!-- /.merchrcust-field-wrapper -->

				<div class="merchrcust-field-wrapper merchrcust-fields-customisation merchrcust-fields-customisation__text">
					<div class="merchrcust-field-label">
						<label class="label">Font</label>
					</div>
					<div class="merchrcust-field-value">
						<?php echo FontsStatic::fontListSelect($font_family); ?>
					</div>
				</div><!-- /.merchrcust-field-wrapper -->
				<?php if ($print_type === 'single') { ?>
					<!--<input type="hidden" name="merchrcust_text_color<?php echo self::postkey_label_suffix; ?>" value="Text Color" />-->
					<input type="hidden" name="merchrcust_text_color" value="<?php echo esc_url($text_color); ?>" />

					<div class="merchrcust-field-wrapper merchrcust-fields-customisation merchrcust-fields-customisation__text">
						<div class="merchrcust-field-label">
							<label class="label">Text Colour</label>
						</div>
						<div class="merchrcust-field-value">
							<?php echo TextColorsStatic::colorListRadio($text_color); ?>
						</div>
					</div><!-- /.merchrcust-field-wrapper -->
					
					<!--[PPP-192]-->
					<?php if ($allow_images){ ?>
						<div class="merchrcust-field-wrapper merchrcust-fields-customisation merchrcust-fields-customisation__image">
							<div class="merchrcust-field-label">
								<label class="label">Image</label>
							</div>

							<div class="merchrcust-field-value">
								<!--<input id="merchrcust_image_to_place" type="text" value="https://nigelhill.dev.hub.merchr.co.uk/testimages/intel.jpg" />
								<a href="#" class="button" id="merchrcust-image-place-button">Place Image</a>-->
								<a href="#" class="button" id="merchrcust-image-modal-button">My Images...</a>
								<a href="#" class="button" id="merchrcust-remove-white-button" style="display: none;">Remove white from image...</a>
							</div>
							
							<div id="merchrcust-white-removal-modal" class="merchrcust-modal" style="display: none;">
								<div class="row merchrcust-wrm-options">
									<div class="col">
										<div id="merchrcust-remove-white-none-image" class="merchrcust-rwm-image-wrapper img-checkerboard"></div>
										<button class="button" id="merchrcust-remove-white-none">Remove none</button>
									</div>
									<div class="col">
										<div id="merchrcust-remove-white-edges-image" class="merchrcust-rwm-image-wrapper img-checkerboard"></div>
										<button class="button" id="merchrcust-remove-white-edges">Remove from edges</button>
									</div>
									<div class="col">
										<div id="merchrcust-remove-white-all-image" class="merchrcust-rwm-image-wrapper img-checkerboard"></div>
										<button class="button" id="merchrcust-remove-white-all">Remove all white</button>
									</div>
								</div>
							</div><!--/#merchrcust-white-removal-modal-->

						</div><!-- /.merchrcust-field-wrapper -->
					<?php } // endif ?>
				<?php } ?>
				<!--<div class="merchrcust-field-wrapper">
					<div class="merchrcust-field-label">
						<label class="label">Qty</label>
					</div>
					<div class="merchrcust-field-value js__merchrProductQtyInputHere">
					</div>
				</div><!-- /.merchrcust-field-wrapper -->
			</div><!-- /.merchrcust-fields -->
		</div><!-- /.merchrcust-fields-wrapper -->
		<?php	
	}
	
	/*
	 * These are the keys that data is entered into in the cart
	 * Each has a corresponding label, which is the key suffixed with self::postkey_label_suffix
	 * Remember to copy any new hidden fields to beforeAddToCartQty
	 */
	public static function cartItemDataKeys($include = true){
		$keys = [];
		
		$keys['always'] = [
			'merchrcust_customisation_type',
			'merchrcust_thumbnail',
		];
		
		$keys['text'] = [
			'merchrcust_custom_text',
			'merchrcust_text_color',
			'merchrcust_font_family',
		];
		
		$keys['image'] = [
			'merchrcust_custom_image',
			'merchrcust_custom_image_whiteremove',
		];
		
		if (!$include){
			return $keys['always'];
		}
		if ($include === true){
			$return = [];
			foreach ($keys as $array){
				$return = array_merge($return, $array);
			}
		}else{
			$return = $keys['always'];
			foreach ($include as $key){
				$return = array_merge($return, $keys[$key]);
			}
		}
		
		return $return;
		
	}
	
	public static function getCartItemDataLabel($key){
		$labels = [
			'merchrcust_customisation_type'			=> esc_html('Customisation Type', 'merchr'),
			'merchrcust_text_color'					=> esc_html('Text Colour', 'merchr'),
			'merchrcust_font_family'				=> esc_html('Font', 'merchr'),
			'merchrcust_custom_text'				=> esc_html('Custom Text', 'merchr'),
			'merchrcust_custom_image'				=> esc_html('Image', 'merchr'),
			'merchrcust_custom_image_whiteremove'	=> esc_html('Image White Removal', 'merchr'),
			'merchrcust_thumbnail'					=> esc_html('#hidden#', 'merchr'),
		];
		if (array_key_exists($key, $labels)){
			return $labels[$key];
		}else{
			return '[' . $key . ']';
		}
		
	}
	
	/*
	 * Add custom field to the cart row.
	 */
	public function addCartItemData($cart_item_data, $product_id, $variation_id){
		if(isset($_REQUEST['merchrcust_customisation_type']) && $_REQUEST['merchrcust_customisation_type'] !== 'none'){
			switch ($_REQUEST['merchrcust_customisation_type']){
				case 'image':
					$keys = self::cartItemDataKeys(['image']);
					break;
				case 'text':
					$keys = self::cartItemDataKeys(['text']);
					break;
			}
		}else{
			$keys = self::cartItemDataKeys(false); // just get the basic ones
		}
		foreach ($keys as $labelkey){
			$cart_item_data = self::addCartItemDataItem($labelkey, $cart_item_data, $product_id, $variation_id);
		}
		return $cart_item_data;
	}

	private static function addCartItemDataItem($key, $cart_item_data, $product_id, $variation_id){
		if (isset($_REQUEST[$key])){
			$labelkey = $key . self::postkey_label_suffix;
			if ($key === 'merchrcust_thumbnail' && $_REQUEST[$key] !== '' ){
				// save to disk and replace with URL
				$string = self::saveImage($_REQUEST[$key], $cart_item_data, $product_id, $variation_id);
				$cart_item_data[$key] = sanitize_text_field($string);
			}else{
				$cart_item_data[$key] = sanitize_text_field($_REQUEST[$key]);
			}
			//$cart_item_data[$labelkey] = sanitize_text_field($_REQUEST[$labelkey]);
		}
		return $cart_item_data;
	}
	
	private static function saveImage($base64_img, $cart_item_data, $product_id, $variation_id){
		list($decoded, $ext) = self::decodeBase64Image($base64_img);
		if (!$decoded) {
            return '';
        }
        
        $title = implode('-', [
			'order_item_preview',
			$product_id, 
			$variation_id,
			strtotime('now'),
		]);
		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		$filename        = $title . '.' . $ext;
		$file_type       = 'image/' . $ext;

		// Save the image in the uploads directory.
		$upload_file = file_put_contents( $upload_path . $filename, $decoded );

		$attachment = array(
			'post_mime_type' => sanitize_text_field($file_type),
			'post_title'     => sanitize_text_field(preg_replace( '/\.[^.]+$/', '', basename( $filename ) )),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => sanitize_url($upload_dir['url'] . '/' . basename( $filename ))
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $filename );
		return $attachment['guid'];
	}
	
	private static function decodeBase64Image($img){
		$exts = [
			'png',
			'jpeg',
			'jpg',
		];
		
		foreach ($exts as $ext){
			$find = 'data:image/'. $ext;
			if (str_contains($img, $find)){
				$img     = str_replace( $find . ';base64,', '', $img );
				$img     = str_replace( ' ', '+', $img );
				$decoded = base64_decode( $img, true );
				return [
					$decoded,
					$ext,
				];
			}
		}
	}
	
	/* This is what is displayed in the cart
	*/
	public function showDataInCart($item_data, $cart_item){
		foreach (self::cartItemDataKeys(true) as $key){
			if(array_key_exists($key, $cart_item)){
				$label = self::getCartItemDataLabel($key);
				if ($label === '#hidden#'){
					continue;
				}
				$item_data[] = array(
					'key'   => $label,
					'value' => $cart_item[$key]
				);
			}
			
		}

		return $item_data;
	}
	
	public function cartItemThumbnail($product_get_image, $item)
	{
		if (array_key_exists('merchrcust_thumbnail', $item) && !empty($item['merchrcust_thumbnail'])){
			return '<img class="merchr-cart-product-thumbnail" src="' . esc_attr($item['merchrcust_thumbnail']) . '" />';
		}
		return $product_get_image; 
	}
	
	/*
	 * Add values here to add data to the order
	 */
	public function addDataToOrder($item, $cart_item_key, $values, $order)
	{
		$keys = self::cartItemDataKeys(true);
		foreach ($keys as $key){
			$ukey = '_' . $key;
			if (array_key_exists($key, $values)){
				$item->add_meta_data($ukey, $values[$key]);
			}
		}
	}
	
	public function addCustomFieldsToOrderEmail($item_id, $item, $order, $plain_text)
	{
		if( ! (is_admin() || is_wc_endpoint_url() )) {
			echo self::getItemThumbnail($item);
			echo self::getCustomFieldsHtml($item_id);
		}
	}
	
	public function orderItemHideDefaultThumbnailFromOrderEmail($args)
	{
		$args['show_image'] = false;
		return $args;
	}


	public static function getCustomFieldsHtml($item_id, $show_empty = false)
	{
		$html = '';
		$keys = self::cartItemDataKeys(true);
		foreach ($keys as $key){
			$ukey = '_' . $key;
			$label = self::getCartItemDataLabel($key);
			if ($label === '#hidden#'){
				continue;
			}
			$value = wc_get_order_item_meta( $item_id, $ukey );
			if (empty($value) && !$show_empty){
				continue;
			}
			$html .= '<div>' . esc_html($label) . ': '. esc_html($value) .'</div>';
		}
		return $html;
	}

	public static function getItemThumbnail($id_or_object, $size = null)
	{
		if (is_int($id_or_object)){
			$item = new WC_Order_Item_Product($id_or_object);
		}else{
			$item = $id_or_object;
		}
		$thumb = esc_attr(wc_get_order_item_meta( $item->get_id(), '_merchrcust_thumbnail' ));
		if (!empty($thumb)){
			$style = '';
			if ($size){
				$size = esc_attr($size);
                $style = "width: {$size}px; height: {$size}px; ";
			}
			return "<img class='attachment-woocommerce_thumbnail size-woocommerce_thumbnail' src='{$thumb}' style='{$style}' />";
		}
		$product_id = $item['product_id'];
		$_product = wc_get_product( $product_id );
		return $_product->get_image();
	}
	
	public function orderItemThumbnail($thumbnail, $item)
	{
		return self::getItemThumbnail($item);
	}

	public static function personaliseBlock()
	{
		if ( is_product() ){
			echo CustomHelperStatic::personaliseBlock();
		}
	}
	
	public function orderReceivedPage($order_id)
	{
		$order = wc_get_order( $order_id );
		?> 
		<h2 class="woocommerce-order-details__title">Products</h2>
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<?php
		// Loop through order items
		foreach ( $order->get_items() as $item ){
			?>
			<tbody>
				<tr class="woocommerce-table__line-item order_item">
					<td>
						<?php echo self::getItemThumbnail($item); ?>
					</td>
					<td>
						<?php 
						echo '<p><strong>' . esc_html($item['name']) . '</strong> x ' . esc_html($item['qty']) . '</p>';
						echo self::getCustomFieldsHtml($item->get_id());
						?>
					</td>
				</tr>
			</tbody>
			<?php
		}
		?>
		</table>
		<?php
		
	}

}
