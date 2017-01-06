<?php
namespace Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Exception;

class PermissionsService implements ServiceLocatorAwareInterface
{
	protected $sm;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
    
    public function savePermissions($routeId, $permissions, $allowedActionsKeys)
    {
        $user = $this->sm->get('User');
        
        $roleTable = $this->sm->get('RoleTable');
        $permissionsTable = $this->sm->get('PermissionsTable');
        
        $selectAllowedRoles = $roleTable->createSelectAllowedRolesByUserId($user->id);
        
        $permissionsTable->denyRoute($routeId, $selectAllowedRoles, $allowedActionsKeys);
			
        $except_roles = array();
       
        if(is_array($permissions) && count($permissions)>0)
        {
            foreach($permissions as $permission)
            {
                $actions = $permission->getActions();
                if(is_array($actions) && count($actions)>0)
                {
                    $except_roles[]=$permission->roleId;
                    foreach($actions as $action)
                    {
                        $permissionsTable->savePermission($routeId, $permission->roleId, null, $action, 1);
                    }
                }
            }
        }
        
                
        $permissionsTable->deletePermissionsForRole($routeId, $except_roles, $selectAllowedRoles, $allowedActionsKeys);
    }
	
}