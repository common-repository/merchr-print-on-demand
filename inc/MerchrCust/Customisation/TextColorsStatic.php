<?php

namespace MerchrCust\Customisation;

class TextColorsStatic {

	public static function colorList(){
		return [
			'Red'			=> '#ec2227',
			'Orange'		=> '#f15b29',
			'Yellow'		=> '#f7ec13',
			'Green'			=> '#079247',
			'Blue'			=> '#1f4497',
			'Purple'		=> '#643d97',
			'Black'			=> '#000000',
			'White'			=> '#FFFFFF',
			'Pink'			=> '#fad6e5',
			'Golden'		=> '#e08827',
			'Cream'			=> '#f7f7c7',
			'Light Green'	=> '#3edd77',
			'Light Blue'	=> '#40b1dd',
			'Lilac'			=> '#cc7add',
			'Grey'			=> '#6b615d',
			'Default'		=> false,
		];
	}
	
	public static function colorListRadio($selected = null){
		$output = '<div id="merchrcust_custom_text_colors">';
		$lists = array_chunk(self::colorList(), ceil(count(self::colorList())/2), true);
		foreach ($lists as $list){
			$output .= '<div>';
			foreach ($list as $name => $color){
				$classname = '';
				if (!$color){
					$classname = 'js__merchrDefaultColorInput';
					$color = esc_attr($selected);
				}
				$checked = ( $selected === $color? 'checked' : '' );
				$output .= "<label style='background-color: {$color};' class='{$classname}'>
					<input type='radio' name='merchrcust_text_color' {$checked} value='{$color}'>
				</label>";
			}
			$output .= '</div>';
		}
		$output .= '</div>';
		return $output;
	}
}
