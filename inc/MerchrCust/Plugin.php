<?php

namespace MerchrCust;

class Plugin {
	
	public function __construct()
	{
		// get plugin slug
		// Less code but causes a notice $plugin->slug = reset(explode('/', str_replace(WP_PLUGIN_DIR . '/', '', __DIR__)));		
		$file_path_from_plugin_root = str_replace(WP_PLUGIN_DIR . '/', '', __DIR__);
		// Explode the path into an array
		$path_array = explode('/', $file_path_from_plugin_root);
		// Plugin folder is the first element
		$this->slug = reset($path_array);
	}
}
