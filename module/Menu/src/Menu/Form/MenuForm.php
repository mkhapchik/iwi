<?php
namespace Menu\Form;
 
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;

class MenuForm extends \Pages\Form\PageForm
{
	public function __construct($name = 'menu_form')
    {
        parent::__construct($name);
    }
	
	protected function addFields()
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
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Код меню',
            ),
        ));
		
		$this->add(array(
            'name' => 'description',
			'type'  => 'textarea',
            'attributes' => array(
               
            ),
            'options' => array(
                'label' => 'Описание',
            ),
        ));
        
        
        $menuItemFieldset = $this->sm->get('Menu\Form\MenuItemFieldset');
        $menuItemFieldset->init();
        
		$this->add(array(
            'name' => 'items',
			'type' => 'Zend\Form\Element\Collection',
			'attributes' => array(
				'id'=>'items'
			),
            'options' => array(
                'use_as_base_fieldset' => false,
				'count' => 0,
                'should_create_template' => true,
                'allow_add' => false,
				//'target_element' => new PermissionsFieldset($this->sm)
				'target_element' => $menuItemFieldset
				
            )
        ));
        
	}
		
	protected function __getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
			'name' => 'group',
			'continue_if_empty' => true
		)));
        
		/*
		$inputFilter->add($factory->createInput(array(
			'name'     => 'name',
			'required' => true,
			'filters'  => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
                      'name' =>'NotEmpty', 
                        'options' => array(
                            'messages' => array(
                               NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения' 
                            ),
                        ),
				),		
			),
		)));
		*/

		return $inputFilter;
	}
	
}