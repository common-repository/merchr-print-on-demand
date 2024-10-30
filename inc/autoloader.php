<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin.
 *
 * @package Tutsplus_Namespace_Demo\Inc
 */
 
spl_autoload_register( 'merchrCustAutoload' );

function merchrCustAutoload( $class_name ) {
	$namespace = 'MerchrCust';
	if ( false !== strpos( $class_name, $namespace ) ) {
		$classes_dir = str_replace('\\', DIRECTORY_SEPARATOR, realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR);
		$class_file = str_replace( ['_', '\\'], DIRECTORY_SEPARATOR, $class_name ) . '.php';
		require_once $classes_dir . $class_file;
	}
}
