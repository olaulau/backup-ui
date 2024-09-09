<?php
namespace controller;

use Base;
use ErrorException;
use model\BorgArchiveInfoMdl;
use model\BorgRepositoryInfoMdl;
use model\BorgRepositoryListMdl;
use model\DuplicatiRepositoryInfoMdl;
use model\DuplicatiRepositoryListMdl;
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
		$repos_borg = $f3->get("conf.repos.borg");
		$f3->set("repos_borg", $repos_borg);
		$repos_duplicati = $f3->get("conf.repos.duplicati");
		$f3->set("repos_duplicati", $repos_duplicati);
		
		$data_borg = [];
		foreach($servers as $server_name => list("label" => $server_label, "url" => $server_url)) {
			foreach ($repos_borg as $user_name => $user) {
				foreach ($user as $repo_name => $repo_label) {
					$repo_info = new BorgRepositoryInfoMdl($user_name, $repo_name, $server_name);
					$repo_info_value = $repo_info->getValueFromCache();
					$data_borg [$server_name] [$user_name] [$repo_name] ["info"] = $repo_info_value;
					
					$repo_list = new BorgRepositoryListMdl($repo_info);
					$repo_list_value = $repo_list->getValueFromCache();
					$data_borg [$server_name] [$user_name] [$repo_name] ["list"] = $repo_list_value;
					
					if(!empty($repo_list_value)) {
						$archives = $repo_list_value ["archives"];
						$last_archive = $archives[array_key_last($archives)];
						$last_archive_name = $last_archive ["name"];
						$last_archive = (new BorgArchiveInfoMdl($repo_info, $last_archive_name))->getValueFromCache();
					}
					else {
						$last_archive = null;
					}
					$data_borg [$server_name] [$user_name] [$repo_name] ["last_archive"] = $last_archive;
				}
			}
		}
		$f3->set("data_borg", $data_borg);
		
		$data_duplicati = [];
		foreach($servers as $server_name => list("label" => $server_label, "url" => $server_url)) {
			foreach ($repos_duplicati as $user_name => $user) {
				foreach ($user as $repo_name => $repo) {
					$repo_label = $repo ["label"];
					$repo_passphrase = $repo ["passphrase"];
					
					$repo_info = new DuplicatiRepositoryInfoMdl($user_name, $repo_name, $server_name);
					$repo_info_value = $repo_info->getValueFromCache();
					$data_duplicati [$server_name] [$user_name] [$repo_name] ["info"] = $repo_info_value;
					
					$repo_list = new DuplicatiRepositoryListMdl($repo_info);
					$repo_list_value = $repo_list->getValueFromCache();
					$data_duplicati [$server_name] [$user_name] [$repo_name] ["list"] = $repo_list_value;
					
					// if(!empty($repo_list_value)) {
					// 	$archives = $repo_list_value ["archives"];
					// 	$last_archive = $archives[array_key_last($archives)];
					// 	$last_archive_name = $last_archive ["name"];
					// 	$last_archive = (new DuplicatiArchiveInfoMdl($repo_info, $last_archive_name))->getValueFromCache();
					// }
					// else {
					// 	$last_archive = null;
					// }
					// $data_duplicati [$server_name] [$user_name] [$repo_name] ["last_archive"] = $last_archive;
				}
			}
		}
		$f3->set("data_duplicati", $data_duplicati);
		
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
		// params
		$repo_type = $f3->get("PARAMS.repo_type");
		$f3->set("repo_type", $repo_type);
		
		$user_name = $f3->get("PARAMS.user_name");
		$user_label = $f3->get("conf.users.$user_name");
		$f3->set("user_label", $user_label);
		
		$repo_name = $f3->get("PARAMS.repo_name");
		$f3->set("repo_name", $repo_name);
		
		$repo_label = $f3->get("conf.repos.$repo_type.$user_name.$repo_name");
		$f3->set("repo_label", $repo_label);
		
		// get data
		$local_server_name = Stuff::get_local_server_name();
		$repo_info = new BorgRepositoryInfoMdl($user_name, $repo_name, $local_server_name);
		
		$repo_list = new BorgRepositoryListMdl($repo_info);
		$f3->set("repo_list", $repo_list);
		
		$archives_names = $repo_list->get_archives_names();
		$f3->set("archives_names", $archives_names);
		
		
		$js_data = [];
		$archives_info = [];
		foreach ($archives_names as $archive_name) {
			$dt = $repo_list->get_archive_date($archive_name);
			if(empty($dt)) {
				throw new ErrorException("empty datetime");
			}
			$js_data [] = $dt->getTimestamp();
			
			$archive_info = new BorgArchiveInfoMdl($repo_info, $archive_name);
			$archive_info_value = $archive_info->getValueFromCache();
			if(!empty($archive_info_value)) {
				$archives_info [ $archive_name ] = $archive_info_value;
			}
		}
		$f3->set("js_data", $js_data);
		$f3->set("archives_info", $archives_info);
		
		$page ["title"] = "archives ( ".count($archives_names) ." )";
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
				"label"	=> $repo_type,
				"url"	=> null,
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
		$repo_label = $f3->get("conf.repos.borg.$repo_name.label");
		$f3->set("repo_label", $repo_label);
		
		$local_server_name = Stuff::get_local_server_name();
		$repo_info = new BorgRepositoryInfoMdl($user_name, $repo_name, $local_server_name);
		$arch_info = new BorgArchiveInfoMdl($repo_info, $archive_name);
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
		$repo_type = $f3->get("PARAMS.repo_type");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		
		$local_server_name = Stuff::get_local_server_name();
		$data = [];
		if($repo_type === "borg") {
			// update own cache
			$repo_info = new BorgRepositoryInfoMdl($user_name, $repo_name, $local_server_name);
			$repo_info->updateCacheRecursive($force_archive_infos ?? false);
			
			// prepare data to be sent
			$repo_list = new BorgRepositoryListMdl($repo_info);
			$data = [
				"repo_info" =>		$repo_info->getValueFromCache(),
				"repo_list" =>		$repo_list->getValueFromCache(),
				"archives_info" =>	[],
			];
			foreach($data ["repo_list"] ["archives"] as $archive) {
				$archive_name = $archive ["archive"];
				$archive_info = new BorgArchiveInfoMdl($repo_info, $archive_name);
				$data ["archives_info"] [$archive_name] = $archive_info->getValueFromCache();
			}
		}
		elseif ($repo_type === "duplicati") {
			// update own cache
			$repo_info = new DuplicatiRepositoryInfoMdl($user_name, $repo_name, $local_server_name);
			$repo_info->updateCacheRecursive($force_archive_infos ?? false);
			
			// prepare data to be sent
			$repo_list = new DuplicatiRepositoryListMdl($repo_info);
			$data = [
				"repo_info" =>		$repo_info->getValueFromCache(),
				"repo_list" =>		$repo_list->getValueFromCache(),
				"archives_info" =>	[],
			];
			// foreach($data ["repo_list"] ["archives"] as $archive) {
			// 	$archive_name = $archive ["archive"];
			// 	$archive_info = new DuplicatiArchiveInfoMdl($repo_info, $archive_name);
			// 	$data ["archives_info"] [$archive_name] = $archive_info->getValueFromCache();
			// }
		}
		else {
			throw new ErrorException("invalid repo type");
		}
		
		// push data to other servers
		$local_server_name = Stuff::get_local_server_name();
		$servers = $f3->get("conf.servers");
		foreach($servers as $server_name => list("label" => $server_label, "url" => $server_url, "remote" => $server_remote)) {
			if($server_remote === true) { // don't push to yourself
				$server_url = rtrim($server_url, "/");
				$url = $server_url . $f3->alias("cache_push", ["server_name" => $local_server_name, "user_name" => $user_name, "repo_name" => $repo_name]);
				// echo "POST $url <br/>" . PHP_EOL;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, ["data" => json_encode($data)]);
				$res = curl_exec($ch);
				if($res === false) {
					echo "curl error #" . curl_errno($ch) . " : " . curl_error($ch) . "<br/>" . PHP_EOL;
				}
				curl_close($ch);
			}
		}
	}
	
	
	public static function cachePushPOST (Base $f3) : void
	{
		// params & data
		$repo_type = $f3->get("PARAMS.repo_type");
		$server_name = $f3->get("PARAMS.server_name");
		$user_name = $f3->get("PARAMS.user_name");
		$repo_name = $f3->get("PARAMS.repo_name");
		$data = json_decode($f3->get("POST.data"), true);
		
		// push into cache
		if($repo_type === "borg") {
			$repo_info = new BorgRepositoryInfoMdl($user_name, $repo_name, $server_name);
			$repo_info->pushIntoCache($data ["repo_info"]);
			
			$repo_list = new BorgRepositoryListMdl($repo_info);
			$repo_list->pushIntoCache($data ["repo_list"]);
			
			foreach($data ["archives_info"] as $archive_name => $archive) {
				$archive_info = new BorgArchiveInfoMdl($repo_info, $archive_name);
				$archive_info->pushIntoCache($archive);
			}
		}
		elseif($repo_type === "duplicati") {
			///////////////////
			// $data ["repo_info"]
			// $data ["repo_list"]
			// $data ["archives_info"]
			
			$repo_info = new DuplicatiRepositoryInfoMdl($user_name, $repo_name, $server_name);
			$repo_info->pushIntoCache($data ["repo_info"]);
			
			$repo_list = new DuplicatiRepositoryListMdl($repo_info);
			$repo_list->pushIntoCache($data ["repo_list"]);
			
			// foreach($data ["archives_info"] as $archive_name => $archive) {
			// 	$archive_info = new DuplicatiArchiveInfoMdl($repo_info, $archive_name);
			// 	$archive_info->pushIntoCache($archive);
			// }
		}
		else {
			throw new ErrorException("unknown repo type");
		}
	}
	
}
