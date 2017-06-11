<?php
namespace Auth\Model;
use Application\Entity\AbstractEntity;

class Route extends AbstractEntity
{
	/**
	*  Идентификатор маршрута
	*/
	public $id;
	
	/**
	*  Имя маршрута
	*/
	public $route_name;
	
	/**
	*  Параметр id маршрута
	*/
	public $route_param_id;
	
	/**
	*  Шаблон дизайна
	*/
	public $layout;
	
	/**
	*  Флаг, является ли маршрут активным
	*/
	public $is_active;
	
	/**
	*  Метод проверяющий активность маршрута
	* @return BOOL true - активный, false - неактивный
	*/
	public function isActive()
	{
		return (bool)$this->is_active;
	}
}