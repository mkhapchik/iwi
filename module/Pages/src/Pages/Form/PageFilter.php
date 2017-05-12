<?php
namespace Pages\Form;
 
use Zend\Form\Form;
//use Zend\InputFilter\Factory as InputFactory;
//use Zend\InputFilter\InputFilter;
//use Zend\Validator\StringLength;
//use Zend\Validator\NotEmpty;

class PageFilter extends Form
{
	/**
	* Конструктор
	* @param $name - имя формы
	*/
	public function __construct($name = 'page_filter')
    {
        parent::__construct($name);
		$this->init();
    }
	
	public function init()
	{
        $this->setAttribute('action','');
        $this->setAttribute('method', 'get');
		
		$filter = new \Zend\Form\Fieldset('filter');
		$filter->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Название страницы',
            ),
        ));
		
		$filter->add(array(
            'name' => 'is_system',
			'type'  => 'checkbox',
            'attributes' => array(
               
            ),
            'options' => array(
                'label' => 'Системные страницы',
            ),
        ));

		$this->add($filter);
		
		//$inputFilter = $this->__getInputFilter();
		//$this->setInputFilter($inputFilter);
	}
	/*
	private function __getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

		return $inputFilter;
	}
	*/
}