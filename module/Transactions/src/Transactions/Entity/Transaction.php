<?php
namespace Transactions\Entity;

class Transaction
{
	public $id;
	
	/**
	* дата операции
	*/
	public $date;
	
	/**
	* сумма операции
	*/
	public $amount;
	
	/**
	* id категории
	*/
	public $categories_id;
	
	/**
	* id счета
	*/
	public $account_id;
	
	/**
	* комментрарий
	*/
	public $comment;
	
	/**
	* 1 - доход, -1 - расход
	*/	
	public function exchangeArray($data)
	{
		foreach($data as $var=>$value) 
		{
			if($var=='date') $this->$var = date('Y-m-d', strtotime($value));
			else $this->$var = $value;
		}
		if(!array_key_exists('id', $data)) $this->id = false;
	}
}
?>