<?php
namespace model;

class ArchiveInfoMdl extends AbstractCachedValueMdl
{
	
	private RepositoryInfoMdl $repo_info;
	
	
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
	
	
	function getCacheKey ()
	{
		return $cache_key = "repo(" . $this->repo_info->getRepoName() . ")-archive($this->archive_name)-info";
	}
	
	
	function calculateValue ()
	{
		$location = $this->getRepoInfo()->getLocation();
		$cmd = "borg info $location::$this->archive_name --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archive_infos = \json_decode($output, true);
		return $archive_infos;
	}
	
}
