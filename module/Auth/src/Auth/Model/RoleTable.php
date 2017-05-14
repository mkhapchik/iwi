<?php
namespace Auth\Model;

use Application\Db\TableGateway\TableGateway;

use Zend\Db\Sql\Expression;
use Auth\Model\Role;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class RoleTable extends TableGateway
{
	public function addRole(Role $role)
	{
		$data = array(
			'label'=>$role->label,
			'description'=>$role->description,
			'is_guest'=>$role->is_guest,
			'is_registered'=>$role->is_registered
		);
		$this->insert($data);
	}
	
	public function editRole(Role $role)
	{
		$id = (int)$role->id;
		$data = array(
			'label'=>$role->label,
			'description'=>$role->description,
			'is_guest'=>$role->is_guest,
			'is_registered'=>$role->is_registered
		);
		$this->update($data, array('id'=>$id));
	}
    
    public function getRole($id)
    {
        $id = (int)$id;
        $resultSet = $this->select(array('id'=>$id));
        return  $resultSet->current();
    }
    
   

    /**
	* Получение полного списка ролей пользователя без учета привилегий на управление ролями
	* @param $userId - идентификатор пользователя
	* @param $auto_roles - флаг, включать автоматические роли, такие как гость и зарегистрированные пользователи
	* @return array - массив, ключи которого идентификаторы ролей, значения - массив полей роли
	*/
	public function getRolesByUserId($userId, $auto_roles=true)
	{
		$select = new Select();
		
		$select->from(array('r' => $this->table));
		$select->join(array('m' => 'users_roles_map'), 'r.id = m.role_id', array(), Select::JOIN_LEFT);
		$select->where
			->equalTo('m.user_id', $userId);
		if($auto_roles) $select->where->or->equalTo('r.is_guest', 1);
		if($userId && $auto_roles) $select->where->or->equalTo('r.is_registered', 1);
			
		$select->order(array('r.is_guest'=>'DESC'));	
	
		$resultset = $this->executeSelect($select);
	   		
		$roles = array();
		foreach($resultset as $role)  $roles[$role->id]=$role;
		
        return $roles;
	}

	/**
	* Получение объекта выборки разрешенных ролей пользователя 
	* @param $userId - идентификатор пользователя
    * @param $permRolesColumns - массив колонок для permissions_roles
	* @return SELECT $select - объект выборки разрешенных ролей пользователя
	*/
    public function createSelectAllowedRolesByUserId($userId, $permRolesColumns=array())
    {
        $userId = (int)$userId;
        
        $select = new Select();
        $select->columns(array('id', 'label', 'is_guest', 'is_registered', 'description'));
        $select->from(array('ur' => $this->table));
        $select->join(array('pr' => 'permissions_roles'), 'ur.id = pr.role_id', $permRolesColumns, Select::JOIN_LEFT);
        
        $select_map = new Select();
        $select_map->columns(array('role_id'));
        $select_map->from(array('urm'=>'users_roles_map'));
        $select_map->where(array('urm.user_id'=>$userId));
        
        $select_super = new Select();
        $select_super->columns(array('is_super'));
        $select_super->from(array('u'=>'users'));
        $select_super->where(array('u.id'=>$userId));
        
        $where = new Where();
        $where->nest
            ->in('pr.p_role_id', $select_map)
            ->or
            ->equalTo('pr.p_user_id', $userId)
            ->or
            ->equalTo($select_super, 1)
            ->unnest;
            
        $select->where($where);
        $select->group('ur.id');
        
        return $select;
    }
    
    /**
    *  Получает разрешенные роли для управления для пользователя
    *  $userId - идентификатор пользователя
    */
    public function getAllowedRolesForUser($userId)
    {
        $select = $this->createSelectAllowedRolesByUserId($userId);
        $select->columns(array('id', 'label'));
        $resultSet=$this->selectWith($select);
        
        return $this->createGuide($resultSet);
    }
    
    public function getRolesPaginator($authId, $sort, $filter=array())
	{
		$select = new Select($this->table);
				
        if(is_array($filter)) $select->where($filter);
		
        if(is_array($sort) && count($sort)>0)$select->order($sort);
        else $select->order(array('is_guest'=>'DESC','is_registered'=>'DESC', 'label'=>'ASC'));
        
        $selectAllowedRoles = $this->createSelectAllowedRolesByUserId($authId);
        $selectAllowedRoles->columns(array('id'));
        
        $select->where->In('id', $selectAllowedRoles);
		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter(), $this->getResultSetPrototype());
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);

		return $paginator;       
	}
    
    /**
    * Проверяет, является ли роль разрешенной
    * @param $roleId - идентификатор проверяемой роли
    * @param $authUserId - идентификатор авторизованного пользователя
    * @return BOOL: true - роль разрешена, false - не разрешена
    */
    public function isAllowedRole($roleId, $authUserId)
    {
        $roleId = (int)$roleId;

        $select = $this->createSelectAllowedRolesByUserId($authUserId);
        $select->columns(array('c' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $select->where->equalTo('ur.id', $roleId);
        $resultSet = $this->selectWith($select);
        
        $resultSet=$this->selectWith($select);
        $resultSet->setArrayObjectPrototype(new \ArrayObject());
        $current = $resultSet->current();
       
        if($current && isset($current['c']) && $current['c']) $result = true;
        else $result = false;
       
        return $result;
    }
    
    /**
    *  Получение разрешенных ролей, не связанных с пользователем
    *  @param $userId - идентификатор пользователя, для которого получаем роли
    *  @param $authUserId - идентификатор авторизованного пользователя, участвует в условии получения разрешенных ролей
    *  @return array: ключ - id роли, значение - название роли
    */
    public function getSelectedRolesForUserId($userId, $authUserId)
    {
        $select = $this->createSelectAllowedRolesByUserId($authUserId);
        $select->columns(array('id', 'label', 'is_guest', 'is_registered'));
        
        $select_map = new Select();
        $select_map->columns(array('role_id'));
        $select_map->from(array('urm'=>'users_roles_map'));
        $select_map->where(array('urm.user_id'=>$userId));
                
        $select->having
            ->notIn('ur.id', $select_map)
            ->notEqualTo('is_guest', 1)
            ->notEqualTo('is_registered', 1);
            
        $select->order('ur.id');
       
        $resultSet = $this->selectWith($select);

        return $this->createGuide($resultSet);
    }
    
    /**
    *  Получение списка установленных ролей пользователя с учетом привилегий управления ролями
    *  @param $userId
    */
    public function getSetAllowedRolesForUser($userId, $authUserId)
    {
        $select = $this->createSelectAllowedRolesByUserId($authUserId);
        
        $select_map = new Select();
        $select_map->columns(array('role_id'));
        $select_map->from(array('urm'=>'users_roles_map'));
        $select_map->where(array('urm.user_id'=>$userId));
                
        $select->having
            ->In('ur.id', $select_map)
            ->notEqualTo('is_guest', 1)
            ->notEqualTo('is_registered', 1);
            
        $select->order('ur.id');
     
        $resultSet = $this->selectWith($select);
		
        return $resultSet;
    }
    
    /**
    *  Получение разрешенных ролей, не связанных с ролью
    *  @param $roleId - идентификатор роли, для которой получаем список разрешенных ролей
    *  @param $authUserId - идентификатор авторизованного пользователя, участвует в условии получения разрешенных ролей
    *  @return array: ключ - id роли, значение - название роли
    */
    public function getSelectedRolesForRole($roleId, $authUserId)
    {
        $select = $this->createSelectAllowedRolesByUserId($authUserId);
        $select->columns(array('id', 'label'));
        $select_permissions_roles = $this->createSelectPermissionsRoles($roleId, $authUserId);
        
        $select->having->notIn('ur.id', $select_permissions_roles)->notEqualTo('ur.id', $roleId);
        
        $resultSet = $this->selectWith($select);

        return $this->createGuide($resultSet);
    }

    /**
     * Получение списка ролей разрешенных для конкретной роли
     * @param $roleId
     * @param $authUserId
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getSetAllowedRolesForRole($roleId, $authUserId)
    {
        $select = $this->createSelectAllowedRolesByUserId($authUserId, array('p_role_id'));
        $select->columns(array('id', 'label', 'is_guest', 'is_registered', 'description'));
        $select_permissions_roles = $this->createSelectPermissionsRoles($roleId, $authUserId);
        
        $select->having->In('id', $select_permissions_roles)
            ->AND
            ->equalTo('p_role_id', $roleId);

        $resultSet = $this->selectWith($select);

        return $resultSet;
    }
    
    public function getSelectedRolesForPermissions($routeId, $authUserId)
    {
        $select = $this->createSelectAllowedRolesByUserId($authUserId);
        $select->columns(array('id', 'label'));
        
        $select_permissions = new Select();
        $select_permissions->columns(array('role'));
        $select_permissions->from(array('pr1'=>'permissions'));
        $select_permissions->where->equalTo('pr1.routeId', $routeId);
               
        $select->having->notIn('ur.id', $select_permissions);
        
        $resultSet = $this->selectWith($select);

        return $this->createGuide($resultSet);
    }
    
    /**
    *  
    */
    private function createSelectPermissionsRoles($roleId, $authUserId)
    {
        $select_permissions_roles = new Select();
        $select_permissions_roles->columns(array('role_id'));
        $select_permissions_roles->from(array('pr1'=>'permissions_roles'));
        $select_permissions_roles->where
            ->equalTo('pr1.p_role_id', $roleId)
            ->or
            ->equalTo('pr1.p_user_id', $authUserId);
          
        return $select_permissions_roles;
    }
    
    
    
    private function createGuide($resultSet)
    {
        $guide = array();
		if($resultSet) foreach($resultSet as $item) $guide[$item->id] = $item->label;
        
        return $guide;
    }
    
    /****************************************************************************************/
    
    /**
    *  Получение списка разрешенных ролей пользователя
    *  @param $userId  - идентификатор пользователя, для которого необходимо получить роли
    *  @return ARRAY - массив объектов ролей 
    
    public function getAllowedRolesByUserId($userId)
    {
        $userId = (int)$userId;
        
        $select = new Select();
        $select->columns(array('id', 'label', 'is_guest', 'is_registered', 'description'));
        $select->from(array('ur' => $this->table));
        $select->join(array('pr' => 'permissions_roles'), 'ur.id = pr.role_id', array(), Select::JOIN_LEFT);
        
        $select_map = new Select();
        $select_map->columns(array('role_id'));
        $select_map->from(array('urm'=>'users_roles_map'));
        $select_map->where(array('urm.user_id'=>$userId));
        
        $select_super = new Select();
        $select_super->columns(array('is_super'));
        $select_super->from(array('u'=>'users'));
        $select_super->where(array('u.id'=>$userId));
        
        $where = new Where();
        $where->in('pr.p_role_id', $select_map)
            ->or
            ->equalTo('pr.p_user_id', $userId)
            ->or
            ->equalTo($select_super, 1);
            
        $select->where($where);
        $select->group('ur.id');
        
       
        
    }
    */
	
    
    
    
    
    
    /** DEPRECATED
	*  Роли не участвующие в правилах доступа
	
	public function getNonRulesRoles($routeId, $allowedRoleIds)
	{
		if(!$this->checkAllowedRoleIds($allowedRoleIds)) return array();
        
        $q = "SELECT * FROM users_roles r WHERE r.id NOT IN(SELECT p.role FROM permissions p WHERE p.routeId=$routeId AND p.allow=1)";
        
        if($allowedRoleIds!==true)
        {
            $q.=" AND r.id IN(".implode($allowedRoleIds).")";
        }
        
        
		$rowset = $this->query($q);
		
		$guide = array();
		foreach($rowset as $row)
		{
			$guide[$row['id']] = $row['label'];
		}
		
		return $guide;
	}
    */
    private function getSelectObjAllowedRolesForUser($userId)
    {
        $userId = (int)$userId;
        
        $select = new Select();
        $select->from(array('r' => 'users_roles'));
        $select->columns(array('id', 'label'));
        $select->join(array('pr' => 'permissions_roles'), 'pr.role_id = r.id', array(), Select::JOIN_INNER);
        $select->join(array('m' => 'users_roles_map'), 'm.role_id = pr.p_role_id', array(), Select::JOIN_INNER);
        $select->join(array('u' => 'users'), 'u.id = m.user_id', array(), Select::JOIN_INNER);
        $select->where(array('u.id'=>$userId));
        $select->group('r.id');
        
        return $select;
    }
    
    
    /**
    *  Список ролей не связанных с ролью и разрешенных пользователю для указания связи
    *  @param $roleId - идентификатор роли, связанные роли с которой должны быть отброшены
    *  @param $allowedRoleIds - array || true - массив разрешенных идентификаторов или true для разрешения всех ролей
    
    public function getSelectedRolesForRole($roleId, $allowedRoleIds)
    {
        if(!$this->checkAllowedRoleIds($allowedRoleIds)) return array();
        
        $roleId = (int)$roleId;
        
        $select_use_role = new Select();
        $select_use_role->from(array('pr' => 'permissions_roles'));
        $select_use_role->columns(array('role_id'));
        $select_use_role->where(array('p_role_id'=>$roleId));
        
        $select = new Select();
        $select->columns(array('id', 'label'));
        $select->from(array('r' => $this->table));
        
        $where = new Where();
        if($allowedRoleIds!==true)
        {
            $where->In('r.id', $allowedRoleIds);   
        }
        
        $where->notIn('r.id', $select_use_role);
        
        $select->where($where);
       
        $resultSet = $this->selectWith($select);
        
        $result = array();
        while($role = $resultSet->current())
        {
            $result[$role->id] = $role->label;
            $resultSet->next();
        }
        
        return $result;
    }
    */
    /**
    *  Получает разрешенные роли для управления для роли
    *  $roleId - идентификатор роли
    */
    public function getAllowedRolesForRole($roleId, $allowedRoleIds)
    {
        if(!$this->checkAllowedRoleIds($allowedRoleIds)) return array();
        
        $roleId = (int)$roleId;
        
        $select = new Select();
        $select->from(array('r' => 'users_roles'));
        $select->columns(array('id', 'label'));
        $select->join(array('pr' => 'permissions_roles'), 'pr.role_id = r.id', array(), Select::JOIN_INNER);
        
        $where = new Where();
        $where->equalTo('pr.p_role_id', $roleId);
        
        if($allowedRoleIds!==true)
        {
            $where->In('r.id', $allowedRoleIds);   
        }

        $select->where($where);
        $select->group('r.id');
        
        $resultSet=$this->selectWith($select);
         
        return $resultSet->toArray();
    }
    
    
	
	
	
	private function checkAllowedRoleIds($allowedRoleIds)
    {
        if(($allowedRoleIds!==true && !is_array($allowedRoleIds)) || 
            (is_array($allowedRoleIds) && count($allowedRoleIds)==0))
        {
            $check = false;
        }
        else
        {
            $check = true;
        }
        
        return $check;
    }
	

}