<?php

namespace MerchrCust\Customisation;

class PrintTypeStatic {

	public static function typeList(){
		return [
			'single'	=> esc_html__('Single Colour', 'merchr'), // default
			'engrave'	=> esc_html__('Engrave', 'merchr'),
			//'emboss'	=> 'Emboss',
			//'deboss'	=> 'Deboss',
		];
	}
	
	public static function typeListDefault(){
		$list = self::typeList();
		$keys = array_keys($list);
		return array_shift($keys);
	}
	
}
