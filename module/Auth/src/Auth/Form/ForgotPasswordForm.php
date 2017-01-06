<?php
namespace Auth\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Csrf;

class ForgotPasswordForm extends Form
{
	public function __construct($name='ForgotPassword')
	{
		parent:: __construct($name);
		$this->initForm();
	}
	
	protected function initForm()
	{
		$this->setAttribute('method', 'post');
		
		$this->add(array(
            'name' => 'pwd',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'Новый пароль',
            ),
        ));
		
		$this->add(array(
            'name' => 'pwd_confirm',
            'attributes' => array(
                'type'  => 'password',
            ),
			'options' => array(
                'label' => 'Подтверждение пароля',
            ),
			
        ));
		
		$this->add(array(
            'name' => 'close_session',
            'type'  => 'checkbox',
			'attributes' => array(
               'checked'=>'checked'
            ),
			'options' => array(
                'label' => 'Выйти из всех активных сессий',
            ),
			
        ));
		
		$this->add(array(
			 'name' => 'security',
			 'type' => 'Csrf',
			 'options' => array(
				'csrf_options' => array(
					'timeout' => 600
				)
			 )
		));
		
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Отправить',
            ),
        ));
		
		
		$inputFilter = $this->__getInputFilter();
		$this->setInputFilter($inputFilter);
	}
	
	protected function __getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name'     => 'pwd',
			'required' => true,
			'validators' => array(
				array(
						'name' =>'NotEmpty', 
                        'options' => array(
                           	'messages' => array(
                               NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения.' 
                            ),
                        ),
				),
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'max'      => 255,
						'messages' => array(
							StringLength::TOO_LONG => 'Длина пароля не должна превышать %max% символов'
						)
					),
				),
				
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'pwd_confirm',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'Identical',
					'options' => array(
						'token' => 'pwd',
					),
				),
			),
		)));

		return $inputFilter;
	}
	
}