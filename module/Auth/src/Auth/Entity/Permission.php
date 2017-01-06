<?php
namespace Auth\Entity;

class Permission
{
    public $roleId;
    
    public $routeId;
    
    public $roleName;
    
    public $actions;
    	
	public function exchangeArray($data)
    {
        $this->roleId = isset($data['roleId']) ? $data['roleId'] : null;
        $this->routeId = isset($data['routeId']) ? $data['routeId'] : null;
        $this->roleName = isset($data['roleName']) ? $data['roleName'] : null;
        
        $this->actions = array();
        
        if(isset($data['actions']))
        {
            if(is_array($data['actions']))  $this->actions = $data['actions'];
            else if(!empty($data['actions'])) $this->actions = explode(',', $data['actions']);
        }
	}
    /*
    public function toArray()
    {
        return $this;
    }
    */
	/*
	public function __get($name) 
	{
        return property_exists($this, $name) ? $this->$name : null;
    }
	
	public function getArrayCopy()
	{
		return (array)$this;
	}
   
    public function setActions($actions)
    {
        $this->actions = $actions;
    }
    */
    public function getActions()
    {
        return $this->actions;
    } 
    
}
	