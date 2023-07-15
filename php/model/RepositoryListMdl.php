<?php
namespace model;

class RepositoryListMdl extends AbstractCachedValueMdl
{
	
	private RepositoryInfoMdl $repo_info;
	
	
	public function __construct ($repo_info)
	{
		$this->repo_info = $repo_info;
	}
	
	
	public function getRepoInfo ()
	{
		return $this->repo_info;
	}
	
	
	function getCacheKey ()
	{
		return $cache_key = "repo(" . $this->repo_info->getRepoName() . ")-list";
	}
	
	
	function calculateValue ()
	{
		$location = $this->getRepoInfo()->getLocation();
		$cmd = "borg list $location --json";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archives_list = \json_decode($output, true);
// 		var_dump($result_code, $archives_list);
		return $archives_list;
	}
	
}
