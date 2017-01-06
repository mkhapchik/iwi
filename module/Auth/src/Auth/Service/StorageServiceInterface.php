<?php
namespace Auth\Service;

interface StorageServiceInterface
{
	public function get($key);
	public function set($key, $value);
	public function isExist($key);
	public function clear($key);
	
}