<?php
namespace Menu\Entity;

class MenuItem implements \Application\TreeObjectInterface
{
	/* 
    * Идентификатор пункта 
    */
    public $id;
    
    /* 
    * Название пункта 
    */
    public $label;
    
    /* 
    * Идентификатор маршрута 
    */
    public $route_id;
    
    /*
    * Идентификатор меню 
    */
    public $parent_menu_id;
    
    /*
    * Идентификатор родительского пункта 
    */
    public $parent_item_id;
    
    /* 
    * Порядок сортировки
    */
    public $ord;
    
    /* 
    * Активность пункта 
    */
    public $is_active;
    
    /* 
    * uri адрес
    */
    public $uri;
	
	/**
	* Тип пункта меню (Url, страница)
	*/
	public $type;
	
    
    /* 
    * флаг, открывать в новом окне 
    */
    public $blank;

    public $icon_class;

    public $icon_img;

    /* 
    * дочерние пункты меню 
    */
    public $children;
    	
	public function exchangeArray($data)
    {
		$class = get_class($this);
		foreach($data as $k=>$v) if(property_exists($class, $k)) $this->$k = $v;
	}
	
	public function getArrayCopy()
	{
		return (array)$this;
	}
    
    public function getParentId()
    {
        return $this->parent_item_id;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setChildren($children)
    {
        $this->children = $children;
    }
    
    public function getChildren()
    {
        return $this->children;
    }
}
	