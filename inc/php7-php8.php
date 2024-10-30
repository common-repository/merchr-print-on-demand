<?php

if (!function_exists('str_starts_with')){
	function str_starts_with($haystack, $needle){
		return (substr($haystack, 0, strlen($needle)) === $needle);
	}
}

if (!function_exists('str_contains')) {
	function str_contains(string $haystack, string $needle): bool
	{
		return '' === $needle || false !== strpos($haystack, $needle);
	}
}