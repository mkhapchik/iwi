<?php
namespace Menu\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;

class Menu implements ServiceLocatorAwareInterface
{
	protected $menu;
	
	protected $sm;
	
	protected $currentUri;
	
	protected $parentId;
	
	protected $route_active_name=null;
	
	protected $route_active_param_id=null;
	
	public function setServiceLocator(ServiceLocatorInterface $sm)
	{
		$this->sm = $sm;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function setRouteActive($route_name, $route_param_id=null)
	{
		$this->route_active_name = $route_name;
		$this->route_active_param_id = $route_param_id;
	}
	
	private function isActiveRoute($route_name, $route_param_id=null)
	{
		$result = false;
		if(
			isset($this->route_active_name) && $this->route_active_name==$route_name 
			&& $this->route_active_param_id == $route_param_id
		)
		{
			$result = true;			
		}
		
		return $result;
	}
	
	private function issetActiveRoute()
	{
		return isset($this->route_active_name);
	}
	
	public function getMenu($name=false, $parentId=0)
	{
		$this->parentId = $parentId;
		
		if(!$this->hasMenu($name))
		{
			$data = $this->generateMenu($name);
			$this->setMenu($data);
		}
		
		return $name===false ? $this->menu : $this->menu[$name];
	}
	
	public function setCurrentUri()
	{
		$request = $this->sm->get('request');
		$url = parse_url($request->getRequestUri());
		$this->currentUri = $url['path'];
	}
	
	public function getCurrentUri()
	{
		if(!isset($this->currentUri)) $this->setCurrentUri();
		return $this->currentUri;
	}
		
	public function setMenu($data)
	{
		if(!is_array($this->menu)) $this->menu = array();
		//$this->menu = array_merge_recursive($this->menu, $data);
		$this->menu = $this->menu + $data;
	}
	
	public function hasMenu($name)
	{
		if(is_array($this->menu) && ($name===false || array_key_exists($name, $this->menu))) return true;
		else return false;
	}
	
	public function generateMenu($name)
	{
		$menuTable = $this->sm->get('Menu\Model\MenuTable');
		
		$user = $this->sm->get('User');
		$roles = array_keys($user->getRoles());
		
		$fetchData = $menuTable->fetchMenu($name, $roles);
		
		
		$structuredList = $this->makeStructuredList($fetchData);
			
		$config = array();
		if($name) $config[$name] = array();
		
		foreach($structuredList as $n=>$menu)
		{
			foreach($menu[$this->parentId] as $item)
			{
				$page = $this->makePages($n, $structuredList, $item);
				if($page) $config[$n][] = $page;
			}
		}
		
		$app_config = $this->sm->get('config'); 
		$navigation_config = array();
		
		
		if($name)
		{
			if(isset($app_config['navigation'][$name]) && is_array($app_config['navigation'][$name]))
			{
				$navigation_config = array($name=>$app_config['navigation'][$name]);
			}
		}
		else
		{
			if(isset($app_config['navigation']) && is_array($app_config['navigation']))
			{
				$navigation_config = $app_config['navigation'];
			}
		}
		
		$config = array_merge_recursive($config, $navigation_config);
		
		return $config;
	}
	
	private function makeStructuredList($data)
	{
		$list = array();
		
		foreach($data as $row)
		{
			$list[$row['menu_name']][$row['parent_item_id']][]=$row;
		}
		
		return $list;
	}
	
	private function makePages($name, $structuredList, $item, $deep=0)
	{
		$pages = array();
		$pages['label'] = $item['label'];
		$pages['blank'] = $item['blank'];

		if(!empty($item['uri']) || empty($item['route_name']))
		{
			$pages['uri'] = (string)$item['uri'];
			$current_uri = $this->getCurrentUri();
			if(trim($pages['uri'], '/') == trim($current_uri, '/') && !$this->issetActiveRoute()) $pages['active'] = 1;
		}
		else
		{
			$route = $item['route_name'];
			$params = array();
			$route_param_id = !empty($item['route_param_id']) ? $item['route_param_id'] : null;
			if($route_param_id) $params['id']=$route_param_id;
			
			
			if(!empty($params))
			{
				$pages['route'] = $route;
				$pages['params'] = $params;
			}
			else
			{
				$pages['route'] = $route;
			}
			
			if($this->issetActiveRoute())
			{
				if($this->isActiveRoute($route, $route_param_id)) $pages['active'] = 1;
				else $pages['active'] = 0;
			}

			$pages['type']='Menu\Navigation\Page\Mvc';
		}
		
		$pages['pages'] = array();
		
		if(isset($structuredList[$name][$item['id']]))
		{
			foreach($structuredList[$name][$item['id']] as $i)
			{
				
				$child = $this->makePages($name, $structuredList, $i, $deep + 1);
				
				if($child) $pages['pages'][]=$child;
			}
		}
		
		return $pages;
			
	}
}