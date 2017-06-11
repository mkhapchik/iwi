<?php
namespace Settings\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SettingsService implements ServiceLocatorAwareInterface{

    protected $sm;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->sm;
    }

    public function getSettings(){
        $settingsTable = $this->getSettingsTable();
        return $settingsTable->getSettings();
    }

    public function getSetting($name){

    }

    public function addSetting(){

    }

    public function editSetting(){

    }

    public function delSetting(){

    }

    public function getCategories(){
        $categoriesTable = $this->getCategoriesTable();
        return $categoriesTable->getCategories();
    }

    public function addCategory(){

    }

    public function editCategory(){

    }

    public function delCategory(){

    }

    private function getSettingsTable(){
        return $this->sm->get('Settings\Model\SettingsTable');
    }

    private function getCategoriesTable(){
        return $this->sm->get('Settings\Model\CategoriesTable');
    }
}