<?php
namespace Pages;
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
	
	public function getServiceConfig()
    {
		return array(
            'factories' => array(
				'Pages\Model\PageModel' => function($sm){
					$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype($sm->get('Page')); 
                    return new \Pages\Model\PageModel('pages', null, null, $resultSetPrototype);
				},
				'Pages\Form\PageForm' => function($sm){
					$pageForm = new \Pages\Form\PageForm(); 
                    $pageForm
                        ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject($sm->get('Page'))
                    ;
                    return $pageForm;
				},
				'PermissionsFieldset' => function($sm){
					$permissionsFieldset = new \Pages\Form\PermissionsFieldset();
                    $permissionsFieldset
                        ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject($sm->get('Permission'))
                    ;
                    
                    return $permissionsFieldset;
				},
                'Page'=> function($sm){
					return new \Pages\Entity\Page();
				},
                
				
			)
		);
    }
}
