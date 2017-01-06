<?php
namespace Users\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Where;

class PermissionsRolesModel extends TableGateway
{
	public function add($roleId, $pRoleId, $pUserId=null)
	{
		$data = array(
            'role_id'   => $roleId,
            'p_role_id' => $pRoleId,
            'p_user_id' => $pUserId
        );
        
        $this->insert($data);
	}
    
    public function del($roleIdList, $p_role_id)
	{
		$where = new Where();
        $where->In('role_id', $roleIdList)->equalTo('p_role_id', $p_role_id);
        $this->delete($where);
	}
}