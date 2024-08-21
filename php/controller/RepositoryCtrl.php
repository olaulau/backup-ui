<?php
namespace controller;

use Base;
use model\ArchiveInfoMdl;
use model\RepositoryInfoMdl;
use model\RepositoryListMdl;


class RepositoryCtrl
{

	public static function beforeRoute (Base $f3) : void
	{
		
	}
    
	
	public static function afterRoute (Base $f3) : void
	{
		
	}

	
	public static function listGET (Base $f3) : void
	{
		$repos = $f3->get("conf.repos");

		$data = [];
		foreach ($repos as $user_name => $user) {
			foreach ($user as $repo_name => $repo_label) {
				$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
				$repo_info_value = $repo_info->getValueFromCache();
				$data [$user_name] [$repo_name] ["info"] = $repo_info_value;
				
				$repo_list = new RepositoryListMdl($repo_info);
				$repo_list_value = $repo_list->getValueFromCache();
				$data [$user_name] [$repo_name] ["list"] = $repo_list_value;
				
				if(!empty($repo_list_value)) {
					$archives = $repo_list_value ["archives"];
					$last_archive = $archives[array_key_last($archives)];
					$last_archive_name = $last_archive ["name"];
					$last_archive = (new ArchiveInfoMdl($repo_info, $last_archive_name))->getValueFromCache();
				}
				else {
					$last_archive = null;
				}
				$data [$user_name] [$repo_name] ["last_archive"] = $last_archive;
			}
		}
		$f3->set("data", $data);
		
		$page ["title"] = "repositories";
		$page ["breadcrumbs"] = [
			[
				"label"	=> $f3->get("conf.hostname_override") ?? $f3->get("HOST"),
				"url"	=> null,
			],
			[
				"label"	=> "repositories",
				"url"	=> $f3->get("BASE") . $f3->alias("repositories"),
			],
		];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('repositories.phtml');
	}
	
	
	public static function viewGET (Base $f3) : void
	{
		$user_name = $f3->get("PARAMS.user_name");
		$user_label = $f3->get("conf.users.$user_name");
		$f3->set("user_label", $user_label);
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
		foreach ($archives as $archive) {
			$dt = new \DateTime($archive["start"]);
			$js_data [] = $dt->getTimestamp();
			
			$archive_info = new ArchiveInfoMdl($repo_info, $archive["name"]);
			$archive_info_value = $archive_info->getValue();
			$archives_info [ $archive["name"] ] = $archive_info_value;
		}
		$f3->set("js_data", $js_data);
		$f3->set("archives_info", $archives_info);
		
		$page ["title"] = "archives ( ".count($archives_info) ." )";
		$page ["breadcrumbs"] = [
			[
				"label"	=> $f3->get("conf.hostname_override") ?? $f3->get("HOST"),
				"url"	=> null,
			],
			[
				"label"	=> "repositories",
				"url"	=> $f3->get("BASE") . $f3->alias("repositories"),
			],
			[
				"label"	=> $user_label,
				"url"	=> null,
			],
			[
				"label"	=> $repo_label,
				"url"	=> $f3->get("BASE") . $f3->alias("repository", ["user_name" => $user_name, "repo_name" => $repo_name]),
			],
		];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('repository.phtml');
	}
	
	
	public static function archiveGET (Base $f3) : void
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
	
	
	public static function cacheUpdateGET (Base $f3) : void
	{
		$repos = $f3->get("conf.repos");
		foreach ($repos as $user_name => $user) {
			foreach ($user as $repo_name => $repo_label) {
				$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
				$repo_info->updateCacheRecursive();
			}
		}
	}
	
	
	public static function cacheUpdateRepoGET (Base $f3) : void
	{
		$force_archive_infos = $f3->get("GET.force_archive_infos");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name);
		$repo_info->updateCacheRecursive($force_archive_infos ?? false);
	}
	
}
