<?php
namespace Pages\Entity;

use Exception;

class Page
{
	public $id;
	
	/**
	*  системное имя
	*/
	public $name;
	
	/**
	* название
	*/
	public $label;
	
	/**
	* флаг удаления страницы
	*/
	public $is_delete=0;
	
    /**
	* флаг активности страницы
	*/
	public $is_active=0;
    
	/**
	* Title страницы
	*/
	public $title;
	
	/**
	*  Заголовок страницы
	*/
	public $header;
	
	/**
	* Основное содержимое, html текст
	*/
	public $content;
	
	/**
	*  Описание
	*/
	public $description;
  
	/**
	*  Ключевые слова
	*/
	public $keywords;
	
	/**
	* автор создания страницы
	*/
	public $author_id;
	
	/**
	* Имя автора страницы 
	*/
	public $author_name;
	
	/**
	* дата создания
	*/
	public $date_creation;
	
	/**
	* дата последнего изменения
	*/
	public $date_last_modification;
	
	/**
	* является ли страница системной
	*/
	public $is_system;
	
	/**
	*  шаблон страницы
	*/
	public $template;
	
	/**
	*  Идентификатор маршрута
	*/
	public $route_id;
	
	/**
	* имя маршрута
	*/
	public $route_name;
	
	/**
	* параметр маршрута
	*/
	public $route_param_id;

	/**
	*  Alias
	*/
	public $uri;
    
    /**
    *  Права доступа 
    */
    public $permissions;
	
	public function __construct()
	{
		$this->route_id = false;
		$this->uri = false;
	}
	
	public function exchangeArray($data)
    {
		foreach($data as $k=>$v) if(property_exists($this, $k)) $this->$k = $v;
	}

	public function getArrayCopy()
	{
		return (array)$this;
	}
	
	public function isActive()
	{
		return (bool)$this->is_active;
	}
    
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }
    
    public function getPermissions()
    {
        if(!isset($this->permissions)) throw new Exception("permissions is not isset");
        return $this->permissions;
    }
}
	