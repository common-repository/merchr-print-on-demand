<?php
/**
 * @link              https://merchr.com
 * @since             1.1.29
 * @package           Merchr
 *
 * @wordpress-plugin
 * Plugin Name:       Merchr Print on Demand
 * Description:       Merchr Hub Print on Demand and Personalisation Plug-in.
 * Version:           1.1.29
 * Requires at least: 5.0.0
 * Requires PHP:      7.0.0
 * Author:            Merchr
 * Author URI:        https://merchr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       merchr
 * Domain Path:       /languages
 */
 
// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

define('MERCHR_PLUGIN_NAME', 'merchr');
define('MERCHR_PLUGIN_FILENAME', __FILE__);
define('MERCHR_PLUGIN_VERSION', '1.1.29');
define('MERCHR_PLUGIN_DATABASE_VERSION', '1.0.1');
define('MERCHR_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MERCHR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MERCHR_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('MERCHR_HUB_API_URL', 'https://hub.merchr.com'); // With protocol
define('MERCHR_HUB_API_URL_FAILOVER', 'https://hub2.merchr.com'); // With protocol

require_once 'inc/php7-php8.php';// new functions
require_once 'inc/autoloader.php';

$merchrcust_plugin = new MerchrCust\Plugin();
$merchrcust_plugin->version = MERCHR_PLUGIN_VERSION;
$merchrcust_plugin->path = MERCHR_PLUGIN_PATH;
$merchrcust_plugin->url = MERCHR_PLUGIN_URL;

new MerchrCust\Enqueue($merchrcust_plugin);
new MerchrCust\HubConnect();
new MerchrCust\Content\ProductDetail;
new MerchrCust\Content\ProductDataTab;
new MerchrCust\Customisation\CustomAdminSection;
new MerchrCust\Customisation\CustomProductVariations;
new MerchrCust\Customisation\CustomProductDataTab;
new MerchrCust\Customisation\CustomProductDetail;
new MerchrCust\Customisation\CustomMain;
new MerchrCust\Customisation\CustomInvoices;

$merchrcust_activate = new MerchrCust\Activate($merchrcust_plugin);
register_activation_hook( __FILE__, [$merchrcust_activate, 'activate']);

if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
	function woocommerce_template_loop_product_thumbnail() {
		$merchrcust_custom_archive = new MerchrCust\Customisation\CustomArchive;
		echo $merchrcust_custom_archive->getProductThumbnail();
	} 
}

// Init Merchr Hub
require_once 'hub/init.php';
