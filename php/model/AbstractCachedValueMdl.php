<?php
namespace model;


abstract class AbstractCachedValueMdl
{
	/**
	 * this method has to be implemented, and gives the cache key
	 */
	abstract protected function getCacheKey () : string;
	
	
	/**
	 * this method has to be implemented, and gives the value the cache should store
	 */
	abstract protected function calculateValue ()/* : mixed*/;
	
	
	/**
	 * indicate if the entity is present in cache
	 */
	public function isCached () : bool
	{
		$cache = \Cache::instance();
		
		$value = $cache->get($this->getCacheKey());
		return($value !== false);
	}
	
	
	/**
	 * get value from cache only
	 */
	public function getValueFromCache ()/* : mixed*/
	{
		$cache = \Cache::instance();
		
		$value = $cache->get($this->getCacheKey());
		return $value;
	}
	
	/**
	 * get value from cache, or by calculating it if needed (and updating the cache)
	 */
	public function getValue()/* : mixed*/
	{
		$value = $this->getValueFromCache();
		if($value === false)
			$value = $this->updateCache();
		return $value;
	}
	
	
	/**
	 * update cache with the value calculated
	 */
	public function updateCache (int $ttl=0)/* : mixed*/
	{
		$cache = \Cache::instance();
		
		$value = $this->calculateValue();
		if(!empty($value))
			$cache->set($this->getCacheKey(), $value, $ttl);
		return $value;
	}
	
	
	/**
	 * remove entry from cache
	 */
	public function removeFromCache () : void
	{
		$cache = \Cache::instance();
		$cache->clear($this->getCacheKey());
	}
	
	
	/**
	 * push content into cache
	 */
	public function pushIntoCache (/*mixed */$content, int $ttl=0) : void
	{
		$cache = \Cache::instance();
		$cache->set($this->getCacheKey(), $content, $ttl);
	}
	
}
