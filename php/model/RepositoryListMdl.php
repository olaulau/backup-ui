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
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes borg list $location --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archives_list = \json_decode($output, true);
		return $archives_list;
	}
	
}
