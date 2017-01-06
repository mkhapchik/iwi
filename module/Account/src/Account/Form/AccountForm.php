<?php
namespace Account\Form;
 
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;
use Zend\Validator\NotEmpty;

 
class AccountForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('account');
        $this->setAttribute('method', 'post');
       
		$this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		
		$this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Название',
            ),
        ));
		
		$this->add(array(
            'name' => 'amount',
            'attributes' => array(
                'type'  => 'text',
				'class'=>'currency'
            ),
            'options' => array(
                'label' => 'Сумма на счете',
            ),
        ));  
	  
		$this->add(array(
            'name' => 'comments',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => 'Комментарий',
            ),
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
						'max'      => 10,
						'messages' => array(
							//StringLength::TOO_LONG => 'qwe %min% %max%'
						)
					),
				),
				
			),
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'amount',
			'required' => false,
			'filters'  => array(
				//array('name' => 'Float'),
			),
			'validators' => array(
				//array('name' =>'NotEmpty'),
			),
			
		)));
		
		$inputFilter->add($factory->createInput(array(
			'name'     => 'comments',
			'required' => false,
			'filters'  => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min'      => 1,
						'max'      => 100,
						
					),
					
				),
			),
			
		)));
		
		return $inputFilter;
	}
	
}