<?php
namespace model;

use DateTimeImmutable;
use DateTimeInterface;
use ErrorException;


class BorgRepositoryListMdl extends AbstractCachedValueMdl implements RepositoryListInterface
{
	
	private BorgRepositoryInfoMdl $repo_info;
	
	
	public function __construct (BorgRepositoryInfoMdl $repo_info)
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
		return "server(" . $this->repo_info->getServerName() . ")-user(" . $this->repo_info->getUserName() . ")-type(borg)-repo(" . $this->repo_info->getRepoName() . ")-list";
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
	
	
	/**
	 * @implements RepositoryListInterface
	 */
	public function get_archives_names() : array
	{
		$cache_value = $this->getValueFromCache();
		if(empty($cache_value)) {
			return [];
		}
		
		$archives = $cache_value ["archives"];
		$archives = array_reverse($archives);
		$res = array_column($archives, "name");
		return $res;
	}
	
	
	/**
	 * @implements RepositoryListInterface
	 */
	public function get_last_archive_name () : string
	{
		$archives_names = $this->get_archives_names();
		if(empty($archives_names)) {
			return "";
		}
		return $archives_names [0];
	}
	
	
	/**
	 * @implements RepositoryListInterface
	 */
	public function get_archive_date (string $archive_name) : ?DateTimeInterface
	{
		$cache_value = $this->getValueFromCache();
		if(empty($cache_value)) {
			throw new ErrorException("empty cache for this repo list");
		}
		
		$archives = $cache_value ["archives"];
		$archives_by_name = array_combine(array_column($archives, "name"), $archives);
		$dt_str = $archives_by_name [$archive_name] ["start"] ?? null;
		if(empty($dt_str)) {
			return null;
		}
		return new DateTimeImmutable($dt_str);
	}
	
}
