<?php

namespace MerchrCust\Customisation;

use MerchrCust\Customisation\CustomHelperStatic;
use MerchrCust\Customisation\FontsStatic;
use MerchrCust\Customisation\PrintTypeStatic;

class CustomProductDataTab{
	
    public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'addDataTab' ] );
		add_action( 'admin_head', [ $this, 'addStyle' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'addDataFields' ] );
		add_action( 'woocommerce_process_product_meta_simple', [ $this, 'saveData' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ $this, 'saveData' ] );		
	}

	public function addDataTab( $product_data_tabs ) {
		$product_data_tabs['merchrcust-tab'] = array(
			'label' => __( 'Customisation', 'merchr' ),
			'target' => 'merchrcust_custom_product_data',
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
		#woocommerce-product-data ul.wc-tabs li.merchrcust-tab_options a:before { font-family: WooCommerce; content: '\e603'; }
		</style>
		<?php 
	}

	// functions you can call to output text boxes, select boxes, etc.

	public function addDataFields() {
		$post_id = get_the_ID();
		// Note: id needs to match target parameter set in add_addDataTab
		?> 
		<div id="merchrcust_custom_product_data" class="panel woocommerce_options_panel">
			<div class="options_group">
				<div>
					<?php self::dataFieldsCustomAreaEditorSwitch($post_id); ?>
				</div>
				<div id="merchrcust-editor">
					<div class="d-flex-xxl-up">
						<div style="flex-grow: 1;">
							<div>
								<div class="js__merchrUsesSimpleEditor">
									<?php self::dataFieldsSize($post_id); ?>
									<?php self::dataFieldsPrintType($post_id); ?>
								</div>
								<div class="js__merchrUsesFullEditor">
									<p>Full editor is only available while in the hub.</p>
									<?php self::dataFieldsFullEditor($post_id); ?>
								</div>
							</div>
							<div>
								<hr />
                                <div style="display:none;">
                                    <?php self::dataFieldsFont($post_id); ?>
                                </div>
							</div>
						</div>
						<div id="merchrcust-editproduct-preview-wrapper">
							<div id="merchrcust-editproduct-preview"></div>
						</div>
					</div>
					<hr />
					<?php self::dataFieldsOptions($post_id); ?>
				</div><!--/.options_group-->
			</div>
		</div><!--/#merchrcust_custom_product_data-->
		<?php
	}

	private function dataFieldsCustomAreaEditorSwitch($post_id){
		$id = 'merchrcust_placement_editor_type';
		woocommerce_wp_select(
			array(
				'id'		=> $id,
				'label'		=> __( 'Placement Editor Type', 'merchr' ),
				'value'		=> esc_attr(get_post_meta( $post_id, $id, true )),
				'options'	=> [
					'none'		=> esc_attr__('None', 'merchr'),
					'simple'	=> esc_attr__('Simple', 'merchr'),
					//'full'	=> esc_attr__('Full', 'merchr'),
				],
				'wrapper_class' => 'js__merchrPlacementEditorType',
			)
		);
	}
	
	private function dataFieldsFullEditor($post_id){
		?>
		<div class="d-flex-xxl-up" style="display: none;">
		<?php
		$key = 'merchrcust_design_ratio_w';
		woocommerce_wp_text_input([
			'id'		=> $key,
			'label'		=> __( 'Design Ratio Width', 'merchr' ),
			'value'		=> esc_attr(get_post_meta( $post_id, $key, true )),
			'type'		=> 'number', 
			'custom_attributes' => [
				'step' 	=> '0.1',
				'min'	=> '0',
				'max'	=> '100',
			],
			'wrapper_class' => 'input_type_number',
		]);
		
		$key = 'merchrcust_design_ratio_h';
		woocommerce_wp_text_input([
			'id'		=> $key,
			'label'		=> esc_html__( 'Design Ratio Height', 'merchr' ),
			'value'		=> esc_attr(get_post_meta( $post_id, $key, true )),
			'type'		=> 'number', 
			'custom_attributes' => [
				'step' 	=> '0.1',
				'min'	=> '0',
				'max'	=> '100',
			],
			'wrapper_class' => 'input_type_number',
		]);
		
		?>
		</div>
		<div class="d-flex-xxl-up" style="display: none;">
		<?php
		$key = 'merchrcust_print_area_w';
		woocommerce_wp_text_input([
			'id'		=> $key,
			'label'		=> esc_html__( 'Print Area Width', 'merchr' ),
			'value'		=> esc_attr(get_post_meta( $post_id, $key, true )),
			'type'		=> 'number', 
			'custom_attributes' => [
				'step' 	=> '0.1',
				'min'	=> '0',
				'max'	=> '100',
			],
			'wrapper_class' => 'input_type_number',
		]);
		
		$key = 'merchrcust_print_area_h';
		woocommerce_wp_text_input([
			'id'		=> $key,
			'label'		=> esc_html__( 'Print Area Height', 'merchr' ),
			'value'		=> esc_attr(get_post_meta( $post_id, $key, true )),
			'type'		=> 'number', 
			'custom_attributes' => [
				'step' 	=> '0.1',
				'min'	=> '0',
				'max'	=> '100',
			],
			'wrapper_class' => 'input_type_number',
		]);
		
		?>
		</div><!--/.d-flex-->
		<div style="display: none;">
		<?php
		$key = 'merchrcust_print_area_shape';
		woocommerce_wp_select([
			'id'		=> $key,
			'label'		=> esc_html__( 'Print Area Shape', 'merchr' ),
			'value'		=> esc_attr(get_post_meta( $post_id, $key, true )),
			'options'	=> [
				'rect'		=> 'Square / Rectangle',
				'circ'		=> 'Circle / Ellipse',
			],
		]);
		
		$key = 'merchrcust_preview_coords';
		woocommerce_wp_textarea_input([
			'id'		=> esc_attr($key),
			'label'		=> esc_html__( 'Preview Coordinates', 'merchr' ),
			'value'		=> html_entity_decode(get_post_meta( $post_id, $key, true )),
		]);
		
		$key = 'merchrcust_personalisation_areas';
		woocommerce_wp_textarea_input([
			'id'		=> esc_attr($key),
			'label'		=> esc_html__( 'Personalisation Areas', 'merchr' ),
			'value'		=> html_entity_decode(get_post_meta( $post_id, $key, true )),
		]);
		
		?>
		</div><!--/.d-flex-->
		<?php
	}
	
	private function dataFieldsSize($post_id){
		?>
		<div>
			<?php
			woocommerce_wp_text_input([
				'id'		=> 'merchrcust_offset_x',
				'label'		=> esc_html__( 'Offset Left', 'merchr' ),
				'value'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_offset_x', true )),
				'type'		=> 'number', 
				'custom_attributes' => [
					'step' 	=> 'any',
					'min'	=> '-100',
					'max'	=> 100,
				],
				'wrapper_class' => 'input_type_number',
			]);

			woocommerce_wp_text_input([
				'id'		=> 'merchrcust_offset_y',
				'label'		=> esc_html__( 'Offset Top', 'merchr' ),
				'value'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_offset_y', true )),
				'type'		=> 'number', 
				'custom_attributes' => [
					'step' 	=> 'any',
					'min'	=> '-100',
					'max'	=> 100,
				],
				'wrapper_class' => 'input_type_number',
			]);

			woocommerce_wp_text_input([
				'id'		=> 'merchrcust_width',
				'label'		=> esc_html__( 'Width', 'merchr' ),
				'value'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_width', true )),
				'type'		=> 'number', 
				'custom_attributes' => [
					'step' 	=> 'any',
					'min'	=> '0',
					'max'	=> 100,
				],
				'wrapper_class' => 'input_type_number',
			]);

			woocommerce_wp_text_input([
				'id'		=> 'merchrcust_height',
				'label'		=> esc_html__( 'Height', 'merchr' ),
				'value'		=> esc_attr(get_post_meta( $post_id, 'merchrcust_height', true )),
				'type'		=> 'number', 
				'custom_attributes' => [
					'step' 	=> 'any',
					'min'	=> '0',
					'max'	=> 100,
				],
				'wrapper_class' => 'input_type_number',
			]);
            /*
			woocommerce_wp_text_input([
				'id'		=> 'merchrcust_rotate',
				'label'		=> esc_html__( 'Rotate', 'merchr' ),
				'value'		=> get_post_meta( $post_id, 'merchrcust_rotate', true ),
				'type'		=> 'number', 
				'custom_attributes' => [
					'step' 	=> '1',
					'min'	=> '-180',
					'max'	=> 180,
				],
				'wrapper_class' => 'input_type_number',
			]);
            */
//			woocommerce_wp_text_input([
//				'id'		=> 'merchrcust_canvas_size',
//				'label'		=> esc_html__( 'Canvas size', 'merchr' ),
//				'value'		=> 500,
//				'type'		=> 'number', 
//				'wrapper_class' => 'input_type_number',
//			]);
//			woocommerce_wp_text_input([
//				'id'		=> 'merchrcust_math1',
//				'label'		=> esc_html__( 'Math1', 'merchr' ),
//				'type'		=> 'number', 
//				'value'		=> 115, 
//				'wrapper_class' => 'input_type_number',
//			]);
//			woocommerce_wp_text_input([
//				'id'		=> 'merchrcust_math2',
//				'label'		=> esc_html__( 'Math2', 'merchr' ),
//				'type'		=> 'number', 
//				'value'		=> 36, 
//				'wrapper_class' => 'input_type_number',
//			]);
//			woocommerce_wp_text_input([
//				'id'		=> 'merchrcust_math3',
//				'label'		=> esc_html__( 'Math3', 'merchr' ),
//				'type'		=> 'number', 
//				'value'		=> 125, 
//				'wrapper_class' => 'input_type_number',
//			]);
//			woocommerce_wp_text_input([
//				'id'		=> 'merchrcust_math4',
//				'label'		=> esc_html__( 'Math4', 'merchr' ),
//				'type'		=> 'number', 
//				'value'		=> 30, 
//				'wrapper_class' => 'input_type_number',
//			]);

			?>
		</div><!--/.d-flex-->
		<?php
	}
	
	private function dataFieldsPrintType($post_id){
		/*
        woocommerce_wp_select(
			array(
				'id'			=> 'merchrcust_print_type',
				'label'			=> esc_html__( 'Print Type', 'merchr' ),
				'value'			=> CustomHelperStatic::getPrintType($post_id),
				'options'		=> PrintTypeStatic::typeList(),
				'wrapper_class' => 'js__merchrPrintTypeSelect',
			)
		);
        */
	}
	
	private function dataFieldsFont($post_id){
		woocommerce_wp_select(
			array(
				'id'		=> 'merchrcust_font_family',
				'label'		=> esc_html__( 'Font', 'merchr' ),
				'value'		=> get_post_meta( $post_id, 'merchrcust_font_family', true ),
				'options'	=> FontsStatic::fontOptions(),
				'wrapper_class' => 'js__selectFonts',
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'			=> 'merchrcust_text_color',
				'label'			=> esc_html__( 'Text Colour', 'merchr' ),
				'value'			=> esc_attr(get_post_meta( $post_id, 'merchrcust_text_color', true )),
				'placeholder'	=> 'Colour',
				'wrapper_class' => 'js__merchrTextColorInput',
			)
		);
	}
	
	private function dataFieldsOptions($post_id){
		$default_label = CustomConfigStatic::getCustomLabel();
		woocommerce_wp_text_input(
			array(
				'id' => CustomConfigStatic::productlevel_textfield_label,
				'label' => esc_html__( 'Label', 'merchr' ),
				'placeholder' => $default_label,
				'description' => esc_html__(sprintf('Leave blank to use the default label &quot;%s&quot;', $default_label)),
				//'desc_tip' => 'true',
				//'wrapper_class' => 'show_if_simple', //show_if_simple or show_if_variable
			)
		);

		$default_text = CustomConfigStatic::getCustomText();
		?>
		<div class="js__codeToTextarea">
		<?php
		woocommerce_wp_textarea_input(
			array(
				'id' => CustomConfigStatic::productlevel_textfield_default,
				'label' => esc_html__( 'Default text', 'merchr' ),
				'placeholder' => esc_attr($default_text),
				'description' =>  esc_html__(sprintf('Leave blank to use the default default text &quot;%s&quot;', $default_text)),
				//'wrapper_class'	=> ''
			)
		);

		?>
			<p class="form-field _default_text_field ">
				<label for="<?php echo esc_html(CustomConfigStatic::productlevel_textfield_default); ?>">&nbsp;</label>
				<span class="textarea js__autofill" data-autofillref="default_text">
					<?php echo esc_html(CustomConfigStatic::autofill_codes); ?>
				</span>
			</p>
		</div><!--/.js__codeToTextarea-->
		<?php
		/*
        // Checkbox
		woocommerce_wp_checkbox(
			array(
				'id' => CustomConfigStatic::productlevel_textfield_line_breaks,
				'label' => esc_html__('Line breaks', 'merchr' ),
				'description' => esc_html__( 'Allow line breaks', 'merchr' )
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id' => CustomConfigStatic::productlevel_allow_images,
				'label' => esc_html__('Images', 'merchr' ),
				'description' => esc_html__( 'Allow images', 'merchr' )
			)
		);
        */
		?>
			<p>Defaults can be changed on the <a target="_blank" href="<?php echo esc_url(CustomConfigStatic::admin_page_menu_slug); ?>&page=<?php echo esc_attr(CustomConfigStatic::admin_page_slug); ?>">Customisation admin page</a>.</p>
		<?php
	}
	
	// Hook callback function to save custom fields data
	public function saveData($post_id) {
        $post_id = (int) $post_id;
        
		$texts = [
			CustomConfigStatic::productlevel_textfield_label,
			CustomConfigStatic::productlevel_textfield_default,
			'merchrcust_font_family',
			'merchrcust_text_color',
			'merchrcust_placement_editor_type',
		];

		$simple_editor_texts = [
			'merchrcust_offset_x',
			'merchrcust_offset_y',
			'merchrcust_width',
			'merchrcust_height',
			'merchrcust_rotate',
			'merchrcust_print_type',
		];
		
		$full_editor_texts = [
			'merchrcust_design_ratio_w',
			'merchrcust_design_ratio_h',
			'merchrcust_print_area_w',
			'merchrcust_print_area_h',
			'merchrcust_print_area_shape',
			'merchrcust_preview_coords',
			'merchrcust_personalisation_areas',
		];
		
		$checkboxes = [
			CustomConfigStatic::productlevel_textfield_line_breaks,
			CustomConfigStatic::productlevel_allow_images,
		];

		if ($_POST['merchrcust_placement_editor_type'] === 'full'){
			$texts = array_merge($texts, $full_editor_texts);
		}else{
			$texts = array_merge($texts, $simple_editor_texts);
		}
		
		foreach ($texts as $field){
			if (isset($_POST[$field])){
				update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
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
