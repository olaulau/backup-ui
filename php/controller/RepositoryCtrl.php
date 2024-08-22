<?php
namespace controller;

use Base;
use model\ArchiveInfoMdl;
use model\RepositoryInfoMdl;
use model\RepositoryListMdl;
use service\Stuff;


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
		// get conf
		$servers = $f3->get("conf.servers");
		$f3->set("servers", $servers);
		$users = $f3->get("conf.users");
		$f3->set("users", $users);
		$repos = $f3->get("conf.repos");
		$f3->set("repos", $repos);
		
		$data = [];
		foreach($servers as $server_name => list("label" => $server_label, "url" => $server_url)) {
			foreach ($repos as $user_name => $user) {
				foreach ($user as $repo_name => $repo_label) {
					$repo_info = new RepositoryInfoMdl($user_name, $repo_name, $server_name);
					$repo_info_value = $repo_info->getValueFromCache();
					$data [$server_name] [$user_name] [$repo_name] ["info"] = $repo_info_value;
					
					$repo_list = new RepositoryListMdl($repo_info);
					$repo_list_value = $repo_list->getValueFromCache();
					$data [$server_name] [$user_name] [$repo_name] ["list"] = $repo_list_value;
					
					if(!empty($repo_list_value)) {
						$archives = $repo_list_value ["archives"];
						$last_archive = $archives[array_key_last($archives)];
						$last_archive_name = $last_archive ["name"];
						$last_archive = (new ArchiveInfoMdl($repo_info, $last_archive_name))->getValueFromCache();
					}
					else {
						$last_archive = null;
					}
					$data [$server_name] [$user_name] [$repo_name] ["last_archive"] = $last_archive;
				}
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
		
		$local_server_name = Stuff::get_local_server_name();
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name, $local_server_name);
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
		
		$local_server_name = Stuff::get_local_server_name();
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name, $local_server_name);
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
	
	
	public static function cacheUpdateRepoGET (Base $f3) : void
	{
		// params
		$force_archive_infos = $f3->get("GET.force_archive_infos");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		
		// update own cache
		$local_server_name = Stuff::get_local_server_name();
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name, $local_server_name);
		$repo_info->updateCacheRecursive($force_archive_infos ?? false);
		
		// prepare data to be sent
		$repo_list = new RepositoryListMdl($repo_info);
		$data = [
			"repo_info" =>		$repo_info->getValueFromCache(),
			"repo_list" =>		$repo_list->getValueFromCache(),
			"archives_info" =>	[],
		];
		foreach($data ["repo_list"] ["archives"] as $archive) {
			$archive_name = $archive ["archive"];
			$archive_info = new ArchiveInfoMdl($repo_info, $archive_name);
			$data ["archives_info"] [$archive_name] = $archive_info->getValueFromCache();
		}
		
		// push data to other servers
		$local_server_name = Stuff::get_local_server_name();
		$servers = $f3->get("conf.servers");
		foreach($servers as $server_name => list("label" => $server_label, "url" => $server_url, "remote" => $server_remote)) {
			if($server_remote === true) { // don't push to yourself
				$server_url = rtrim($server_url, "/");
				$url = $server_url . $f3->alias("cache_push", ["server_name" => $local_server_name, "user_name" => $user_name, "repo_name" => $repo_name]);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, ["data" => json_encode($data)]);
				curl_exec($ch);
				curl_close($ch);
			}
		}
	}
	
	
	public static function cachePushPOST (Base $f3) : void
	{
		// params & data
		$server_name = $f3->get("PARAMS.server_name");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$data = json_decode($f3->get("POST.data"), true);
		
		// push into cache
		$repo_info = new RepositoryInfoMdl($user_name, $repo_name, $server_name);
		$repo_info->pushIntoCache($data ["repo_info"]);
		
		$repo_list = new RepositoryListMdl($repo_info);
		$repo_list->pushIntoCache($data ["repo_list"]);
		
		foreach($data ["archives_info"] as $archive_name => $archive) {
			$archive_info = new ArchiveInfoMdl($repo_info, $archive_name);
			$archive_info->pushIntoCache($archive);
		}
	}
	
}
