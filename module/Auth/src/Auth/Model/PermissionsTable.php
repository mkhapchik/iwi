<?php
namespace Auth\Model;

use Application\Db\TableGateway\TableGateway;
use \Auth\Entity\Permission;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;

class PermissionsTable extends TableGateway
{		
	
	
    /**
    *  Возвращает список прав доступа
    *  @param
    *  @param
    *  @param
    *  @param
    *  @return ARRAY - ключи - идентификатор роли, значение - массив 
                    'roleId' - идентификатор роли
					'routeId' - идентификатор маршрута
					'role_name' - имя роли
					'action' - массив действий
    */
	public function getPermissions($routeId, Select $selectAllowedRoles, $allowedActions=null)
	{
        /*
        SELECT p.id, p.role AS roleId, p.user AS userId, p.routeId, 
  GROUP_CONCAT(DISTINCT p.action SEPARATOR ',') AS actions,
  r.label AS roleName FROM permissions AS p 
  LEFT JOIN users_roles AS r ON r.id = p.role 
  WHERE p.routeId = '31' 
  AND p.role IN (
      SELECT ur.id AS id FROM users_roles AS ur 
      LEFT JOIN permissions_roles AS pr ON ur.id = pr.role_id 
      WHERE (pr.p_role_id IN (
                SELECT urm.role_id AS role_id 
                FROM users_roles_map AS urm 
                WHERE urm.user_id = '1') 
        OR pr.p_user_id = '1' 
        OR (SELECT u.is_super AS is_super FROM users AS u WHERE u.id = '1') = '1') 
      GROUP BY ur.id) 
  AND p.action IN ('view', 'edit', 'del', 'activeToggle', 'addMenu')
  AND p.allow=1
  GROUP BY p.role
  
        */
        
        $select = new Select();
		$select->columns(array('id', 'roleId'=>'role', 'userId'=>'user', 'routeId', 'actions'=>new Expression("GROUP_CONCAT(DISTINCT p.action SEPARATOR ',')")));
		$select->from(array('p' => $this->table));
		$select->join(array('r' => 'users_roles'), 'r.id = p.role', array('roleName'=>'label'), Select::JOIN_LEFT);
				
		$select->where->equalTo('p.routeId', $routeId);
        
        $selectAllowedRoles->columns(array('id'));
        $select->where->In('p.role', $selectAllowedRoles);
		
        if(is_array($allowedActions)) $select->where->in('p.action', $allowedActions);
        
        $select->where->equalTo('p.allow', 1);
        $select->group('p.role');
                
		$resultSet = $this->selectWith($select);
        $list = array();
        foreach($resultSet as $object) $list[] = $object;
        return $list;
	}
    
    public function getAllowedActions($routeId, $userId, $actionList)
    {
        $usersRolesMapSelect = new Select();
        $usersRolesMapSelect->from('users_roles_map');
        $usersRolesMapSelect->columns(array('role_id'));
        $usersRolesMapSelect->where->equalTo('user_id', $userId);
        
        $select = new Select();    
        $select->from($this->table);
        $select->columns(array('allow'=>new Expression("MIN(allow)"), 'action'));
        $select->where
            ->equalTo('routeId', $routeId)
            ->In('action', $actionList)
            ->nest()
                ->equalTo('user', $userId)
                ->or
                ->In('role', $usersRolesMapSelect)
            ->unnest();
        $select->group('action');
        $select->having->equalTo('allow', 1);
        
        $resultSet = $this->selectWith($select);
        $resultSet->setArrayObjectPrototype(new \ArrayObject);
       
        $actions = array_column($resultSet->toArray(), 'action');
        
        return $actions;
    }
	
	public function deletePermissionsForRole($routeId, $exceptRoles, Select $selectAllowedRoles, $allowedActions)
    {
        $selectAllowedRoles->columns(array('id'));
        
        $delete = new Delete($this->table);
        
        $delete->where
            ->equalTo('routeId', $routeId)
            ->in('role', $selectAllowedRoles)
            ->in('action', $allowedActions);
        
        if(is_array($exceptRoles) && count($exceptRoles)>0) $delete->where->notIn('role', $exceptRoles);
        
        $this->deleteWith($delete);
    }
	
	public function isAllowed($routeId, $roleIds, $userId, $action)
	{
		if(is_array($roleIds) && count($roleIds)>0) $roleIds = implode(',', $roleIds);
		else $roleIds = null;
		
		$res = $this->callProcedure('isAllowed', array($routeId, $roleIds, $userId, $action));
        
        if($res)
		{
            $cur = $res->current();
          	$allow = $cur['allow'];
		}
		else $allow = 0;
		
		return $allow;
	}
	
	public function denyRoute($routeId, Select $selectAllowedRoles, $allowedActions)
	{
		$selectAllowedRoles->columns(array('id'));
        
        $update = new Update($this->table);
        $update->set(array('allow'=>0));
        $update->where
            ->equalTo('routeId', $routeId)
            ->equalTo('allow', 1)
            ->In('role', $selectAllowedRoles);
        $update->where->In('action', $allowedActions);
        
        $this->updateWith($update);
	}
	
	public function savePermission($routeId, $roleId, $userId, $action, $allow)
	{
		$this->callProcedure('addPermissionsByRouteId', array(
			$routeId, $roleId, $userId, $action, $allow
		));
	}
}