<?php
namespace Cron;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

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
	
	
	
	public function onBootstrap(MvcEvent $e)
    {		
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'Cron\Model\NasTable'=>function($sm){
					return new \Cron\Model\NasTable('nas');
				},
			),
		);
	}
	
}
