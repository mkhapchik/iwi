<?php
namespace Menu\Navigation\Page;

use Zend\Navigation\Page;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Navigation\Exception;

class Mvc extends Page\Mvc
{
    public function isActive($recursive = false)
    {
		if($this->active===false)
		{
			$result = parent::isActive($recursive);
		}
		else
		{
			$result = Page\AbstractPage::isActive($recursive);
		}
		
		return $result;
    }
	
	public function setActive($active = true)
    {
       	$this->active = $active;
        return $this;
    }
}
