<?php
namespace Menu\Factory\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Menu\Form\MenuItemForm;
use Menu\Form\MenuItemInputFilter;

class MenuItemFormFactory implements FactoryInterface{

    protected $options;

    public function setCreationOptions( array $options )
    {
        $this->options = $options;
    }

    public function createService(ServiceLocatorInterface $serviceLocator){
		$pageModel = $serviceLocator->get('Pages\Model\PageModel');
		$pages = $pageModel->getPagesGuide();
		
		$form  = new MenuItemForm();
		$form->setPages($pages);
		$form->init();
        $form->setInputFilter(new MenuItemInputFilter());

		return $form;
    }

}