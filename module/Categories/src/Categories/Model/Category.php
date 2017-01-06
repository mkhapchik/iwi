<?php
namespace Categories\Model;

class Category
{
	public $id;
	
	/**
	* Название категории
	*/
    public $name;
	
	/**
	* Тип 1 - доход, 0 - расход
	*/
    public $type;
	
	/**
	* Статистика употребления в процентах
	*/
	public $statistic;
	
	/**
	* Лимит в месяц
	*/
	public $amount_limit;
	
	/**
	* Сумма, потраченная за текущий месяц
	*/
	public $sum;
	
	/**
	* Переполнение за текущий месяц
	*/
	public $overflow;
	
	/**
	* Цвет категории
	*/
	public $color;
	
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
		$this->name     = (isset($data['name'])) ? $data['name'] : null;
		$this->type     = (isset($data['type'])) ? $data['type'] : null;
		$this->statistic     = (isset($data['statistic'])) ? $data['statistic'] : null;
		$this->amount_limit     = (isset($data['amount_limit'])) ? $data['amount_limit'] : null;
		$this->sum     = (isset($data['sum'])) ? $data['sum'] : null;
		$this->overflow     = (isset($data['overflow'])) ? $data['overflow'] : null;
		$this->color     = (isset($data['color'])) ? $data['color'] : null;
    }
	
}
?>