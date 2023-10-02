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

		$data = [];
		foreach ($repos as $user_name => $user)
		{
			foreach ($user as $repo_name => $repo_label)
			{
				$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
				$repo_info_value = $repo_info->getValue();
				// var_dump($repo_info_value); die;
				$data [$user_name] [$repo_name] ["info"] = $repo_info_value;
				$repo_list = new RepositoryListMdl($repo_info);
				$repo_list_value = $repo_list->getValue();
				$data [$user_name] [$repo_name] ["list"] = $repo_list_value;
			}
		}
		$f3->set("data", $data);
		
		$view = new \View();
		echo $view->render('repositories.phtml');
	}
	
	
	public static function viewGET ($f3)
	{
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$f3->set("repo_name", $repo_name);
		$repo_label = $f3->get("conf.repos.$user_name.$repo_name");
		$f3->set("repo_label", $repo_label);
		
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
		$repo_list = new RepositoryListMdl($repo_info);
		$repo_list_value = $repo_list->getValue();
		
		$archives = array_reverse($repo_list_value["archives"]);
		$f3->set("archives", $archives);
		
		$js_data = [];
		$archives_info = [];
		foreach ($archives as $archive)
		{
			$dt = new \DateTime($archive["start"]);
			$js_data [] = $dt->getTimestamp();
			
			$archive_info = new ArchiveInfoMdl($repo_info, $archive["name"]);
			$archive_info_value = $archive_info->getValue();
			$archives_info [ $archive["name"] ] = $archive_info_value;
		}
		$f3->set("js_data", $js_data);
		$f3->set("archives_info", $archives_info);
		
		$view = new \View();
		echo $view->render('repository.phtml');
	}
	
	
	public static function archiveGET ($f3)
	{
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$archive_name = $f3->get("PARAMS.archive_name");
		$repo_label = $f3->get("conf.repos.$repo_name.label");
		$f3->set("repo_label", $repo_label);
		
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
		$arch_info = new ArchiveInfoMdl($repo_info, $archive_name);
		$arch_info_value = $arch_info->getValue();
		
		echo "<pre>";
		ini_set('xdebug.var_display_max_depth', 10);
		ini_set('xdebug.var_display_max_children', 256);
		ini_set('xdebug.var_display_max_data', 1024);
		var_dump($arch_info_value);
		echo "</pre>";
		die;
	}
	
	
	public static function cacheUpdateGET ($f3)
	{
		$repos = $f3->get("conf.repos");
		foreach ($repos as $user_name => $user)
		{
			foreach ($user as $repo_name => $repo_label)
			{
				$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
				$repo_info->updateCacheRecursive();
			}
		}
	}
	
	
	public static function cacheUpdateRepoGET ($f3)
	{
		$force_archive_infos = $f3->get("GET.force_archive_infos");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
		$repo_info->updateCacheRecursive($force_archive_infos ?? false);
	}
	
}
