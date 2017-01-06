<?php
namespace Menu;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

	public function onBootstrap(MvcEvent $e)
    {		
		/* detach!!!
		$app = $e->getApplication();
		$events = $app->getEventManager();
		$services = $app->getServiceManager();

		$authListener = $services->get('AuthListener');
		$authListener->detach($events);
		*/
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
				'Menu\Model\MenuTable' => function($sm){
					$alias_table = $sm->get('Aliases\Model\AliasesModel')->getTable();
					
					$table = array(
						'menu'=>'menu', 
						'menu_items'=>'menu_items',
						'aliases'=>$alias_table
					);
					
					$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Menu\Entity\Menu());
					
					return new \Menu\Model\MenuTable($table, null, null, $resultSetPrototype);
				},
                'Menu\Model\MenuItemsTable' => function($sm){
					$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Menu\Entity\MenuItem());
					
					return new \Menu\Model\MenuItemsTable('menu_items', null, null, $resultSetPrototype);
				},
				'Menu\Service\Menu' => function($sm){
					return new \Menu\Service\Menu();
				},
				'Menu\Form\MenuForm' => function($sm){
					return new \Menu\Form\MenuForm();
				},
                'Menu\Form\MenuItemFieldset' => function($sm){
					$menuItemFieldset = new \Menu\Form\MenuItemFieldset();
                    $menuItemFieldset ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject(new \Menu\Entity\MenuItem());
                        
                    return $menuItemFieldset;
				},
				'Menu\Form\MenuItemForm' => 'Menu\Factory\Form\MenuItemFormFactory',
                'Menu\Service\MenuItemFormService' => function($sm){
                    return new \Menu\Service\MenuItemFormService();
                }
			)
		);
    }
	
	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'MenuHelper' => function($helpers){
						$vh = new \Menu\View\Helper\MenuHelper();
						return $vh;
				}
			)
		);
	}
}
