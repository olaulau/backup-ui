<?php
namespace controller;

use olafnorge\borgphp\ListCommand;

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
	
	
	public static function viewGET ($f3)
	{
		require __DIR__ . '/../../config.inc.php';

		$location = $_GET["location"];
		$f3->set("location", $location);
		$repo_name = array_search($location, $conf["repos"]);
		$f3->set("repo_name", $repo_name);
		
		// list repository's archives
		$cmd = new ListCommand([
			$location,
		]);
		
		$cmd->setEnv([
			"BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK" => "yes",
		]);
		try
		{
			$output = $cmd->mustRun()->getOutput();
		}
		catch (Exception $e)
		{
			echo "<pre>" . $e->getMessage() . "</pre>";
			echo "<hr>";
			$err = $cmd->getErrorOutput();
			echo "<pre>"; var_dump($err); echo "</pre>";
			die;
		}
		// 	var_dump($output); die;
		$f3->set("output", $output);
		
		$js_data = [];
		foreach ($output["archives"] as $archive)
		{
			$dt = new \DateTime($archive["start"]);
			$js_data [] = $dt->getTimestamp();
		}
		// var_dump($js_data); die;
		$f3->set("js_data", $js_data);
		
		$view = new \View();
		echo $view->render('repository.phtml');
	}
	
	
	public static function testGET ()
	{
		$view = new \View();
		echo $view->render('test.phtml');
	}
	
}
