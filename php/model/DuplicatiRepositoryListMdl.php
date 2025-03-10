<?php
namespace model;

use DateTimeImmutable;
use DateTimeInterface;
use ErrorException;
use service\Stuff;


class DuplicatiRepositoryListMdl extends AbstractCachedValueMdl
{
	
	private DuplicatiRepositoryInfoMdl $repo_info;
	
	const date_time_format = "m/d/Y H:i:s";
	
	
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
		if($this->getRepoInfo()->getServerName() !== $local_server_name) {
			throw new ErrorException("can't get repo infos for remote repo");
		}
		
		$repo_path = $this->getRepoInfo()->getLocation();
		$cmd = "PASSPHRASE={$this->getRepoInfo()->get_passphrase()} duplicati-cli find {$repo_path} --auto-update=false --all-versions=true --debug-output=true --full-result=true --console-log-level=Information";
		exec($cmd, $output, $result_code);
		if($result_code !== 0) {
			throw new ErrorException("error executing 'duplicati-cli'");
		}
		
		$list_begins_after = "Listing filesets:";
		$search = array_search($list_begins_after, $output);
		if($search === false) {
			throw new ErrorException("coulnd't find filesets listing in 'duplicati-cli list' command output");
		}
		$filesets_strings = array_slice($output, $search+1);
		
		$filesets = [];
		foreach($filesets_strings as $filesets_str) {
			$res = preg_match('|(\d+)\t: (((\d{2})/(\d{2})/(\d{4})) ((\d{2}):(\d{2}):(\d{2})))|', $filesets_str, $matches);
			if($res === false) {
				throw new ErrorException("regex error");
			}
			if($res === 1) {
				$archive_number = $matches[1];
				$archive_dt_str = $matches[2];
				$filesets [$archive_number] = $archive_dt_str;
			}
		}
		return $filesets;
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
		return array_keys($cache_value);
	}
	
	
	/**
	 * @implements RepositoryListInterface
	 * could be common
	 */
	public function get_last_archive_name () : string
	{
		return "0";
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
		
		$dt_str = $cache_value [$archive_name];
		if(empty($dt_str)) {
			return null;
		}
		$dt = DateTimeImmutable::createFromFormat(self::date_time_format, $dt_str);
		return $dt;
	}
	
}
