<?php
namespace service;

use ByteUnits\Binary;


class Stuff
{
	public static function convert_size(float $bytes) : float
	{
		$formated = Binary::bytes($bytes)->format("GiB", " ");
		list($size, $unit) = explode(" ", $formated);
		$float = floatval($size);
		return $float;
	}
	
	
	public static function float_formater(float $float) : string
	{
		return number_format($float, 1, ",", " ");
	}
	
}
