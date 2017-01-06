<?php
namespace Auth\Service;
use Exception;

class CookieService implements StorageServiceInterface
{
	const TOKEN = 'token';
	const LAST_ACTIVITY = 'last_activity';
	
	public function get($key)
	{
		if(!$this->isExist($key)) throw new Exception("Key element '$key' does not exist");
		return $_COOKIE[$key];
	}
	
	public function set($key, $value)
	{
		setcookie($key, $value, 0, '/', null, false, true);
	}
	
	public function isExist($key)
	{
		return isset($_COOKIE[$key]);
		
	}
	
	public function clear($key)
	{
		if($this->isExist($key))
		{
			unset($_COOKIE[$key]);
			setcookie($key, null, -1, '/');
		}
	}
}