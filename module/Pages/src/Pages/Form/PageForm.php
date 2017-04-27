<?php
namespace Pages\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PageForm extends Form implements ServiceLocatorAwareInterface
{
	protected $sm;
	protected $routeId;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	/**
	* Конструктор
	* @param $name - имя формы
	*/
	public function __construct($name = 'page_form')
    {
        parent::__construct($name);
    }
	
	public function init($routeId=false, $actionList=array())
	{
		$this->routeId = $routeId;

        $this->setAttribute('action','');
		$this->setAttribute('method', 'post');
		
		$this->addFields();
		
		$this->addPermissionsFields($actionList);
		
		$this->addSubmit();
		
		$inputFilter = $this->__getInputFilter();
		$this->setInputFilter($inputFilter);
	}
	
	protected function addFields()
	{
		$this->add(array(
            'name' => 'is_active',
			'type'  => 'checkbox',
            'attributes' => array(
                
            ),
            'options' => array(
                'label' => 'Активности страницы',
            ),
        ));
		
		$this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Название страницы',
            ),
        ));
		
		$this->add(array(
            'name' => 'header',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Заголовок',
            ),
        ));
		
		$this->add(array(
            'name' => 'content',
			'type'=>'Textarea',
            'attributes' => array(
             
            ),
            'options' => array(
                'label' => 'Контент',
            ),
        ));
		
		$this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Title',
            ),
        ));
		
		$this->add(array(
            'name' => 'uri',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Url страницы',
            ),
        ));
		
		$this->add(array(
            'name' => 'descriotion',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Descriotion',
            ),
        ));
		
		$this->add(array(
            'name' => 'keywords',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Keywords',
            ),
        ));
	}
	
    public function getSelectedRoles()
    {
        return $this->roles;
    }
    
    public function setSelectedRoles($roles)
    {
        $this->roles = $roles;
    }
    
	protected function addPermissionsFields($actionList=array())
	{
		/*
        if($this->routeId)
		{
			$roles = $this->sm->get('PermissionsTable')->getNonRulesRoles($this->routeId);
		}
		else
		{
			$roles = $this->sm->get('RoleTable')->getGuide();
		}
        */
		$selectedRoles = $this->getSelectedRoles();
		if(count($selectedRoles)>0)
		{
            $this->add(array(
                'name' => 'group',
                'type' => 'Select',
                'attributes' => array(
                   'id'=>'NonRulesRoles'
                ),
                'options' => array(
                    'label' => 'Права доступа',
                    'value_options' => $selectedRoles,
                    'empty_option' => '--- Выберите роль ---',
                    'disable_inarray_validator' => true,
                ),
            ));
		}
		
		$permissionsFieldset = $this->sm->get('PermissionsFieldset');
		$permissionsFieldset->init($actionList);
		
		$this->add(array(
            'name' => 'permissions',
			'type' => 'Zend\Form\Element\Collection',
			'attributes' => array(
				'id'=>'permissions'
			),
            'options' => array(
                'use_as_base_fieldset' => false,
				'count' => 0,
                'should_create_template' => true,
                //'allow_add' => true,
				/*
				'target_element' => array(
                    'type' => 'Transactions\Form\TransactionFieldset'
                )
				*/
				//'target_element' => new PermissionsFieldset($this->sm)
				'target_element' => $permissionsFieldset
				
            )
        ));
	}
	
	protected function addSubmit()
	{
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $this->routeId ? 'Сохранить' : 'Добавить',
                'id' => 'submitbutton',
            ),
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
			'name'     => 'id',
			'required' => true,
			'filters'  => array(
				array('name' => 'Int'),
			),
		)));
        */
        
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
                           NotEmpty::IS_EMPTY => 'Поле обязательно для заполнения' 
                        ),
                    ),
                    'break_chain_on_failure' => true,
				),
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'max'      => 40,
						'messages' => array(
							StringLength::TOO_LONG => 'Длина поля не должна превышать %max% символов'
						)
					),
				),
				
			),
		)));
		
		
		return $inputFilter;
	}
	
}