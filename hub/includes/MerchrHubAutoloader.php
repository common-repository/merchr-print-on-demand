<?php
/**
 * Merchr Hub Autoloader Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAutoloader
{
	protected $path; 		// @var string
	protected $namespace; // @var string
	
	/*
     * Construct.
	 *
	 * @param string
	 * @param string
    */
	public function __construct(string $path, string $namespace)
	{
		$this->path = $path;
		$this->namespace = $namespace;
		
		// Set the include paths
		set_include_path(get_include_path() . PATH_SEPARATOR . $this->path);
		
		// Register autoloader
		spl_autoload_register([$this, 'merchrHubAutoloader']);
	}
	
	/*
     * Merchr Hub Loader.
	 *  
	 * @param string 
    */
	private function merchrHubAutoloader(string $class_name)
	{
		// Format passed class and correct for inclusion
		$class_name = str_ireplace($this->namespace . '\\', '', $class_name);
		$class_name = str_ireplace('\\', DIRECTORY_SEPARATOR, $class_name);
		
		// Set file path, check file exists and require once
		$filename = $this->path . DIRECTORY_SEPARATOR . $class_name . '.php';
		if(is_readable($filename)) {
			require_once $filename;
		}
	}
}
