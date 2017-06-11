<?php
namespace Settings\Factory\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SettingsFormFactory implements FactoryInterface{

    protected $options;

    public function setCreationOptions( array $options )
    {
        $this->options = $options;
    }

    public function createService(ServiceLocatorInterface $serviceLocator){
        //$form  = new SettingsForm();
        $form = new \Zend\Form\Form();
        return $form;
    }
}