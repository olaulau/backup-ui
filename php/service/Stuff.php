<?php
namespace service;

use Base;
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
	
	
	public static function get_local_server_name () : string
	{
		$f3 = Base::instance();
		$servers = $f3->get("conf.servers");
		foreach($servers as $server_name => $server) {
			if($server ["remote"] === false) {
				return $server_name;
			}
		}
		return null;
	}
}
