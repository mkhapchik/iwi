<?php
namespace Menu\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Delete;
use Menu\Entity\Menu;

class MenuTable extends TableGateway
{ 
	public function fetchMenu($name, $access_roles=array())
    {
		$name_cond = $name ? "AND m.name = '$name'" : '';
				
		if(count($access_roles)>0)
		{
			$roles_cond = 'AND (p.role IN(' . implode(',',$access_roles) . ') OR (p.role IS NULL AND i.route_id IS NULL))';	
		}
		else
		{
			$roles_cond = '';
		}

		/*
		SELECT i.*, m.name as menu_name, IFNULL(i.uri, a.uri) as uri, r.route_name, r.route_param_id
		FROM menu_items i 
		INNER JOIN menu m ON i.parent_menu_id=m.id 
		LEFT JOIN routes r ON i.route_id=r.id 
		LEFT JOIN permissions p ON i.route_id=p.routeId 
		LEFT JOIN aliases a ON r.id=a.route_id 

		WHERE i.is_active=1 AND m.is_active=1 AND (r.is_active=1 OR r.is_active IS NULL) 
		AND (p.action='view' OR p.action IS NULL) 
		AND (p.role IN(1,5) OR (p.role IS NULL AND i.route_id IS NULL))
		AND m.name = 'test' 

		GROUP BY i.id 
		ORDER BY i.ord asc, i.id asc 
		
		*/
		
		
		try
		{
			if(!isset($this->table['aliases'])) throw new \Exception('Aliases table not found.');
						
			$query = " SELECT i.*, m.name as menu_name, IFNULL(i.uri, a.uri) as uri, 
				r.route_name, r.route_param_id
				FROM {$this->table['menu_items']} i 
				INNER JOIN {$this->table['menu']} m ON i.parent_menu_id=m.id 
				LEFT JOIN routes r ON i.route_id=r.id
				LEFT JOIN permissions p ON i.route_id=p.routeId
				LEFT JOIN {$this->table['aliases']} a ON r.id=a.route_id
				WHERE i.is_active=1 AND m.is_active=1 
				AND (r.is_active=1 OR r.is_active IS NULL)
				AND (p.action='view' OR p.action IS NULL)
				$roles_cond
				$name_cond
				GROUP BY i.id
				ORDER BY i.ord asc, i.id asc
			";
		}
		catch(\Exception $e)
		{
			$query = " SELECT i.*, m.name as menu_name, 
				r.route_name, r.route_param_id
				FROM {$this->table['menu_items']} i 
				INNER JOIN {$this->table['menu']} m ON i.parent_menu_id=m.id 
				LEFT JOIN routes r ON i.route_id=r.id
				LEFT JOIN permissions p ON i.route_id=p.routeId
				WHERE i.is_active=1 AND m.is_active=1 
				AND (r.is_active=1 OR r.is_active IS NULL)
				AND (p.action='view' OR p.action IS NULL)
				$roles_cond
				$name_cond
				GROUP BY i.id
				ORDER BY i.ord asc, i.id asc
			";
		}
		
		$resultSet = $this->query($query);

		$resultSet = $resultSet->toArray();
		
        return $resultSet;
    }
	
	public function fetchAll($condition=null, $paginated=false, $sort=array('date_last_modification'=>'DESC'), $filter=null)
	{
		$select = new Select();
		$select->from($this->table['menu']);
		$where = new Where();
		if(is_array($condition))
		{
			foreach($condition as $k=>$v)
			{
				if(is_null($v)) $where->isNull($k);
				else $where->equalTo($k, $v);
			}
		}
		
		if(is_array($filter)) 
		{
			foreach($filter as $k=>$v) 
			{
				if(is_numeric($v)) $where->equalTo($k, $v);
				elseif(!empty($v)) $where->like($k, '%'.$v.'%');
			}
		}

		$select->where($where);
		$select->order($sort);
		
		if($paginated) 
		{
			$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter(), $this->resultSetPrototype);
			$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
			
			return $paginator;
        }
		else
		{
			$resultSet = $this->selectWith($select);
			return $resultSet->toArray();
		}
	}
	
	public function getMenu($id)
	{
		$select = new Select();
		$select->from($this->table['menu']);
		$select->where(array('id'=>$id));
		$resultSet = $this->selectWith($select);
		
		return $resultSet->current();
	}
	
    public function addMenu(Menu $menu)
	{
        $data = array(
            'name'  =>  $menu->name, 
            'label' =>  $menu->label,
            'description'   =>  $menu->description
		);
        
		$this->insert($data);
		
		return $this->getLastInsertValue();
	}
    
    public function updateMenu(Menu $menu)
    {
        $data = array(
            'name'  =>  $menu->name, 
            'label' =>  $menu->label,
            'description'   =>  $menu->description
        );

        $this->update($data, array('id'=>$menu->id));
    }

    public function delMenu($menuId){
        $menuId = (int)$menuId;
        $query = "DELETE FROM {$this->table['menu']} WHERE id=$menuId";
        $this->query($query);
    }

    public function setRoute($routeId, $menuId){
        $this->update(array('route_id' => $routeId), array('id'=>$menuId));
    }
    
    public function setActive($menuId)
    {
        $menuId = (int)$menuId;
        $this->update(array('is_active'=>1), array('id'=>$menuId));
    }
    
    public function unsetActive($menuId)
    {
        $menuId = (int)$menuId;
        $this->update(array('is_active'=>0), array('id'=>$menuId));
    }
    
    
	
}