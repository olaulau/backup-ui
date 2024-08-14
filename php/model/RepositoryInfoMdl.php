<?php
namespace model;

use ErrorException;


class RepositoryInfoMdl extends AbstractCachedValueMdl
{
	
	private string $user_name;
	private string $repo_name;
	
	
	public function __construct (string $user_name, string $repo_name)
	{
		$this->user_name = $user_name;
		$this->repo_name = $repo_name;
	}
	
	
	public function getUserName () : string
	{
		return $this->user_name;
	}

	public function getRepoName () : string
	{
		return $this->repo_name;
	}
	
	
	public function getLocation () : string
	{
		$f3 = \Base::instance();

		$home_prefix = $f3->get("conf.home_prefix");
		return $location = "$home_prefix/$this->user_name/borg/$this->repo_name/";
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function getCacheKey () : string
	{
		$cache_key = "repo($this->user_name-$this->repo_name)-info";
		return $cache_key;
	}
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function calculateValue ()/* : mixed*/
	{
		$location = $this->getLocation();
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes BORG_RELOCATED_REPO_ACCESS_IS_OK=yes borg info $location --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$repo = \json_decode($output, true);
		$json_error = json_last_error();
		if($json_error !== JSON_ERROR_NONE) {
			throw new ErrorException($output);
		}
		return $repo;
	}
	
	
	function updateCacheRecursive (bool $force_archive_infos=false) : void
	{
		// repo infos
		$this->updateCache();
		
		// repo's archive list
		$repo_list = new RepositoryListMdl($this);
		$old_repo_list_value = $repo_list->getValueFromCache();
		$repo_list_value = $repo_list->updateCache();
		
		// remove deleted archives
		if(!empty($old_repo_list_value)) {
			$old_archives_list = array_column($old_repo_list_value ["archives"], "archive");
			$archives_list = array_column($repo_list_value ["archives"], "archive");
			$archives_to_remove = array_diff($old_archives_list, $archives_list);
			foreach($archives_to_remove as $archive_name) {
				$archive_info = new ArchiveInfoMdl($this, $archive_name);
				$archive_info->removeFromCache ();
			}
		}
		
		// archives's infos
		if(!empty($repo_list_value)) {
			foreach ($repo_list_value["archives"] as $archive) {
				$archive_name = $archive["name"];
				$archive_info = new ArchiveInfoMdl($this, $archive_name);
				if($force_archive_infos || !$archive_info->isCached()) {
					$archive_info->updateCache();
				}
			}
		}
	}
	
	
	public function isLocked () : bool
	{
		return file_exists($this->getLocation() . "/" . "lock.exclusive");
	}
	
}
