<?php
namespace controller;

use model\ArchiveInfoMdl;
use model\RepositoryInfoMdl;
use model\RepositoryListMdl;

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
			$repo_info_value = $repo->getValue();
			$data[$name] = $repo_info_value;
		}
		$f3->set("data", $data);
		
		$view = new \View();
		echo $view->render('repositories.phtml');
	}
	
	
	public static function viewGET ($f3)
	{
		$repo_name = $f3->get("PARAMS.repo_name");
		$repo_label = $f3->get("conf.repos.$repo_name.label");
		$f3->set("repo_label", $repo_label);
		
		$repo_info = new RepositoryInfoMdl($repo_name);		
		$repo_list = new RepositoryListMdl($repo_info);
		$repo_list_value = $repo_list->getValue();
		
		$archives = array_reverse($repo_list_value["archives"]);
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

		//TODO manually check borg lock files exist
		
		// repo infos
		$repo_info = new RepositoryInfoMdl($repo_name);
		$location = $repo_info->getLocation();
		$repo_info->updateCache();
		
		// repo's archive list
		$repo_list = new RepositoryListMdl($repo_info);
		$repo_list_value = $repo_list->updateCache();
		
		die; ///////////////////
		
		foreach ($repo_list_value["archives"] as $archive)
		{
			$archive_name = $archive["name"];
			$archive_info = new ArchiveInfoMdl($repo_info, $archive_name);
			$archive_info->updateCache();
		}
	}
	
}
