<?php
namespace Users\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use \Users\Form\RolesFieldset;

class UserForm extends Form implements ServiceLocatorAwareInterface
{
	protected $sm;
	protected $userId;
    protected $selectedRoles;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
    
    public function setSelectedRoles($selectedRoles)
    {
        $this->selectedRoles = $selectedRoles;
    }
    
    public function getSelectedRoles()
    {
        return $this->selectedRoles;
    }
	
	/**
	* Конструктор
	* @param $name - имя формы
	*/
	public function __construct($name = 'user_form')
    {
        parent::__construct($name);
    }
	
	public function init($userId=false)
	{
		$this->userId = $userId;
		$this->setAttribute('method', 'post');
		
		$this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Имя пользователя',
            ),
        ));
		
		$this->add(array(
            'name' => 'blocked',
			'type'  => 'Checkbox',
            'options' => array(
                'label' => 'Заблокировать пользоватея',
            ),
        ));
		
		$this->add(array(
            'name' => 'swich_disable_temporary_block',
			'type'  => 'checkbox',
			'attributes'=>array(
				'class'=>'swich-disable'
			),
			'options' => array(
            ),
        ));
				
		$this->add(array(
            'name' => 'temporary_block',
			'attributes'=>array(
				'type'  => 'text',
				'class'=>'form-control date_hm'
			),
			'options' => array(
                'label' => 'Закрыть доступ на время',
            ),
        ));
		
		
		
		$this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'E-mail',
            ),
        ));
		
		$this->add(array(
            'name' => 'login',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Логин',
            ),
        ));
		
		$this->add(array(
            'name' => 'pwd',
            'attributes' => array(
                'type'  => 'text',
				'class' => 'pwd'
            ),
            'options' => array(
                'label' => 'Пароль',
            ),
        ));

		
		
		$this->add(array(
            'name' => 'sendPwd',
			'type'=>'Checkbox',
            'attributes' => array(
               
            ),
            'options' => array(
                'label' => 'Выслать пароль на email',
            ),
        ));
		
		$this->add(array(
			'name' => 'select_roles',
			'type' => 'Select',
			'attributes' => array(
			  
			),
			'options' => array(
				'label' => 'Роли пользователя',
				'value_options' => $this->getSelectedRoles(),
				'empty_option' => '--- Выберите роль ---',
				'disable_inarray_validator' => true,
			),
		));
		
		//init();
		$rolesFieldset = new RolesFieldset();
        $rolesFieldset->setServiceLocator($this->getServiceLocator());
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
				'target_element' => $rolesFieldset
            )
        ));
		
		/*
		$this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
				
            ),
            'options' => array(
                'label' => 'Имя пользователя',
            ),
        ));
		*/
		

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
			'name'     => 'select_roles',
			'required' => false,
		)));

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
		
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'email',
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
					'name'=>'EmailAddress',
					'options' => array(
						'messages' => array(
						   \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Неверный формат email адреса'
						),
					),
				),
				array(
                    'name' => 'Db\NoRecordExists',
                    'options' => array(
                        'table' => 'users',
                        'field' => 'email',
                        'adapter'=>$this->sm->get('Zend\Db\Adapter\Adapter'),
						'exclude' => array(
							'field' => 'id',
							'value' => $this->userId
						),
						'messages' => array(
						   \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email адрес уже существует'
						),
                    ),
                ),
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'login',
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
                    'name' => 'Db\NoRecordExists',
                    'options' => array(
                        'table' => 'users',
                        'field' => 'login',
                        'adapter'=>$this->sm->get('Zend\Db\Adapter\Adapter'),
						'exclude' => array(
							'field' => 'id',
							'value' => $this->userId
						),
						'messages' => array(
						   \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Логин уже существует'
						),
                    ),
                ),
			),
		)));
		
		$user_pswd_opt = array(
			'name'     => 'pwd',
			'filters'  => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
		);
		
		if(!$this->userId)
		{
			$user_pswd_opt['validators'] = array(
				array(
					'name' =>'NotEmpty', 
					'options' => array(
						'messages' => array(
						   NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения' 
						),
					),
				),
			);
			$user_pswd_opt['required'] = true;
		}
		else $user_pswd_opt['required'] = false;
		
		$inputFilter->add($factory->createInput($user_pswd_opt));
		
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'swich_disable_temporary_block',
			'required' => false,
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'temporary_block',
			'required' => false,
			'filters'  => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' =>'Callback', 
					'options' => array(
						'callback' => function($value, $context=array()){
							return strtotime($value)!==false;
						},
						'messages'=>array(
							\Zend\Validator\Callback::INVALID_VALUE => 'Неверный формат даты и времени'
						)
					),
					'break_chain_on_failure' => true,
				),
				array(
					'name' =>'Callback', 
					'options' => array(
						'callback' => function($value, $context=array()){
							return strtotime($value)>time();
						},
						'messages'=>array(
							\Zend\Validator\Callback::INVALID_VALUE => 'Время блокировки должно быть больше текущего'
						)
					)
				),
			),
		)));
		
		
		
		
		return $inputFilter;
	}
	
}