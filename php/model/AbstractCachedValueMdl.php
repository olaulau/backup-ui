<?php
namespace model;

abstract class AbstractCachedValueMdl
// 	extends Mdl
{
	
	abstract protected function getCacheKey ();
	
	
	abstract protected function calculateValue ();
	
	
	public function getInfo()
	{
		$f3 = \Base::instance();
		$cache = \Cache::instance();
		
		$repo_infos = $cache->get($this->getCacheKey());
		if($repo_infos == false)
			$repo_infos = $this->updateCache();
		return $repo_infos;
	}
	
	
	public function updateCache ()
	{
		$f3 = \Base::instance();
		$cache = \Cache::instance();
		
		$value = $this->calculateValue();
		$cache->set($this->getCacheKey(), $value);
		return $value;
	}
	
}
