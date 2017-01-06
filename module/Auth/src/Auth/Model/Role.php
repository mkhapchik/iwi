<?php
namespace Auth\Model;

class Role
{
	public $id;
    public $label;
	public $is_guest;
	public $is_registered;
    public $description;
    public $roles;
		 
    public function __construct()
	{
		$this->id = null;
		$this->label = null;
		$this->is_guest = null;
		$this->is_registered = null;
        $this->roles=array();
	}
	
	public function exchangeArray($data)
    {
		foreach($data as $k=>$v) if(property_exists($this, $k)) $this->$k=$v;
    }
	
	public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function getAllowedRoles()
    {
        return $this->roles;
    }
    
    public function setAllowedRoles($roles)
    {
        $this->roles = $roles;
    }
    
   
    

}