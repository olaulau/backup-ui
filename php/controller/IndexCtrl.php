<?php
namespace controller;

use Base;

class IndexCtrl
{

	public static function beforeRoute (Base $f3) : void
	{
		
	}
    
	
	public static function afterRoute (Base$f3) : void
	{
		
	}

	
	public static function indexGET (Base$f3) : void
	{
		$page ["title"] = $f3->get("conf.hostname_override") ?? $f3->get("HOST");
		$page ["breadcrumbs"] = [];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('index.phtml');
	}
	
	
	public static function testGET (Base$f3) : void
	{
		$servers = $f3->get("conf.servers");
		var_dump($servers);
		
		die;
	}
	
}
