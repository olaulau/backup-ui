<?php
namespace model;

class RepositoryInfoMdl extends AbstractCachedValueMdl
{
	
	private $repo_name;
	
	
	public function __construct ($repo_name)
	{
		$this->repo_name = $repo_name;
	}
	
	
	public function getRepoName ()
	{
		return $this->repo_name;
	}
	
	
	public function getLocation ()
	{
		return $location = "/home/$this->repo_name/borg/";
	}
	

	function getCacheKey ()
	{
		return $cache_key = "repo($this->repo_name)-info";
	}
	
	
	function calculateValue ()
	{
		$location = $this->getLocation();
		$cmd = "borg info $location --json";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$repo = \json_decode($output, true);
// 		var_dump($result_code, $repo); die;
		return $repo;
	}
	
}
