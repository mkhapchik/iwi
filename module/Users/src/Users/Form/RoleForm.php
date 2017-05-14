<?php
namespace Users\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RoleForm extends Form implements ServiceLocatorAwareInterface
{
	protected $sm;
    protected $selRoles;
	
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
	public function __construct($name = 'role_form')
    {
        parent::__construct($name);
    }
    
    public function setSelectedRoles($selRoles)
    {
        $this->selRoles = $selRoles;
    }
    
    public function getSelectedRoles()
    {
        return $this->selRoles;
    }
	
	public function init()
	{
        $this->setAttribute('action', '');
        $this->setAttribute('method', 'post');
         
		$this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name' => 'form_name',
            'attributes' => array(
                'type'  => 'hidden',
                'value' => $this->getName()
            ),
        ));
		
		$this->add(array(
            'name' => 'label',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Название роли',
            ),
        ));
		
		$this->add(array(
            'name' => 'description',
            'type'  => 'textarea',
			'attributes' => array(
                
            ),
            'options' => array(
                'label' => 'Описание роли',
            ),
        ));
		
		
		
		$this->add(array(
            'name' => 'is_guest',
			'type'=>'Checkbox',
            'attributes' => array(
               
            ),
            'options' => array(
                'label' => 'Назначать автоматически всем пользователям',
            ),
        ));
		
		$this->add(array(
            'name' => 'is_registered',
			'type'=>'Checkbox',
            'attributes' => array(
               
            ),
            'options' => array(
                'label' => 'Назначать автоматически всем зарегистрированным пользователям',
            ),
        ));

        $this->add(array(
			'name' => 'select_roles',
			'type' => 'Select',
			'attributes' => array(
			  
			),
			'options' => array(
				'label' => 'Разрешённые роли',
				'value_options' => $this->getSelectedRoles(),
				'empty_option' => '--- Выберите роль ---',
				'disable_inarray_validator' => true,
			),
		));
		
        $rolesFieldset = $this->getServiceLocator()->get('Users\Form\RolesFieldset');
        $rolesFieldset->init();
        
		$this->add(array(
            'name' => 'roles',
			'type' => 'Zend\Form\Element\Collection',
			'attributes' => array(
				'id'=>'roles'
			),
            'options' => array(
				'count' => 0,
                'should_create_template' => true,	
				'target_element' => $rolesFieldset,
                //'use_as_base_fieldset' => true,
             
            )
        ));
        
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Сохранить',
                'id' => 'submitbutton',
            ),
        ));
		
		$inputFilter = $this->__getInputFilter();
		$this->setInputFilter($inputFilter);
	}
	
	
	private function __getInputFilter()
	{	
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'label',
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
                        'break_chain_on_failure' => true,
				),
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						//'min'      => 1,
						'max'      => 50,
						'messages' => array(
							StringLength::TOO_LONG => 'Длина поля должна быть меньше %max%'
						)
					),
				),
				
			),
		)));
        
        $inputFilter->add($factory->createInput(array(
			'name'     => 'select_roles',
			'required' => false,
		)));

		return $inputFilter;
	}
	
}