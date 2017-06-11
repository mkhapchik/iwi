<?php
namespace Settings\Factory\Controller;

use Settings\Controller\SettingsController;
use Zend\Form\Form;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SettingsControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $settingsService = $realServiceLocator->get('Settings\Service\SettingsService');
        $settingsFormService = $realServiceLocator->get('Settings\Service\Form\SettingsFormService');
        return new SettingsController($settingsService, $settingsFormService);
    }
}