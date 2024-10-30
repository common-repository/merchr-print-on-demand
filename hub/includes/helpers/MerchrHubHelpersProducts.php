<?php
/**
 * Merchr Hub Helpers Products Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersProducts
{
	/**
	 * Create Product Attributes.
	 *
     * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param array
	 *
	 * @return array
	 */
	public static function createProductAttributes($product, array $product_options)
	{
		$i = 0;
		$attributes = [];
		$attributes_list = [];
        
		foreach($product_options as $option) {
			// Set option id and name
			$option_id = $option['id'];
			$name = $option['product_option_name'];
			
			// Loop values and prepare for WC
			$values = [];
			foreach($option['product_assigned_values'] as $value) {
				$values[] = $value['product_option_value_name'];
				$attributes_list[$option_id][$value['id']] = [
					$name,
					$value['product_option_value_name'],
				];
			}
			
			$attribute = new \WC_Product_Attribute();
			$attribute->set_name($name);
			$attribute->set_options($values);
			$attribute->set_position($i);
			$attribute->set_visible(true);
			$attribute->set_variation(true);
			$attributes[] = $attribute;
			$i++;
		}
        
		$product->set_attributes($attributes);
		$product->save();
        
        return $attributes_list;
	}
    
    /**
	 * Return Attributes List.
	 *
	 * @param array
	 *
	 * @return array
	 */
	public static function returnAttributesList(array $product_options)
	{

		$attributes_list = [];
		foreach($product_options as $option) {
			// Set option id and name
			$option_id = $option['id'];
			$name = $option['product_option_name'];
			
			// Loop values and prepare for WC
			$values = [];
			foreach($option['product_assigned_values'] as $value) {
				$values[] = $value['product_option_value_name'];
				$attributes_list[$option_id][$value['id']] = [
					$name,
					$value['product_option_value_name'],
				];
			}
		}
        
        return $attributes_list;
	}
    
    
    /**
	 * Return ID Keyed Variant Array.
	 *
	 * @param array
	 *
	 * @return array
	 */
	public static function returnIdKeyedVariantArray(array $variants)
	{
		$keyed_variants = [];
        foreach($variants as $variant) {
            $keyed_variants[$variant['id']] = $variant;
        }
        return $keyed_variants;
	}
    
    /**
	 * Clean Store Base Country String.
     *
     * WordPress uses the 2 char country format (Alpha 2)
     * but also supports state if applicable 
     * for that country and uses the following
     * format: COUNTRY:STATE codes for example
     * Arizona would return US:AZ, we only need
     * the country code so this method returns that
	 *
	 * @param string
	 *
	 * @return string
	 */
	public static function cleanStoreBaseCountryString(string $country)
	{
		$country = trim($country);
        if($country === '') { return ''; }
        
        if(stripos($country, ':') !== false) {
            $parts = explode(':', $country);
            $country = $parts[0];
        }
        
        return $country;
	}
    
    /**
	 * Check and Set Country Restrictions.
	 *
     * @param object @WC_Product_Variable/@WC_Product_Simple
	 * @param array
	 *
	 * @return void
	 */
	public static function checkAndSetCountryRestrictions($product, array $product_data)
	{
		// Company decided to disable country restrictions for the moment
        // Will simply return this function to bypass it but leave the 
        // process in place for the future.
        return;
        
        if(isset($product_data['country_codes']) && !empty($product_data['country_codes'])) {
            $country_codes = $product_data['country_codes'];
            $country_codes_type = $product_data['country_codes_type'];
            
            $type = 'excluded';
            if($country_codes_type === 'allowed') {
                $type = 'specific';
            }
            
            $country_array = [];
            foreach($country_codes as $code) {
                $country_array[] = $code['alpha-2'];
            }
            
            // Update product
            $product->update_meta_data('_fz_country_restriction_type', $type);
            $product->update_meta_data('_restricted_countries', $country_array);
        } else {
            $product->update_meta_data('_fz_country_restriction_type', 'all');
            $product->update_meta_data('_restricted_countries', '');
        }
	}
    
    /**
	 * Return Random Chars
     * Not secure for cryptography
	 *
	 * @param int
	 *
	 * @return string
	 */
	public static function randomChars(int $length)
	{
		$keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($keyspace) - 1);
            $random_string .= $keyspace[$index];
        }
        
        return $random_string;
	}
}
