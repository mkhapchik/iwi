<?php
namespace Aliases;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getServiceConfig()
    {
		return array(
            'factories' => array(
				'Aliases\Model\AliasesModel' => function($sm){
					return new \Aliases\Model\AliasesModel('aliases');
				},
				'Aliases\Service\Url' => function($sm){
					return new \Aliases\Service\Url();
				},
				
			)
		);
    }
	
	/**
	* Обработчик события "начальная загрузка"
	*/
	public function onBootstrap(MvcEvent $e)
    {		
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		//$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'addAliasRoutes'), 3);
		$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'addAliasRoute'), 3);
		
    }
	
	public function addAliasRoute(MvcEvent $e)
	{
		$app = $e->getApplication();
		$sm = $app->getServiceManager();
		
		
		$urlService = $sm->get('Aliases\Service\Url');
		$request = $sm->get('Request');
		$uri = $urlService->makeUrl($request->getRequestUri());
				
		$aliasesModel = $sm->get('Aliases\Model\AliasesModel');
		$routeData = $aliasesModel->match($uri);
		
		if($routeData )
		{
			$route_name = $routeData['route_name'];
			$params = array('id'=>$routeData['route_param_id']);
						
			$router = $e->getRouter();
			
			if($router->hasRoute($route_name))
			{
				$options = $this->getRouterOptions($sm, $route_name);
				
				if(is_array($params)) $options['defaults'] = array_merge($options['defaults'], $params);
				$options['route'] = $routeData['uri']."[/]";
								
				$route = \Zend\Mvc\Router\Http\Segment::factory($options);
				$router->addRoute($routeData['alias_name'], $route);
			}
		}
	}
	
	public function addAliasRoutes(MvcEvent $e)
	{
		$app = $e->getApplication();
		$sm = $app->getServiceManager();
		$AliasesModel = $sm->get('Aliases\Model\AliasesModel');
		$aliases = $AliasesModel->getAliases();
	
		if(is_array($aliases) && count($aliases)>0)
		{
			foreach($aliases as $alias)
			{
				$route_name = $alias['route_name'];
				$params = (array)json_decode($alias['route_params']);
							
				$router = $e->getRouter();
				
				if($router->hasRoute($route_name))
				{
					$options = $this->getRouterOptions($sm, $route_name);
					
					if(is_array($params)) $options['defaults'] = array_merge($options['defaults'], $params);
					$options['route'] = $alias['uri']."[/]";
									
					$route = \Zend\Mvc\Router\Http\Segment::factory($options);
					$router->addRoute($alias['alias_name'], $route);
				}
			}
		}
	}	
	
	private function getRouterOptions($sm, $route_name)
	{
		$config = $sm->get('config');
		$route_config = $config['router']['routes'];
		$route_name_list = explode('/', $route_name);
		$i=0;
		foreach($route_name_list as $name) 
		{
			if($i>0) $route_config = $route_config['child_routes'][$name];
			else $route_config = $route_config[$name];
			$i++;
		}
		
		$options = $route_config['options'];
		if(!isset($options['defaults'])) $options['defaults'] = array();
		
		return $options;
	}

}
