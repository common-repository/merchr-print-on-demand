<?php
/**
 * Merchr Hub Init Script.
 *
 * @since      1.0.0
 * @package    Merchr
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub;

use MerchrHub\includes\MerchrHub;
use MerchrHub\includes\MerchrHubAutoloader;
use MerchrHub\includes\MerchrHubActivator;
use MerchrHub\includes\helpers\MerchrHubHelpersSetup;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

// Set additional hub constants
DEFINE('MERCHR_HUB_NAMESPACE', 'MerchrHub');
DEFINE('MERCHR_HUB_INCLUDE_PATH', MERCHR_PLUGIN_PATH . 'hub'); // No trailing slash

// Require Composer and plug-in autoloaders
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'MerchrHubAutoloader.php';

// Instantiate plug-in autoloader
new MerchrHubAutoloader(
	MERCHR_HUB_INCLUDE_PATH, 
	MERCHR_HUB_NAMESPACE
);

// Register plug-in activation and activated
$merchr_hub_activator = new MerchrHubActivator();
register_activation_hook(
	MERCHR_PLUGIN_FILENAME, 
	[
		$merchr_hub_activator,
		'activate'
	]
);
add_action(
	'activated_plugin', 
	[
		$merchr_hub_activator,
		'activated'
	]
);

// Action scheduler uses its own loader so require file directly
$merchr_action_scheduler = require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'woocommerce/action-scheduler/action-scheduler.php';

// Only execute if plug-in is active
if(MerchrHubHelpersSetup::isMerchrHubPluginActive()) {
	$merchr_hub_plugin = new MerchrHub();
	$merchr_hub_plugin->run();
}
