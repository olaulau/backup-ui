<?php
namespace controller;

use olafnorge\borgphp\InfoCommand;
use olafnorge\borgphp\ListCommand;

class RepositoryCtrl
{

	public static function beforeRoute ()
	{
		
	}
    
	
	public static function afterRoute ()
	{
		
	}

	
	public static function listGET ($f3)
	{
		require __DIR__ . '/../../config.inc.php';
		$f3->set("conf", $conf);

		$error_string = "";
		$outputs = [];
		foreach ($conf["repos"] as $name => $location)
		{
			$cmd = new InfoCommand([
				$location,
			]);
			$cmd->setEnv([
				"BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK" => "yes",
			]);
			try
			{
				$output = $cmd->mustRun()->getOutput();
			}
			catch (\Exception $ex)
			{
				$errors = $cmd->getErrorOutput();
				if(is_array($errors[0]))
					$error_message = $errors[0]["message"];
				else
					$error_message = $errors[0];
				if(str_starts_with($error_message, "Failed to create/acquire the lock "))
				{
					$output = "LOCK";
				}
				else
				{
					if(isset($errors[1]))
					{
						if(is_array($errors[1]))
							$error_message = $errors[1]["message"];
						else
							$error_message = $errors[1];
						if
						(
							strpos($error_message, "PermissionError: [Errno 13] Permission denied") !== false && 
							(
							strpos($error_message, "lock.exclusive") !== false || 
							strpos($error_message, "lock.roster") !== false
							)
						)
						{
							$output = "LOCK";
						}
						else
						{
							$output = "UNKNOWN_ERROR";
							$error_string .= "<hr>";
							$error_string .= "<hr>";
							$error_string .= "<pre>" . $ex->getMessage() . "</pre>";
							$error_string .= "<hr>";
							$error_string .= "<pre>" . var_export($errors, true) . "</pre>";
						}
					}
					else
					{
						$output = "ERROR";
					}
				}
			}
// 			var_dump($output); die;
			$outputs[$name] = $output;
		}
		$f3->set("outputs", $outputs);
		$f3->set("error_string", $error_string);
		
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
		catch (\Exception $ex)
		{
			echo "<pre>" . $ex->getMessage() . "</pre>";
			echo "<hr>";
			$err = $cmd->getErrorOutput();
			echo "<pre>"; var_dump($err); echo "</pre>";
			die;
		}
// 		var_dump($output); die;
		
		$archives = array_reverse($output["archives"]);
		$f3->set("archives", $archives);
		
		$js_data = [];
		foreach ($archives as $archive)
		{
			$dt = new \DateTime($archive["start"]);
			$js_data [] = $dt->getTimestamp();
		}
		// var_dump($js_data); die;
		$f3->set("js_data", $js_data);
		
		$view = new \View();
		echo $view->render('repository.phtml');
	}
	
}
