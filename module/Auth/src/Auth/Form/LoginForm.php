<?php
namespace Auth\Form;

use Application\Form\CaptchaForm;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;
use Zend\Validator\Csrf;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginForm extends CaptchaForm implements ServiceLocatorAwareInterface
{
	
	protected $sm;
	protected $authConfig;
	
	public function __construct($name, $authConfig)
	{
		$this->authConfig = $authConfig;
		parent::__construct($name);
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	protected function initForm()
	{
		//$this->setUseInputFilterDefaults(false); // отключение стандартных валидаторов
		$this->setAttribute('method', 'post');

		$this->add(array(
            'name' => 'login',
            'attributes' => array(
                'type'  => 'text',
				'class' => 'form-control required'
            ),
            'options' => array(
                'label' => 'Логин',
				
            ),
			
        ));
		
		$this->add(array(
            'name' => 'pwd',
            'attributes' => array(
                'type'  => 'password',
				'class' => 'form-control required'
            ),
			'options' => array(
                'label' => 'Пароль',
            ),
			
        ));
		
		$this->add(array(
			 'name' => 'security',
			 'type' => 'Csrf',
			 'options' => array(
				'csrf_options' => array(
					'timeout' => $this->authConfig['lifetime_inactive_session_sec']
				)
			 )
		));
		
	}
	
	protected function __getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

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
                               NotEmpty::IS_EMPTY => 'Поле "Логин" обязательно для заполнения.' 
                            ),
                        ),
				),
				/*
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'max'      => 50,
						'messages' => array(
							StringLength::TOO_LONG => 'Длина логина не должна превышать %max% символов'
						)
					),
				),
				*/
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'pwd',
			'required' => true,
			'validators' => array(
				array(
                      'name' =>'NotEmpty', 
                        'options' => array(
                            'messages' => array(
                               NotEmpty::IS_EMPTY => 'Поле "Пароль" обязательно для заполнения.' 
                            ),
                        ),
				),
				/*
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'max'      => 50,
						'messages' => array(
							StringLength::TOO_LONG => 'Длина пароля не должна превышать %max% символов'
						)
					),
				),
				*/
				
			),
		)));

		return $inputFilter;
	}
	
}