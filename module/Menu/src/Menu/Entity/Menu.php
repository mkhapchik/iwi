<?php
namespace Menu\Entity;
use Application\Entity\AbstractEntity;

class Menu extends AbstractEntity
{
	public $id;
	
	/**
	*  Код меню, для вызова в шаблонах
	*/
	public $name;
	
	/**
	*  Название меню
	*/
	public $label;
	
	/**
	*  Описание меню
	*/
	public $description;
	
	/**
	*  Активность меню
	*/
	public $is_active;

    /**
     * Идентификатор маршрута
     */
    public $route_id;

    /**
    *  Права доступа 
    */
    public $permissions;
    
    /**
    *  Элементы меню
    */
    public $items;

	
	public function isActive()
	{
		return (bool)$this->is_active;
	}
    
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }
    
    public function getPermissions()
    {
        if(!isset($this->permissions)) throw new \Exception("permissions is not isset");
        return $this->permissions;
    }
    
    public function getItems()
    {
        return $this->items;
    }
    
    public function setItems($items)
    {
         $this->items = $items;
    }
}
	