<?php
namespace controller;


class IndexCtrl
{

	public static function beforeRoute ($f3)
	{
		
	}
    
	
	public static function afterRoute ($f3)
	{
		
	}

	
	public static function indexGET ($f3)
	{
		$page ["title"] = $f3->get("conf.hostname_override") ?? $f3->get("HOST");
		$page ["breadcrumbs"] = [];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('index.phtml');
	}
	
	
	public static function testGET ($f3)
	{
		
		die;
	}
	
}
