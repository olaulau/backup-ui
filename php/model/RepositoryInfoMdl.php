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
		return $location = "/home/$this->repo_name/borg/$this->repo_name/";
	}
	

	function getCacheKey ()
	{
		return $cache_key = "repo($this->repo_name)-info";
	}
	
	
	function calculateValue ()
	{
		$location = $this->getLocation();
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes borg info $location --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
// 		var_dump($output); die;
		$repo = \json_decode($output, true);
		return $repo;
	}
	
	
	function updateCacheRecursive ($force_archive_infos=false)
	{
		// TODO manually check borg lock files exist
		
		// repo infos
		$this->updateCache();
		
		// repo's archive list
		$repo_list = new RepositoryListMdl($this);
		$repo_list_value = $repo_list->updateCache();
		
		// archives's infos
		foreach ($repo_list_value["archives"] as $archive)
		{
			$archive_name = $archive["name"];
			$archive_info = new ArchiveInfoMdl($this, $archive_name);
			if($force_archive_infos || !$archive_info->isCached())
				$archive_info->updateCache(60*60*24*7); // 1w
		}
	}
	
}
