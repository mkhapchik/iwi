<?php
namespace Auth\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router;
use Auth\Service\Authorization as AuthorizationService;
use Exception;

class AuthListener extends AbstractListenerAggregate
{
    const isAuthorizeNameMethod = '_isAuthorize';
	const authorizeNameMethod = 'authorize';
	
	/**
    * Attach to an event manager
    *
    * @param  EventManagerInterface $events
    * @return void
    */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'initAuth'));
    }

	public function initAuth($e)
	{
		$sm = $e->getApplication()->getServiceManager();
		
		$AuthorizationService = $sm->get('AuthorizationService');
		if($e->getRouteMatch()->getMatchedRouteName()!='auth/timeout')
		{
			$AuthorizationService->updateLastActivity();
		}
		
		$controller = $this->getController($e);
		$codeAccess = AuthorizationService::CODE_ACCESS_IS_ALLOWED;
	
		if($this->isAuthorize($e))
		{
			$authorize = self::authorizeNameMethod; 
			if($controller && method_exists($controller, $authorize))
			{
				$codeAccess = $controller->$authorize($e);
			}
			else
			{
				$codeAccess = $AuthorizationService->checkAccess($e);
			}
		}
				
		if($codeAccess != AuthorizationService::CODE_ACCESS_IS_ALLOWED)
		{
			$this->setRouteParams($codeAccess, $e);
		}
	}
	
	public function isAuthorize($e)
	{
		$controller = $this->getController($e);
		
		$_isAuthorizeMethod = self::isAuthorizeNameMethod;
		
		if($controller && method_exists($controller, $_isAuthorizeMethod)) $isAuthorize = $controller->$_isAuthorizeMethod();
		else $isAuthorize = true;
		
		return $isAuthorize;
	}
	
	public function getController($e)
	{
		$controller = false;
		
		$serviceManager = $e->getApplication()->getServiceManager();
		$routeMatch = $e->getRouteMatch();
		
		if($routeMatch)
		{
		
			$__NAMESPACE__ = $routeMatch->getParam('__NAMESPACE__', '');
			$controllerName = $routeMatch->getParam('controller', false);
					
			if($controllerName)
			{
				$controller_index = !empty($__NAMESPACE__) && strpos($controllerName, $__NAMESPACE__)===0 ? $controllerName  : "$__NAMESPACE__\\$controllerName";
				$controller = $serviceManager->get('ControllerManager')->get($controller_index);
			}
		
		}
				
		return $controller;
	}

	private function setRouteParams($codeAccess, $e)
	{
		$serviceManager = $e->getApplication()->getServiceManager();
		
		$is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
		$is_loginform = (isset($_REQUEST['form_name']) && $_REQUEST['form_name']=='loginForm');
		
		if($is_ajax && !$is_loginform)
		{
			$AuthorizationService = $serviceManager->get('AuthorizationService');
			$message = $AuthorizationService->getMessageByCode($codeAccess);
			echo json_encode(array('is_success'=>0, 'codeAccess'=>$codeAccess, 'message'=>$message));
			exit();
		}
		else
		{
			$routeMatch = $e->getRouteMatch();
			$routeMatch->setParam('__NAMESPACE__', 'Auth\Controller');
			$routeMatch->setParam('__CONTROLLER__', 'Authentication');
			$routeMatch->setParam('controller', 'Authentication');
			$routeMatch->setParam('action', 'login');
			$routeMatch->setParam('codeAccess', $codeAccess);
			$routeMatch->setParam('is_success', 0);
			//$routeMatch->setParam('is_ajax', 0);
		}
	}
}
