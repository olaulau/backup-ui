<?php
namespace controller;

use model\RepositoryInfoMdl;
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
		$repos = $f3->get("conf.repos");

		$error_string = "";
		$data = [];
		foreach ($repos as $name => $label)
		{
			$repo = new RepositoryInfoMdl($name);
			$repo_infos = $repo->getInfo();
			$data[$name] = $repo_infos;
		}
		$f3->set("data", $data);
		
		$view = new \View();
		echo $view->render('repositories.phtml');
	}
	
	
	public static function viewGET ($f3)
	{
		$repo_name = $f3->get("PARAMS.repo_name");
		$location = "/home/$repo_name/borg/";
		
		$repo_label = $f3->get("conf.repos.$repo_name.label");
		$f3->set("repo_label", $repo_label);
		
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
	
	
	public static function cacheRepoGET ($f3)
	{
		$cache = \Cache::instance();
		
		$repo_name = $f3->get("PARAMS.repo_name");
		$repo_info = new RepositoryInfoMdl($repo_name);
		$location = $repo_info->getLocation();

		//TODO manually check borg lock files exist
		
		// repo infos
		$repo_info->updateCache();
		
		// repo's archive list
		$cmd = "borg list $location --json";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archives_list = \json_decode($output, true);
		$archives = $archives_list["archives"];
// 		var_dump($result_code, $archives);
		$cache_key = "repo($repo_name)-list";
		$cache->set($cache_key, $archives_list);
		
		die; ///////////////////
		
		foreach ($archives as $i => $archive)
		{
			// archive info
			$archive_name = $archive["name"];
			$cmd = "borg info $location::$archive_name --json";
			\exec($cmd, $output, $result_code);
			$output = \implode(PHP_EOL, $output);
			$archive_infos = \json_decode($output, true);
// 			var_dump($archive_name, $result_code, $archive_infos);
			$cache_key = "repo($repo_name)-archive($archive_name)-info";
			$cache->set($cache_key, $archive_infos);
		}
	}
	
}
