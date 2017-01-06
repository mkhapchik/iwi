<?php
namespace Auth\Model;

class Route
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
	
    public function exchangeArray($data)
    {
        $this->id     			= (isset($data['id'])) 				? $data['id'] 			: null;		
		$this->route_name     	= (isset($data['route_name'])) 		? $data['route_name'] 	: null;		
		$this->route_param_id     	= (isset($data['route_param_id'])) 		? $data['route_param_id'] 	: null;		
		$this->layout     		= (isset($data['layout'])) 			? $data['layout'] 		: null;		
		$this->is_active     	= (isset($data['is_active'])) 		? $data['is_active'] 	: null;		
    }
	
	/**
	*  Метод проверяющий активность маршрута
	* @return BOOL true - активный, false - неактивный
	*/
	public function isActive()
	{
		return (bool)$this->is_active;
	}
}