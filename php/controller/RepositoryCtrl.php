<?php
namespace controller;

class RepositoryCtrl
{

	public static function beforeRoute ()
	{
		
	}
    
	
	public static function afterRoute ()
	{
		
	}

	
	public static function listGET ()
	{
		$view = new \View();
		echo $view->render('repositories.phtml');
	}
	
	
	public static function viewGET ()
	{
		$view = new \View();
		echo $view->render('repository.phtml');
	}
	
	
	public static function testGET ()
	{
		$view = new \View();
		echo $view->render('test.phtml');
	}
	
}
