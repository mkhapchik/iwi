<?php
namespace Aliases\Service;

class Url
{
	public function makeUrl($path)
	{
		$url = '/'.trim(parse_url($path, PHP_URL_PATH), '/');
		return $url;
	}
}

?>