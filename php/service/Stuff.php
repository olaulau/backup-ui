<?php
namespace service;

use Base;
use ByteUnits\Binary;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use ErrorException;


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
	
	
	public static function get_local_server_name () : ?string
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
	
	
	public static function start_delay_bg_color (DateTimeInterface $dt) : string
	{
		$f3 = Base::instance();
		
		// calculate delay
		$dtz = new DateTimeZone("Europe/Paris");
		$now = new DateTimeImmutable("now", $dtz);
		$di = $dt->diff($now);
		$delay_days = $di->days;
		if($delay_days < 0) {
			throw new ErrorException("future archive");
		}
		
		// match according conf color
		$delays = $f3->get("conf.delays");
		$res = "";
		foreach($delays as $color => $min) {
			if($delay_days >= $min) {
				$res = $color;
			}
		}
		return $res;
	}
	
}
