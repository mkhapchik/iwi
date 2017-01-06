<?php
namespace Pages\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\Callback;

class PermissionsFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{
	protected $sm;
    protected $actionList;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
    
    public function setActionList($actionList)
    {
        $this->actionList = $actionList;
    }
    
    public function getActionList()
    {
        return $this->actionList;
    }
    
	public function init($actionList=array())
	{
		$this->setActionList($actionList);
        
        $this->add(array(
            'name' => 'roleId',
            'attributes' => array(
                'type'  => 'hidden',
				'class'=>'roleId',
            ),
        ));
				
		$this->add(array(
            'name' => 'roleName',
            'attributes' => array(
                'type'  => 'text',
				'readonly'=>'readonly',
				'class'=>'role',
            ),
            'options' => array(
               
            ),
        ));
	
		
		$this->addActions();
        
	}
    
    private function addActions()
    {
        $actionList = $this->getActionList();
        
        $privilege_value_options = array();
		
		foreach($actionList as $action=>$label)
		{
			$privilege_value_options[]=array(
				'value'=>$action,
				'attributes' => array(
					'class'=>"privilege",
					'checked'=>$action=='view' ? true : false,
				),				
				'label_attributes' => array(
					'class'  => "glyphicon action $action",
					'title'=>$label,
					'data-toggle'=>"tooltip",
					'data-placement'=>"auto top"
				),
			);
		}
		
		
		$this->add(array(
            'name' => 'actions',
            'type'  => 'multicheckbox',				
            'options' => array(
                'value_options'=>$privilege_value_options,
                'use_as_base_fieldset' => false,
                'use_hidden_element' => true
            ),
		));
    }
 
    /**
     * @return array
     */
    public function getInputFilterSpecification()
    {
       	$actionList = $this->getActionList();
        $sm = $this->getServiceLocator();
       
        return array(
			'actions' => array(
                'required' => false,
                'validators' => array(
                    /*
                    array(
                        'name' =>'InArray', 
                        'options' => array(
                            'haystack'=>array_keys($actionList),
                            'messages' => array(
                                InArray::NOT_IN_ARRAY => 'Запрещенные привилегии ' . '%value%'
                            ),
                        ),
                    ),
                    */
                    array(
						'name' =>'Callback', 
						'options' => array(
							'callback' => function($value, $context=array()) use ($actionList) {
								$result = true;
                                foreach($value as $v)
                                {
                                    $result = $result && in_array($v, array_keys($actionList));
                                }
                                return $result;
							},
							'messages'=>array(
								Callback::INVALID_VALUE => 'Выбраны запрещённые роли %value%'
							)
						)
					),
                    
                ),
            ),
            'roleId' => array(
                'validators' => array(
					array(
                        'name' =>'NotEmpty', 
                        'options' => array(
                            'messages' => array(
                               NotEmpty::IS_EMPTY => 'Роль не выбрана' 
                            ),
                        ),
                    ),
                    array(
						'name' =>'Callback', 
						'options' => array(
							'callback' => function($value, $context=array()) use ($sm) {
								$roleTable = $sm->get('RoleTable');
                                $authUser =  $sm->get('User');
								return $roleTable->isAllowedRole($value, $authUser->id);
							},
							'messages'=>array(
								Callback::INVALID_VALUE => 'Выбраны запрещённые роли'
							)
						)
					),
				)
            )
		);
    }
}