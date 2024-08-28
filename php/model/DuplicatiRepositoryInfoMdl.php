<?php
namespace model;

use ErrorException;
use service\Stuff;


class DuplicatiRepositoryInfoMdl extends AbstractCachedValueMdl
{
	
	private string $server_name;
	private string $user_name;
	private string $repo_name;
	
	
	public function __construct (string $user_name, string $repo_name, string $server_name)
	{
		$this->server_name = $server_name;
		$this->user_name = $user_name;
		$this->repo_name = $repo_name;
	}
	
	
	public function getServerName () : string
	{
		return $this->server_name;
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
		return $location = "$home_prefix/$this->user_name/duplicati/$this->repo_name/";
	}
	
	
	public function get_conf () : array
	{
		$f3 = \Base::instance();
		
		$res = $f3->get("conf.repos.duplicati.{$this->getUserName()}.{$this->getRepoName()}");
		return $res;
	}
	
	public function get_label () : string
	{
		$conf = $this->get_conf();
		return $conf ["label"];
	}
	
	public function get_passphrase () : string
	{
		$conf = $this->get_conf();
		return $conf ["passphrase"];
	}
	
	
	/**
	 * @implements AbstractCachedValueMdl
	 */
	function getCacheKey () : string
	{
		return "server({$this->server_name})-user({$this->user_name})-type(duplicati)-repo({$this->repo_name})-info";
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
		
		$repo_location = $this->getLocation();
		$cmd = "du -sb {$repo_location}";
		exec($cmd, $output, $result_code);
		if($result_code !== 0) {
			throw new ErrorException("error executing 'du' command");
		}
		
		$regex = '/^(\d+)\t(.+)$/';
		$res = preg_match($regex, $output[0], $matches);
		if($res === false) {
			throw new ErrorException("error during 'du' output parsing");
		}
		if($res === 0) {
			throw new ErrorException("'du' output was not as anticipated");
		}
		
		return $matches[1];
	}
	
	
	function updateCacheRecursive (bool $force_archive_infos=false) : void
	{
		// repo infos
		$this->updateCache();
		
		// repo list
		$repo_list = new DuplicatiRepositoryListMdl($this);
		$repo_list->updateCache();
	}
	
	
	public function isLocked () : bool
	{
		return file_exists($this->getLocation() . "/" . "lock.exclusive");
	}
	
}
