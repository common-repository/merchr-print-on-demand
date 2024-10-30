<?php

namespace MerchrCust;

use MerchrCust\Customisation\FontsStatic;

class Enqueue {
	
	const CACHEBUMP = 18; // For production, bump the value of const CACHEBUMP up by one for a one-off de-cache.
	private $devmode = false;

	public function __construct($plugin){
		
		$this->plugin = $plugin;

		if (isset($_SERVER['HTTP_MERCHRDEV']) && $_SERVER['HTTP_MERCHRDEV'] === 'VIOLINBIOLOGY'){
			$this->devmode = true;
		}
		add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueue' ], 999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'publicEnqueue' ], 999 );
		
	}
	
	// CSS/JS version bump to counteract cache.
	// To prevent caching issue with CSS and JS files
	// For Dev DEV ONLY return strtotime now 
	// Alternatively run 
	//		jQuery('link[rel="stylesheet"]').attr('href', function(){var href = jQuery(this).attr('href'); return href + (href.indexOf('?') > -1? '&' : '?') + Math.random()}); 
	//		in the console to reload CSS, but it must be done each page load.

	// use constant MERCHRDEV instead. private static $dev = false;
	public function version(){

		if ($this->devmode){
			return '1.' . strtotime('now');
		}else{
			return self::CACHEBUMP;
		}
	}
	
	/* 
	 * Add JS and any other style/js includes
	 */
	public function adminEnqueue() {
		wp_enqueue_script( 'merchrcust-admin-js', $this->plugin->url . 'resources/js/merchrcust-admin.js', ['jquery', 'fabricjs-js', 'webfont-js'], $this->version(), true );
		$style = 'merchrcust-admin';
		wp_register_style( $style, $this->plugin->url . '/resources/css/merchrcust-admin.css', [], $this->version(), 'all', 999 );
		wp_enqueue_style( $style );
        $this->sharedEnqueue();
	}

	public function publicEnqueue() {
		wp_enqueue_script( 'merchrcust-public-js', $this->plugin->url . 'resources/js/merchrcust-public.js', ['jquery', 'fabricjs-js', 'webfont-js'], $this->version(), true );
		$style = 'merchrcust-public';
		wp_register_style( $style, $this->plugin->url . '/resources/css/merchrcust-public.css', [], $this->version(), 'all', 999 );
		wp_enqueue_style( $style );
        $this->sharedEnqueue();
	}
	
	public function sharedEnqueue(){
		// Add fonts
		$style = 'merchrcust-fonts';
		wp_register_style( $style, false );
		wp_enqueue_style( $style );
		wp_add_inline_style( $style, FontsStatic::importCss() );
		
		// Add shared JS
		wp_enqueue_script( 'fabricjs-js', $this->plugin->url . 'resources/js/fabric.min.js', [], 1, true );
		wp_enqueue_script( 'webfont-js', $this->plugin->url . 'resources/js/webfont.js', [], $this->version(), true );

		$script_handle = 'merchrcust-shared-js';
		wp_enqueue_script( $script_handle, $this->plugin->url . 'resources/js/merchrcust-shared.js', ['jquery', 'fabricjs-js', 'webfont-js'], $this->version(), true );
		wp_add_inline_script ( $script_handle, FontsStatic::fontListJs(), 'before' );
	}	
	
}
