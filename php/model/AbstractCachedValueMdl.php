<?php
namespace model;

abstract class AbstractCachedValueMdl
// 	extends Mdl
{
	
	abstract protected function getCacheKey ();
	
	
	abstract protected function calculateValue ();
	
	
	public function getValue()
	{
		$f3 = \Base::instance();
		$cache = \Cache::instance();
		
		$repo_infos = $cache->get($this->getCacheKey());
		if($repo_infos == false)
			$repo_infos = $this->updateCache();
		return $repo_infos;
	}
	
	
	public function updateCache ($ttl=0)
	{
		$f3 = \Base::instance();
		$cache = \Cache::instance();
		
		$value = $this->calculateValue();
		if(!empty($value))
			$cache->set($this->getCacheKey(), $value, $ttl);
		return $value;
	}
	
}
