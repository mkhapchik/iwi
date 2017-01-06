<?php
namespace Auth\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

class RouteListener extends AbstractListenerAggregate
{
    /**
    * Attach to an event manager
    *
    * @param  EventManagerInterface $events
    * @return void
    */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'initRoute'));
    }
	
	public function initRoute(MvcEvent $event)
	{
		$sm = $event->getApplication()->getServiceManager();
		$route = $sm->get('Route');
		
		$routesModel = $sm->get('RoutesTable');
		
		$routeMatch = $event->getRouteMatch();
		$routName = $routeMatch->getMatchedRouteName();
		$params = $routeMatch->getParams();
		$route_param_id = isset($params['id']) ? (int)$params['id'] : null;
		
		try
		{
			$request = $sm->get('Request');
			$urlService = $sm->get('Aliases\Service\Url');
			$uri = $urlService->makeUrl($request->getRequestUri());
			$aliasesModel = $sm->get('Aliases\Model\AliasesModel');
			$routeData = $aliasesModel->match($uri);
			
			if(!$routeData) throw new \Exception('');
			
		}
		catch(\Exception $e)
		{
			$routeData = $routesModel->getRoute($routName, $route_param_id);
		}
		
		$route->exchangeArray((array)$routeData);
	
		//if(!$route->id) trigger_error("Route '$routName' is not added to the database");
	}
}
