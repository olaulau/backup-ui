<?php
namespace model;

use ErrorException;


class RepositoryListMdl extends AbstractCachedValueMdl
{
	
	private RepositoryInfoMdl $repo_info;
	
	
	public function __construct (RepositoryInfoMdl $repo_info)
	{
		$this->repo_info = $repo_info;
	}
	
	
	public function getRepoInfo ()
	{
		return $this->repo_info;
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function getCacheKey () : string
	{
		return "server(" . $this->repo_info->getServerName() . ")-user(" . $this->repo_info->getUserName() . ")-repo(" . $this->repo_info->getRepoName() . ")-list";
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function calculateValue ()/* : mixed*/
	{
		$location = $this->getRepoInfo()->getLocation();
		$cmd = "BORG_UNKNOWN_UNENCRYPTED_REPO_ACCESS_IS_OK=yes BORG_RELOCATED_REPO_ACCESS_IS_OK=yes borg list $location --json 2>&1";
		\exec($cmd, $output, $result_code);
		$output = \implode(PHP_EOL, $output);
		$archives_list = \json_decode($output, true);
		$json_error = json_last_error();
		if($json_error !== JSON_ERROR_NONE) {
			throw new ErrorException($output);
		}
		return $archives_list;
	}
	
}
