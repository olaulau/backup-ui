<?php
namespace controller;

use Base;
use DateTimeImmutable;
use ErrorException;


class IndexCtrl
{

	public static function beforeRoute (Base $f3) : void
	{
		
	}
    
	
	public static function afterRoute (Base$f3) : void
	{
		
	}

	
	public static function indexGET (Base$f3) : void
	{
		$page ["title"] = $f3->get("conf.hostname_override") ?? $f3->get("HOST");
		$page ["breadcrumbs"] = [];
		$f3->set("page", $page);
		
		$view = new \View();
		echo $view->render('index.phtml');
	}
	
	
	public static function testGET (Base$f3) : void
	{
		$user_name = "";
		$repo_name = "";
		$passphrase = "";
		
		$path = "/home/{$user_name}/duplicati/{$repo_name}/";
		$cmd = "du -sm {$path}";
		echo "executing {$cmd} <br/>" . PHP_EOL;
		exec($cmd, $output, $result_code);
		var_dump($result_code);
		var_dump($output);
		die;
		
		
		$cmd = "PASSPHRASE={$passphrase} duplicati-cli find {$path} --auto-update=false --all-versions=true --debug-output=true --full-result=true --console-log-level=Information";
		// echo "executing {$cmd} <br/>" . PHP_EOL;
		exec($cmd, $output, $result_code);
		// var_dump($result_code);
		// var_dump($output);
		
		
		$list_begins_after = "Listing filesets:";
		$search = array_search($list_begins_after, $output);
		if($search === false) {
			throw new ErrorException("coulnd't find filesets listing in 'duplicati-cli list' command output");
		}
		$filesets_strings = array_slice($output, $search+1);
		// var_dump($filesets);
		
		$filesets = [];
		foreach($filesets_strings as $filesets_str) {
			$res = preg_match('|(\d+)\t: (((\d{2})/(\d{2})/(\d{4})) ((\d{2}):(\d{2}):(\d{2})))|', $filesets_str, $matches);
			if($res === false) {
				throw new ErrorException("regex didn't match");
			}
			// var_dump($matches);
			$filesets [$matches[1]] = DateTimeImmutable::createFromFormat("m/d/Y H:i:s", $matches[2]);
		}
		
		var_dump($filesets);
		die;
	}
	
}
