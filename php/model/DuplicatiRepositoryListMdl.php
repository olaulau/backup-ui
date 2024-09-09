<?php
namespace model;

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
		
		$repo_path = "/home/{$this->getRepoInfo()->getUserName()}/duplicati/{$this->getRepoInfo()->getRepoName()}/";
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
				throw new ErrorException("regex didn't match");
			}
			$archive_number = $matches[1];
			$archive_dt_str = $matches[2];
			$filesets [$archive_number] = $archive_dt_str;
		}
		return $filesets;
	}
	
}
