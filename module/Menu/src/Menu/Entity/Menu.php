<?php
namespace Menu\Entity;

class Menu
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
	
	public function exchangeArray($data)
    {
		$class = get_class($this);
		foreach($data as $k=>$v) if(property_exists($class, $k)) $this->$k = $v;
	}
		
	public function getArrayCopy()
	{
		return (array)$this;
	}
	
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
	