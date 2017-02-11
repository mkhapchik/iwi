<?php
namespace Menu\Model;

//use Application\Model\AbstractTable;
use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\ResultSet\ResultSet;
use Menu\Entity\Menu;
use Menu\Entity\MenuItem;

class MenuItemsTable extends TableGateway
{ 	
    public function getItems($menuId)
    {
        $resultSet = $this->select(function (Select $select) use ($menuId) {
            $select->where->equalTo('parent_menu_id', $menuId);
            $select->order('ord ASC');
        });
       
        $items = array();
        foreach($resultSet as $item) $items[$item->id]=$item;
        
        return $items;
    }

    public function delItems($menuId){
        $this->delete(array('parent_menu_id'=>$menuId));
    }
    
    public function addMenuItem(MenuItem $item)
	{
		$data = array(
            'label' => $item->label,
            'is_active' => $item->is_active,
            'blank' => $item->blank,
            'parent_menu_id' => $item->parent_menu_id,
            'parent_item_id' => $item->parent_item_id,
            'ord' => $item->ord,
            'type' => $item->type,
            'route_id' => $item->route_id,
            'uri' => $item->uri,
            'icon_class' => $item->icon_class,
            'icon_img' => null
		);
        
		$this->insert($data);
		
		return $this->getLastInsertValue();
	}
    
    public function updateMenuItem(MenuItem $item)
    {
        $data = array(
            'label' => $item->label,
            'is_active' => $item->is_active,
            'blank' => $item->blank,
            'parent_menu_id' => $item->parent_menu_id,
            'parent_item_id' => $item->parent_item_id,
            'ord' => $item->ord,
            'type' => $item->type,
            'route_id' => $item->route_id,
            'uri' => $item->uri,
            'icon_class' => $item->icon_class,
            'icon_img' => null
        );

        /*
        if(is_array($item->icon_img) && array_key_exists('tmp_name', $item->icon_img)){
            $data['icon_img'] = $item->icon_img['tmp_name'];
        }else if(!isset($item->icon_img)){
            $data['icon_img'] = null;
        }
        */
        $this->update($data, array('id'=>$item->id));
    }
    
    public function delMenuItem($itemId)
    {
        $this->delete(array('id'=>$itemId));
    }
	
	public function getMenuItem($itemId){
		$resultSet = $this->select(array('id'=>$itemId));
		return $resultSet->current();
	}
    
    public function setItemPosition(MenuItem $item)
    {
        $data = array(
            'parent_item_id' => $item->parent_item_id,
            'ord'            => $item->ord,    
        );
        
        $where = array(
            'id' => $item->id
        );
        
        $this->update($data, $where);
    }
    

}