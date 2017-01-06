<?php
namespace Auth\Service;
use Exception;

class SessionService implements StorageServiceInterface
{
	const TOKEN = 'token';
	const LAST_ACTIVITY = 'last_activity';
	
	protected $storage;
	
	public function __construct($storage)
	{
		$this->storage = $storage;
	}
	
	public function get($key)
	{
		if(!$this->isExist($key)) throw new Exception("Key element '$key' does not exist");
		$storage_data = $this->storage->read();
		
		return $storage_data[$key];
	}
	
	public function set($key, $value)
	{
		$storage_data = $this->storage->read();
		$storage_data[$key] = $value;
		$this->storage->write($storage_data);
	}
	
	public function isExist($key)
	{
		$is_exist = false;
		if(!$this->storage->isEmpty())
		{
			$storage_data = $this->storage->read();
			$is_exist = isset($storage_data[$key]) ? true : false;
		}
		
		return $is_exist;
	}
	
	public function clear($key)
	{
		if($this->isExist($key))
		{
			$storage_data = $this->storage->read();
			unset($storage_data[$key]);
			if(count($storage_data)==0) $this->storage->clear();
			else $this->storage->write($storage_data);
		}
	}
}