<?php
/**
 * Merchr Hub Helpers Currencies Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/helpers
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\helpers;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubHelpersCurrencies
{
	/**
	 * Get Store Currency.
	 *
	 * @return string
	 */
	public static function getStoreCurrency()
    {
        return get_option('woocommerce_currency');
    }
    
    
    /**
	 * Check and Convert Currency.
	 *
	 * @param array
     * @param string
     * @param string
     * @param float
	 *
	 * @return array
	 */
	public static function checkAndConvertCurrency(
        $currencies, 
        $store_base_currency, 
        $product_currency, 
        $product_price
    )
    {
        $store_base_currency = strtoupper(trim($store_base_currency));
        $product_currency = strtoupper(trim($product_currency));
        $converted = false;
        $rate = 1;
        
        if($product_currency === $store_base_currency) {
            return [
                number_format($product_price, 2, '.', ''),
                $converted,
                $rate
            ];
        } else {
            foreach($currencies as $currency) {
                $code = strtoupper(trim($currency['code']));
                if($code === $product_currency) {
                    if(isset($currency['exchange_map'][$store_base_currency])) {
                        $rate = (float) $currency['exchange_map'][$store_base_currency];
                        $product_price = $product_price * $rate;
                        $converted = true;
                    }
                    
                    break;
                }
            }
        }

        return [
            number_format($product_price, 2, '.', ''),
            $converted,
            $rate
        ];
    }
}
