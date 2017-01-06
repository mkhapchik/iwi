<?php
namespace Menu\Form;

use Zend\Form\Form;

class MenuItemForm extends Form
{
	private $pages;
	
	
	public function __construct($name = 'menu_item_form')
    {
        parent::__construct($name);
        $this->setAttribute('enctype','multipart/form-data');
		$this->pages = array();
		
    }
	
	public function setPages($pages){
		if(is_array($pages)){
			$this->pages = $pages;
			if(count($pages)>0) $this->hasPages = true;
		}
		
	}
	
	private function hasPages(){
		return (is_array($this->pages) && count($this->pages)>0);
	}
	
	private function getPages(){
		return is_array($this->pages) ? $this->pages : array();
	}
	
	public function init() 
	{
		$this->add(array(
            'name' => 'is_active',
			'type'  => 'Checkbox',
            'options' => array(
                'label' => 'Активность меню',
            ),
        ));
		
		$this->add(array(
            'name' => 'label',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Название',
            ),
        ));
		
		$this->add(array(
            'name' => 'blank',
			'type'  => 'Checkbox',
            'options' => array(
                'label' => 'Открывать в новой вкладке',
            ),
        ));
		
		
		$typeValues = array('url' => 'Url');
		if($this->hasPages()) $typeValues['page'] = "Страница";
		
		$this->add(array(
            'name' => 'type',
            'type'  => 'Select',
			'attributes' => array(
                
            ),
            'options' => array(
                'label' => 'Тип',
				'value_options' => $typeValues,
				'empty_option' => ''
            ),
        ));

		$this->add(array(
			'name' => 'page',
			'type'  => 'Select',
			'attributes' => array(
				
			),
			'options' => array(
				'label' => 'Страницы',
				'value_options' => $this->getPages(),
				'empty_option' => ''
			),
		));
		
		
		$this->add(array(
            'name' => 'uri',
            'attributes' => array(
                'type'  => 'url',
            ),
            'options' => array(
                'label' => 'Url',
            ),
        ));

		$this->add(array(
            'name' => 'icon_class',
			'attributes' => array(
                 'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Класс иконки',
				
            ),
        ));
		
		$this->add(array(
            'name' => 'icon_img',
			'attributes' => array(
                 'type'  => 'file',
            ),
            'options' => array(
                'label' => 'Изображение',

            ),
        ));
        
		$this->add(array(
            'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Сохранить',
            ),
            'options' => array(
            ),
        ));

	}
	
}