<?php
namespace Menu\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\Callback;

class MenuItemFieldset extends Fieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface
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
        
	public function init()
	{
        $this->add(array(
            'name' => 'id',
            'type'  => 'hidden',
            'attributes'=>array(
                'class'=>'id'
            )
		));
        /*
        $this->add(array(
            'name' => 'label',
            'type'  => 'text',				
            'options' => array(
                'label' => 'Название',
            ),
		));
        */
        $this->add(array(
            'name' => 'parent_item_id',
            'type'  => 'hidden',				
            'options' => array(
               
            ),
            'attributes'=>array(
                'class'=>'parentId'
            )
		));
        
        $this->add(array(
            'name' => 'ord',
            'type'  => 'hidden',				
            'options' => array(
               'label' => 'Порядок',
            ),
            'attributes'=>array(
                'class'=>'ord'
            )
		));
        /*
        $this->add(array(
            'name' => 'is_active',
            'type'  => 'Checkbox',				
            'options' => array(
               'label' => 'Активность',
            ),
		));
        
        $this->add(array(
            'name' => 'blank',
            'type'  => 'Checkbox',				
            'options' => array(
               'label' => 'Открывать в новой вкладке',
            ),
		));
        */
        /*
        $this->add(array(
            'name' => 'uri',
            'type'  => 'text',				
            'options' => array(
               'label' => 'uri',
            ),
		));
       
       
        
        
        //route_id
        //parent_menu_id
        
        
        
        
        
       
       */
       
       
       
        
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
                     
                ),
            ),
            'parent_item_id' => array(
                'required' => false,
                'validators' => array(
					
				)
            ),
            'ord' => array(
                'required' => false,
                'validators' => array(
					
				)
            ),
            'is_active' => array(
                'required' => false,
                'validators' => array(
					
				)
            ),
            'blank' => array(
                'required' => false,
                'validators' => array(
					
				)
            ),
		);
    }
}