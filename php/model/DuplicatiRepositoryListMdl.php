<?php
namespace model;

use ErrorException;
use service\Stuff;


class DuplicatiRepositoryListMdl extends AbstractCachedValueMdl
{
	
	private DuplicatiRepositoryInfoMdl $repo_info;
	
	
	public function __construct (DuplicatiRepositoryInfoMdl $repo_info)
	{
		$this->repo_info = $repo_info;
	}
	
	
	public function getRepoInfo () : DuplicatiRepositoryInfoMdl
	{
		return $this->repo_info;
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function getCacheKey () : string
	{
		return "server({$this->getRepoInfo()->getServerName()})-user({$this->getRepoInfo()->getUserName()})-type(duplicati)-repo({$this->getRepoInfo()->getRepoName()})-list";
	}
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function calculateValue ()/* : mixed*/
	{
		$local_server_name = Stuff::get_local_server_name();
		if($this->server_name !== $local_server_name) {
			throw new ErrorException("can't get repo infos for remote repo");
		}
		
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
		$repo_list = new BorgRepositoryListMdl($this);
		$old_repo_list_value = $repo_list->getValueFromCache();
		$repo_list_value = $repo_list->updateCache();
		
		// remove deleted archives
		if(!empty($old_repo_list_value)) {
			$old_archives_list = array_column($old_repo_list_value ["archives"], "archive");
			$archives_list = array_column($repo_list_value ["archives"], "archive");
			$archives_to_remove = array_diff($old_archives_list, $archives_list);
			foreach($archives_to_remove as $archive_name) {
				echo "removing archive $archive_name from cache <br/>" . PHP_EOL;
				$archive_info = new BorgArchiveInfoMdl($this, $archive_name);
				$archive_info->removeFromCache ();
			}
		}
		
		// archives's infos
		if(!empty($repo_list_value)) {
			foreach ($repo_list_value["archives"] as $archive) {
				$archive_name = $archive["name"];
				$archive_info = new BorgArchiveInfoMdl($this, $archive_name);
				if($force_archive_infos || !$archive_info->isCached()) {
					echo "updating archive $archive_name to cache <br/>" . PHP_EOL;
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
