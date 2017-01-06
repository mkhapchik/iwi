<?php
namespace Account\Model;

class Account
{
	public $id;
    public $name;
    public $amount;
	public $comments;
	 
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
		$this->name     = (isset($data['name'])) ? $data['name'] : null;
		$this->amount     = (isset($data['amount'])) ? $data['amount'] : null;
		$this->comments     = (isset($data['comments'])) ? $data['comments'] : null;
    }
	
}