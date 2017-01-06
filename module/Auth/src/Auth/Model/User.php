<?php
namespace Auth\Model;

class User
{
	public $id=null;
    public $login;
    public $email;
	public $counter_failures;
	public $blocked=null;
	public $date_blocked=null;
	public $name;
	public $roles;
	private $is_super;
	public $temporary_block;
	public $date_temporary_block;
	
	 
    public function exchangeArray($data)
    {
        foreach($data as $k=>$v) 
		{
			switch($k)
			{
				case 'is_super' : 
					$this->is_super = (isset($data['is_super']) && $data['is_super']==1) ? 1 : null;
					break;
				default:
					if(property_exists($this, $k)) $this->$k=$v;
			}
		}
    }
	
	public function toArray()
	{
		return array(
			'id' => $this->id,     
			'login' => $this->login,  
			//'pwd' => $this->pwd,    
			'counter_failures'=> $this->counter_failures,   
			'blocked'=>$this->blocked,    
			'name'=>$this->name,    
			'is_super'=>$this->is_super, 
			'temporary_block'=>$this->temporary_block,
			'email'=>$this->email,
			'date_blocked'=>$this->date_blocked,
			'date_temporary_block'=>$this->date_temporary_block
		);
	}
	
	public function getArrayCopy()
    {
        return get_object_vars($this);
    }

	
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}
	
	public function getRoles()
	{
		return $this->roles;
	}
    
    public function getRolesIDs()
	{
		return is_array($this->roles) ? array_keys($this->roles) : array();
	}
	
	public function isBlockedFlag()
	{
		return (bool)$this->blocked;
	}
	
	public function isTemporarilyBlocked()
	{
		return $this->temporary_block && strtotime($this->temporary_block)>time();
	}
	
	public function isBlocked()
	{
		return $this->isBlockedFlag() || $this->isTemporarilyBlocked();
	}
	
	public function isAuth()
	{
		return (bool)$this->id;
	}
	
	public function isSuper()
	{
		return $this->is_super===1;
	}
	
	public function reset() 
	{
        foreach ($this as $key => $value) $this->$k=null;
    }
	
}