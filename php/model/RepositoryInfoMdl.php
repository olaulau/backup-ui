<?php
namespace model;

class RepositoryInfoMdl extends AbstractCachedValueMdl
{
	
	private string $user_name;
	private string $repo_name;
	
	
	public function __construct ($user_name, $repo_name)
	{
		$this->user_name = $user_name;
		$this->repo_name = $repo_name;
	}
	
	
	public function getUserName ()
	{
		return $this->user_name;
	}

	public function getRepoName ()
	{
		return $this->repo_name;
	}
	
	
	public function getLocation ()
	{
		$f3 = \Base::instance();

		$home_prefix = $f3->get("conf.home_prefix");
		return $location = "$home_prefix/$this->user_name/borg/$this->repo_name/";
	}
	

	function getCacheKey ()
	{
		$cache_key = "repo($this->user_name-$this->repo_name)-info";
		return $cache_key;
	}
	
	
	function calculateValue ()
	{
		$location = $this->getLocation();
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes BORG_RELOCATED_REPO_ACCESS_IS_OK=yes borg info $location --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
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
		if(!empty($repo_list_value))
		{
			foreach ($repo_list_value["archives"] as $archive)
			{
				$archive_name = $archive["name"];
				$archive_info = new ArchiveInfoMdl($this, $archive_name);
				if($force_archive_infos || !$archive_info->isCached())
					$archive_info->updateCache(60*60*24*7); // 1w
			}
		}
	}
	
}
