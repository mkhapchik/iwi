<?php
namespace Transactions\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Transactions\Entity\Transaction;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class TransactionFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($categories=false, $accounts=false)
    {
       parent::__construct();
	   if(!$categories) $categories=array();
	   if(!$accounts) $accounts = array();
	   
	   $this->categories = $categories;
	   $this->accounts = $accounts;
	   
       $this->init();
    }
	
	public function init()
	{
		$this->setHydrator(new ClassMethodsHydrator(false))->setObject(new Transaction());
		
		/*
		$this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		*/
		
		$this->add(array(
            'name' => 'date',
            'attributes' => array(
                'type'  => 'text',
				'class'=>'date',
				'value'=>date("d.m.Y"),
            ),
            'options' => array(
                'label' => 'Дата',
            ),
        ));
		
		$this->add(array(
            'name' => 'amount',
            'attributes' => array(
                'type'  => 'text',
				'class'=>'currency'
            ),
            'options' => array(
                'label' => 'Сумма',
            ),
        ));
		
		$this->add(array(
            'name' => 'categories_id',
            'type' => 'Select',
			'attributes' => array(
               //'value'=>3
			   'class'=>'categories'
            ),
            'options' => array(
                'label' => 'Категория',
				'value_options' => $this->categories,
				'empty_option' => '--- Выберите категорию ---',
            ),
			
        ));
		
		$this->add(array(
            'name' => 'account_id',
            'type' => 'Select',
			'attributes' => array(
                'class'=>'account'
            ),
            'options' => array(
                'label' => 'Счет',
				'value_options' => $this->accounts,
				'empty_option' => '--- Выберите счет ---',
            ),
        )); 
		
		$this->add(array(
            'name' => 'comment',
            'attributes' => array(
                'type'  => 'text',
				'class'=>'comment'
            ),
            'options' => array(
                'label' => 'Комментрарий',
            ),
        )); 
		
	}
 
    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
       	return array(
            'date' => array(
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
								\Zend\Validator\NotEmpty::IS_EMPTY => 'Поле обязательно для заполнения' 
							),
						),
					),
				)
            ),
			'amount'=>array(
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
								\Zend\Validator\NotEmpty::IS_EMPTY => 'Поле обязательно для заполнения' 
							),
						),
					),
				)
			),
			'categories_id'=>array(
				'required' => true,
				'filters'  => array(
					array('name' => 'Int'),
				),
				'validators' => array(
					array(
						'name' =>'InArray', 
						'options' => array(
							'haystack'=>array_keys($this->categories),
							'strict'=>true,
							'messages' => array(
								\Zend\Validator\InArray::NOT_IN_ARRAY => 'Поле обязательно для заполнения'
							)
						)
					),
				)
			),
			'account_id'=>array(
				'required' => true,
				'filters'  => array(
					array('name' => 'Int'),
				),
				'validators' => array(
					array(
						'name' =>'InArray', 
						'options' => array(
							'haystack'=>array_keys($this->accounts),
							'strict'=>true,
							'messages' => array(
								\Zend\Validator\InArray::NOT_IN_ARRAY => 'Поле обязательно для заполнения'
							)
						)
					),
				)
			),
			'comment'=>array(
				'required' => false,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
        );
		
    }
}