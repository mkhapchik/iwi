<?php
namespace Users;

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
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'Users\Form\UserForm'=>function($sm){
					return new \Users\Form\UserForm();
				},
				'Users\Model\UsersRolesMapTable'=>function($sm){
					return new \Users\Model\UsersRolesMapTable('users_roles_map');
				},
                'Users\Model\PermissionsRolesModel'=>function($sm){
                    return new \Users\Model\PermissionsRolesModel('permissions_roles');
                },
                'Users\Form\RoleForm'=>function($sm){
                    $roleForm = new \Users\Form\RoleForm(); 
                    $roleForm
                        ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject(new \Auth\Model\Role())
                    ;
                    
                    return $roleForm;
                },
                'Users\Form\RoleForm'=>function($sm){
                    $roleForm = new \Users\Form\RoleForm(); 
                    $roleForm
                        ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject(new \Auth\Model\Role())
                    ;
                    
                    return $roleForm;
                },
                'Users\Form\RolesFieldset'=>function($sm){
                    $rolesFieldset = new \Users\Form\RolesFieldset(); 
                    $rolesFieldset
                        ->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty())
                        ->setObject(new \Auth\Model\Role())
                    ;
                    
                    return $rolesFieldset;
                },
				
			),
		);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'UserHelper' => function($sm){
						$vh = new \Users\View\Helper\UserHelper();
						return $vh;
				},
			)
		);
	}
}
