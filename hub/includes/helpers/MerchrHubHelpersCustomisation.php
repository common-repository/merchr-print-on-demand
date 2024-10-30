<?php
/**
 * Merchr Hub Helpers Customisation Class.
 * 
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersCustomisation
{
	/**
	 * Process Customisation Data
	 *
	 * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param array
	 * @param array
	 * @param array
	 */
	public static function processCustomisationData($product, array $editable_text_areas, array $image_preview, array $design) 
	{
		// Set image preview data
		$ptlx = $image_preview['ptlx'];
		$ptly = $image_preview['ptly'];
		$ptrx = $image_preview['ptrx'];
		$ptry = $image_preview['ptry'];
		$pbrx = $image_preview['pbrx'];
		$pbry = $image_preview['pbry'];
		$pblx = $image_preview['pblx'];
		$pbly = $image_preview['pbly'];
		
		// Set offsets from image preview data
		$design_area_offset_x = $ptlx;
		$design_area_offset_y = $ptly;
		
		// Calculate width and height from image preview data
		$design_area_width = $ptrx - $ptlx;
		$design_area_height = $pbly - $ptly;
		
		// Set defaults
		$font_family = 'Patrick Hand';
		$text_colour = '#0ca1cc'; // Merchr Cyan
		$editor = 'simple';
		$offset_x = 0;
		$offset_y = 0;
		$width = 0;
		$height = 0;
		$rotate = 0;
		$textfield_line_breaks = false;
		
		// This next few lines will not be required after
        // the designer tool has been refactored.
        $left = $editable_text_areas[0]['leftPercent'];
        $top = $editable_text_areas[0]['topPercent'];
        
        // Get the editable text area data and process
		// The offsets and dimensions are calculated from the 
		// preview/design area, we need to calculate for this.
		//
		// For now Merchr Hub only supports one editable 
		// area per product. We will support multiple areas
		// in the future so this will need to be updated and
		// the frontend personalisation handler.
		$text_area_width = $editable_text_areas[0]['areaWidth'];
		$text_area_height = $editable_text_areas[0]['areaHeight'];
		$text_area_offset_x = $left;
		$text_area_offset_y = $top;
		
		// Recalculate data as positioning is done on the image area not the design area.
		$width = $text_area_width * $design_area_width / 100;
		$height = ($text_area_height * $design_area_height / 100);
		$offset_x = $design_area_offset_x + ($design_area_width * $text_area_offset_x / 100);
		$offset_y = $design_area_offset_y + ($design_area_height * $text_area_offset_y / 100);
		
		// Check for and set other editable text data
		$editable_text_reference = trim($editable_text_areas[0]['editable_text_ref']);
		$font_family = trim($editable_text_areas[0]['textFamily']);
		$text_colour = trim($editable_text_areas[0]['textColour']);
		$rotate = trim($editable_text_areas[0]['angle']);
        $text_size = trim($editable_text_areas[0]['textSize']);
        $default_text = trim($editable_text_areas[0]['textString']);
        /*
        $design_data = json_decode($design['storage']['json_data'], true);
		if(!empty($design_data)) {
			foreach($design_data['objects'] as $object) {
				if(trim($object['type']) == 'textbox') {
					if(isset($object['merchr']['editable_text_ref'])) {
						if($editable_text_reference == trim($object['merchr']['editable_text_ref'])) {
							// Set default meta
							$font_family = $object['fontFamily'];
							$text_colour = $object['fill'];
							$rotate = $object['angle'];
							
							// Set additional meta
							$product->update_meta_data('merchrcust_font_size', $object['fontSize']);
							$product->update_meta_data('merchrcust_editable_text', $object['text']);
							
							// All done
							break;
						}
					}
				}
			}
		}
        */
		
		// Sanitise values before saving
		$font_family = sanitize_text_field($font_family);
		$text_colour = sanitize_text_field($text_colour);
		$rotate = (int) $rotate;
		$width = (float) $width;
		$height = (float) $height;
		$offset_x = (float) $offset_x;
		$offset_y = (float) $offset_y;
        
        // JSONify the image preview data
        $image_preview_json = json_encode($image_preview);
		
		// Update meta
		$product->update_meta_data('merchrcust_font_family', $font_family);
		$product->update_meta_data('merchrcust_text_color', $text_colour);
        $product->update_meta_data('merchrcust_font_size', $text_size);
		$product->update_meta_data('merchrcust_editable_text', $default_text);
		$product->update_meta_data('merchrcust_placement_editor_type', $editor);
		$product->update_meta_data('merchrcust_offset_x', $offset_x);
		$product->update_meta_data('merchrcust_offset_y', $offset_y);
		$product->update_meta_data('merchrcust_width', $width);
		$product->update_meta_data('merchrcust_height', $height);
		$product->update_meta_data('merchrcust_rotate', $rotate);
        $product->update_meta_data('merchrcust_image_preview', $image_preview_json);
		
		// Save product
		$product->save_meta_data();
	}
}
