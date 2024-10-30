<?php

namespace MerchrCust\Customisation;

class FontsStatic {

	public static function fontList(){
		return [
		  "Abril Fatface",
		  "Aladin",
		  "Alex Brush",
		  "Alfa Slab One",
		  "Amiri",
		  "Anton",
		  "Architects Daughter",
		  "Archivo Black",
		  "Audiowide",
		  "Bahiana",
		  "Balsamiq Sans",
		  "Bangers",
		  "Barrio",
		  "Bebas Neue",
		  "Berkshire Swash",
		  "Beth Ellen",
		  "Bubblegum Sans",
		  "Bungee",
		  "Caesar Dressing",
		  "Calistoga",
		  "Carter One",
		  "Caveat",
		  "Caveat Brush",
		  "Chela One",
		  "Chelsea Market",
		  "Chewy",
		  "Chicle",
		  "Comfortaa",
		  "Condiment",
		  "Corben",
		  "Coustard",
		  "Covered By Your Grace",
		  "Creepster",
		  "Dancing Script",
		  "Dekko",
		  "Dokdo",
		  "Dorsa",
		  "East Sea Dokdo",
		  "Erica One",
		  "Flavors",
		  "Freckle Face",
		  "Fredoka One",
		  "Fugaz One",
		  "Gaegu",
		  "Germania One",
		  "Gluten",
		  "Graduate",
		  "Grand Hotel",
		  "Grandstander",
		  "Homemade Apple",
		  "Italiana",
		  "Jolly Lodger",
		  "Josefin Sans",
		  "Kalam",
		  "Kanit",
		  "Knewave",
		  "Koulen",
		  "La Belle Aurore",
		  "Lacquer",
		  "Lato",
		  "Leckerli One",
		  "Libre Baskerville",
		  "Lilita One",
		  "Londrina Sketch",
		  "Londrina Solid",
		  "Mansalva",
		  "Metamorphous",
		  "Mitr",
		  "Modak",
		  "Monoton",
		  "Montserrat",
		  "Montserrat Subrayada",
		  "Mr Bedfort",
		  "Mr Dafoe",
		  "Mrs Sheppards",
		  "Nanum Brush Script",
		  "Nanum Pen Script",
		  "Nerko One",
		  "Niconne",
		  "Nosifer",
		  "Notable",
		  "Nunito",
		  "Odibee Sans",
		  "Open Sans",
		  "Oswald",
		  "Pacifico",
		  "Passero One",
		  "Passion One",
		  "Patrick Hand",
		  "Patua One",
		  "Permanent Marker",
		  "Pirata One",
		  "Playfair Display",
		  "Poiret One",
		  "Purple Purse",
		  "Qwigley",
		  "Raleway",
		  "Rambla",
		  "Ranchers",
		  "Roboto",
		  "Roboto Slab",
		  "Rock Salt",
		  "Rozha One",
		  "Rubik Mono One",
		  "Sedgwick Ave Display",
		  "Shrikhand",
		  "Six Caps",
		  "Skranji",
		  "Sora",
		  "Source Sans Pro",
		  "Source Serif Pro",
		  "Special Elite",
		  "Spectral",
		  "Spicy Rice",
		  "Squada One",
		  "Staatliches",
		  "Suranna",
		  "Teko",
		  "Ultra",
		  "Uncial Antiqua",
		  "Varela",
		  "Vibur",
		  "Vollkorn",
		  "Work Sans",
		  "Yeseva One"
		];
	}
	
	public static function fontOptions(){
		$array = self::fontList();
		return array_combine($array, $array);
	}
	
	public static function importCss(){
		$families = str_replace(' ', '+', 'family=' . implode('&family=', self::fontList()));
		return "@import url('https://fonts.googleapis.com/css2?" . $families . "&display=swap')";
	}
	
	public static function fontListJs(){
		return 'var merchrcust_fontlist = ' . json_encode(self::fontList()) . ';';
	}
	
	public static function fontListSelect($selected = null){
		$output = '<select name="merchrcust_font_family" id="merchrcust_fontfamily_select">';
		foreach (self::fontList() as $font){
			$output .= '<option ' . ($selected === $font? 'selected' : '') . ' value="' . esc_attr($font) . '" style="font-family: \'' . esc_attr($font) . '\'">' . esc_attr($font) . '</option>';
		}
		$output .= '</select>';
		return $output;
	}
}
