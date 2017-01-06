<?php
namespace Users\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RolesFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{
	protected $sm;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
    
    public function __construct()
	{
		parent::__construct();
	}
	
	public function init()
	{
		$this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
				'class'=>'role_id',
            ),
        ));
				
		$this->add(array(
            'name' => 'label',
            'attributes' => array(
                'type'  => 'text',
				'readonly'=>'readonly',
			),
            'options' => array(
               'label'=>true
            ),
        ));
		
		
	}
 
    /**
     * @return array
    */
    public function getInputFilterSpecification()
    {
        $sm = $this->getServiceLocator();
        
        return array(
			'id' => array(
                'required' => false,
				'validators' => array(
					array(
						'name' =>'Callback', 
						'options' => array(
							'callback' => function($value, $context=array()) use ($sm) {
								$roleTable = $sm->get('RoleTable');
                                $authUser =  $sm->get('User');
								return $roleTable->isAllowedRole($value, $authUser->id);
							},
							'messages'=>array(
								\Zend\Validator\Callback::INVALID_VALUE => 'Выбраны запрещённые роли'
							)
						)
					),
					
					
				)
            ),
			
			
		);
    } 
	
}