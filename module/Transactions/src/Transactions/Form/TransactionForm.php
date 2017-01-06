<?php
namespace Transactions\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;
use Transactions\Form\TransactionFieldset;

class TransactionForm extends Form
{
	private $categories;
	private $accounts;
	
	/**
	* Конструктор
	* @param $type - тип транзакции 1 - доход, 0 - расход
	* @param $name - имя формы
	*/
	public function __construct($name = 'transaction_form')
    {
        parent::__construct($name);

		$this->categories = array();
		$this->accounts = array();
    }
	
	public function init($count=1)
	{
		$this->setAttribute('method', 'post');

		$this->add(array(
            'name' => 'transaction',
			'type' => 'Zend\Form\Element\Collection',
            'options' => array(
                'use_as_base_fieldset' => true,
				'count' => $count,
                'should_create_template' => true,
                'allow_add' => true,
				/*
				'target_element' => array(
                    'type' => 'Transactions\Form\TransactionFieldset'
                )
				*/
				'target_element' => new TransactionFieldset($this->categories, $this->accounts)
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
		
	}
	
	/*
	private function __getInputFilter()
	{
		
		$inputFilter = new InputFilter();
		$factory     = new InputFactory();

		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'id',
			'required' => true,
			'filters'  => array(
				array('name' => 'Int'),
			),
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
                               //NotEmpty::IS_EMPTY => 'qqq' 
                            ),
                        ),
				),
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min'      => 1,
						'max'      => 20,
						'messages' => array(
							//StringLength::TOO_LONG => 'qwe %min% %max%'
						)
					),
				),
				
			),
		)));
		
		
		return $inputFilter;
	}
	*/
	
	public function setCategories($categories)
	{
		$this->categories = $categories;
	}
	
	public function setAccounts($accounts)
	{
		$this->accounts = $accounts;
		
	}
	
}