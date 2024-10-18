<?php
namespace controller;

use Base;
use ErrorException;

class IndexCtrl
{

	public static function beforeRoute (Base $f3) : void
	{
		
	}
    
	
	public static function afterRoute (Base $f3) : void
	{
		
	}

	
	public static function indexGET (Base $f3) : void
	{
		$page ["title"] = $f3->get("conf.hostname_override") ?? $f3->get("HOST");
		$page ["breadcrumbs"] = [];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('index.phtml');
	}
	
	
	public static function testGET (Base $f3) : void
	{
		
		die;
	}
	
	
	public static function faviconGET (\Base $f3, array $url, string $controler)
	{
		$web = \Web::instance();
		$filename = __DIR__ . "/../../assets/app_icon.svg";
		$sent = $web->send($filename);
		if ($sent === false) {
			throw new ErrorException("web send error");
		}
	}
	
}
