<?php
namespace model;

use ErrorException;


class ArchiveInfoMdl extends AbstractCachedValueMdl
{
	
	private RepositoryInfoMdl $repo_info;
	private string $archive_name;
	
	
	public function __construct ($repo_info, $archive_name)
	{
		$this->repo_info = $repo_info;
		$this->archive_name = $archive_name;
	}
	
	
	public function getRepoInfo ()
	{
		return $this->repo_info;
	}
	
	public function getArchiveName ()
	{
		return $this->archive_name;
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function getCacheKey () : string
	{
		return $cache_key = "repo(" . $this->repo_info->getUserName() . "-". $this->repo_info->getRepoName() . ")-archive($this->archive_name)-info";
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function calculateValue () : mixed
	{
		$location = $this->getRepoInfo()->getLocation();
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes BORG_RELOCATED_REPO_ACCESS_IS_OK=yes borg info $location::$this->archive_name --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archive_infos = \json_decode($output, true);
		$json_error = json_last_error();
		if($json_error !== JSON_ERROR_NONE) {
			throw new ErrorException($output);
		}
		return $archive_infos;
	}
	
}
