<?php
/**
 * Merchr Hub Helpers Tax Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersTax
{
	/**
	 * Is Tax Removal Required.
	 *
	 * @param array
	 *
	 * @return array
	 */
	public static function isTaxRemovalRequired(array $options)
	{
		$result = false;
        $wc_taxes_enabled = get_option('woocommerce_calc_taxes');
        $wc_prices_inc_tax = get_option('woocommerce_prices_include_tax');
        $wc_tax_display_shop = get_option('woocommerce_tax_display_shop');
        $wc_tax_display_cart = get_option('woocommerce_tax_display_cart');
        
        if($wc_taxes_enabled == 'yes') {
            if( ($wc_prices_inc_tax == 'yes' 
               && $wc_tax_display_shop == 'excl' 
               && $wc_tax_display_cart == 'excl') 
               || ($wc_prices_inc_tax == 'no' 
               && $wc_tax_display_shop == 'incl' 
               && $wc_tax_display_cart == 'incl') 
            ) {
                $result = true;
            }
        }
        
        return $result;
	}
    
    
    /**
	 * Return Tax Calculations.
	 *
     * @param int
	 * @param array
     * @param float
	 *
	 * @return float
	 */
	public static function returnTaxCalculationValue(
        int $tax_class_id, 
        array $tax_classes, 
        $tax_calcualtaion_value
    )
	{
		foreach($tax_classes as $class) {
			$class_id = (int) $class['id'];
				if($class_id === $tax_class_id) {
					$percentage = sprintf("%02d", $class['percentage']);
					$tax_calcualtaion_value = (float) "1.{$percentage}";
					break;
				}
		}
        
        return $tax_calcualtaion_value;
	}
    
    /**
	 * Return Formatted Price.
	 *
     * @param int
	 * @param array
	 *
	 * @return float
	 */
	public static function returnFormattedPrice(float $price, float $tax_calculation)
	{
		return number_format(($price / $tax_calculation), 2, '.', '');
	}
}
