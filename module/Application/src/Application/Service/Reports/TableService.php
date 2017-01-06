<?php
namespace Application\Service\Reports;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
*  Класс для формирование табличных отчетов
*/
class TableService implements ServiceLocatorAwareInterface
{
	protected $sm;
	protected $config;
	
	protected $routName;
	protected $routeParams;
	protected $routeOptions;
	
	protected $params;
	protected $sort;
	protected $filter;
	protected $paginator;
	protected $allow_sort;
	protected $allow_filter;
	
	protected $filterForm;
	
	public function __construct()
	{
		
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function init($limit=false, $allow_sort=false, $allow_filter=false, $filterForm=false)
	{
		$config = $this->sm->get('config');
		$this->config = $config['report'];
		
		$this->params = $this->sm->get('ControllerPluginManager')->get('params');
		$this->routName   = $this->sm->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
		$this->routeParams = array();
		$this->routeOptions = array();
		$this->routeOptions['query'] = $this->params->fromQuery();
		
		$this->allow_sort	=	$allow_sort;
		$this->allow_filter	=	$allow_filter;
		
		$this->sort = $this->filterParams($this->params->fromQuery('sort', array()), $allow_sort);
		$this->filter = $this->filterParams($this->params->fromQuery('filter', null), $allow_filter);
			
		if($filterForm) 
		{
			$filterForm->setData(array('filter'=>$this->filter));
			$this->filterForm = $filterForm;
		}
		
		$this->limit = $limit ? $limit : $this->config['table']['countPerPage'];
	}
	
	private function filterParams($params, $allow=false)
	{
		$filter_params = array();
		
		if(is_array($params))
		{				
			foreach($params as $k=>$v)
			{
				if(!empty($v))
				{
					if($allow) if(in_array($k, $allow)) $filter_params[$k]=$v;
					else $filter_params[$k]=$v;
				}
			}
		}
		return $filter_params;
		/*
		if(is_array($allow) && is_array($params)) 
		{
			return array_intersect_key($params, array_flip($allow));	
		}
		else
		{
			return $params;
		}
		*/
	}
	
	public function getSort()
	{
		return $this->sort;
	}
	
	public function getFilter()
	{
		return $this->filter;
	}
	
	public function setPaginator($paginator)
	{
		$this->paginator = $paginator;
	}
	
	public function view()
	{
		if(!isset($this->paginator)) throw new \Exception('Paginator is not set!');
		
		$this->paginator->setCurrentPageNumber((int) $this->params->fromQuery('page', 1));
		$this->paginator->setItemCountPerPage((int) $this->params->fromQuery('count', $this->limit));

		$sortLinks = $this->getSortLinks($this->routeOptions, $this->routName, $this->routeParams);
		
		$params = array(
			'paginator' => $this->paginator,
			'routName' => $this->routName,
			'routeParams'=>$this->routeParams,
			'routeOptions'=>$this->routeOptions,
			'sortLinks'=>$sortLinks
		);
		
		//$this->filterForm->setData(array('filter'=>array('name'=>'test2')));
		
		if(isset($this->filterForm)) $params['filterForm'] = $this->filterForm;
		
		return $params;
	}
	
	
	
	/*
	protected function clearSort($querySort)
	{
		$list = $this->getSortList();
		if(is_array($list) && count($list)>0 && is_array($querySort))
		{
			return array_filter($querySort, function($var, $key) use ($list){
				if(in_array($key, $list) && in_array($var, array('asc', 'desc'))) return true;
				else return array();
			}, ARRAY_FILTER_USE_BOTH);
		}
		else
		{
			return array();
		}
	}
	
	protected function clearFilter($queryFilter)
	{
		$list = $this->getFilterList();
		if(is_array($list) && count($list)>0 && is_array($queryFilter))
		{
			return array_filter($queryFilter, function($var, $key) use ($list){
				if(in_array($key, $list))
				{
					if(is_array($var))
					{
						return null;
					}
					else return !empty($var);
				}
				else 
				{
					return null;
				}
			}, ARRAY_FILTER_USE_BOTH);
		}
		else
		{
			return null;
		}
	}
	*/
	private function getSortLinks($options, $routName, $routeParams)
	{
		if(is_array($this->allow_sort) && count($this->allow_sort)>0)
		{
			$sort_url = array();
			foreach($this->allow_sort as $name) $sort_url[$name] = $this->getSortItem($name, $options, $routName, $routeParams);
			
			return $sort_url;
		}
		else
		{
			return array();
		}
	}
	
	private function getSortItem($name, $options, $routName, $routeParams)
	{
		if(isset($options['query']['sort'][$name])) 
		{
			if($options['query']['sort'][$name]=='asc') 
			{
				$options['query']['sort'][$name] = 'desc';
				$dir = 'asc';
			}
			else
			{
				unset($options['query']['sort'][$name]);
				$dir = 'desc';
			}
		}
		else 
		{
			$options['query']['sort'][$name] = 'asc';
			$dir = '';
		}
		
		$viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
		$url = $viewHelperManager->get('url');
		
		return array(
			'url' => $url($routName, $routeParams, $options),
			'dir' => $dir,
		);
	}
}