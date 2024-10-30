<?php

namespace MerchrCust;

class Activate {
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->option_key = 'Activated_Plugin';
	}
	
	public function activate()
	{
		add_option($this->option_key, $this->plugin->slug);
	}
}