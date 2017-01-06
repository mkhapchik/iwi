<?php
namespace Auth\Service;

class StorageService implements StorageServiceInterface
{
	protected $storage;
	
	public function __construct(StorageServiceInterface $storage)
	{
		$this->storage = $storage;
	}
	
	public function get($key)
	{
		return $this->storage->get($key);
	}
	
	public function set($key, $value)
	{
		return $this->storage->set($key, $value);
	}
	
	public function isExist($key)
	{
		return $this->storage->isExist($key);
	}
	
	public function clear($key)
	{
		return $this->storage->clear($key);
	}
}