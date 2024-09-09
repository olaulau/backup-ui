<?php
namespace model;

use DateTimeInterface;


interface RepositoryListInterface 
{
	
	/**
	 * get archive names (by descending order of date)
	 */
	public function get_archives_names () : array;
	
	
	/**
	 * get the name of the last archive
	 */
	public function get_last_archive_name () : string;
	
	
	/**
	 * get the date of a named archive
	 */
	public function get_archive_date (string $archive_name) : ?DateTimeInterface;
	
}
