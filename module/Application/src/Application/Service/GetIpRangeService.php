<?php
namespace Application\Service;

class GetIpRangeService
{
	private $CIDR;
	private $ip;
	private $mask;
	
	public function __construct($CIDR)
	{
		$this->CIDR = $CIDR;
		$ip_info = explode('/', $CIDR);
		$this->ip = $ip_info[0];
		$this->mask = isset($ip_info[1]) ? $ip_info[1] : 32;
	}
	
	/**
	* ѕолучение минимального ip адреса в подсети
	*/
	public function getMinIp()
	{
		$ip_dec = ip2long($this->ip);
		$mask_dec = bindec($this->mask2bin($this->mask));
		$ip_min = long2ip($ip_dec & $mask_dec);
		return $ip_min;
	}

	/**
	* ѕолучение максимального ip адреса в подсети
	*/
	public function getMaxIp()
	{
		$ip_dec = ip2long($this->ip);
		$mask_dec = bindec($this->mask2bin($this->mask));
		$ip_max = long2ip($ip_dec | ~$mask_dec);
		return $ip_max;
	}
	
	/**
	* ѕеревод маски подсети в бинарное представление
	* @param $mask - маска подсети
	*/
	private function mask2bin($mask)
	{
		return str_pad(str_pad('', $mask, 1, STR_PAD_LEFT), 32, 0, STR_PAD_RIGHT);
	}
	
	public function getIp()
	{
		return $this->ip;
	}
	
	public function getMask()
	{
		return $this->mask;
	}
	
	public function getCIDR()
	{
		return $this->ip . '/' . $this->mask;
	}
	
	
}
?>