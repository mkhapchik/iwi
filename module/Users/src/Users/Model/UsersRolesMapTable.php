<?php
namespace Users\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Where;

class UsersRolesMapTable extends TableGateway
{
	public function add($data)
	{
		$this->insert($data);
	}
    
    public function del($user_id=null, $roleIdList=null, Select $condAllowRoles=null)
    {
        if(!$user_id && !$roleIdList && !$condAllowRoles) return true;
        
        $where = new Where();
        
        if($user_id)
        {
            $where->equalTo('user_id', $user_id);
        }
        
        if($roleIdList)
        {
            if(is_array($roleIdList) && count($roleIdList)>0) $where->in('role_id', $roleIdList);
            else if(is_numeric($roleIdList)) $where->equalTo('role_id', $roleIdList);
        }
        
        if($condAllowRoles && $condAllowRoles instanceof Select)
        {
            $condAllowRoles->columns(array('id'));
            
            $selAllow = new Select();
            $selAllow->from(array('S'=>$condAllowRoles));
            $selAllow->columns(array('id'));
            
            $where->in('role_id', $selAllow);
        }
        
        $delete = new Delete();
        $delete->from($this->table);
        $delete->where($where);
        
        return $this->deleteWith($delete);
    }
}