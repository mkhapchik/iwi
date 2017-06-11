<?php
namespace Auth\Model;
use Application\Entity\AbstractEntity;

class Role extends AbstractEntity
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
    
    public function getAllowedRoles()
    {
        return $this->roles;
    }
    
    public function setAllowedRoles($roles)
    {
        $this->roles = $roles;
    }
}