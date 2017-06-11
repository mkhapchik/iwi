<?php
namespace Settings;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
	public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


	public function getServiceConfig()
    {
		return array(
            'factories' => array(
                'Settings\Form\SettingsForm' => 'Settings\Factory\Form\SettingsFormFactory',
                'Settings\Model\SettingsTable' => function($sm){
                    $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Settings\Entity\Setting());

                    return new \Settings\Model\SettingsTable('settings', null, null, $resultSetPrototype);
                },
                'Settings\Model\CategoriesTable' => function($sm){
                    $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Settings\Entity\Category());

                    return new \Settings\Model\CategoriesTable('settings_categories', null, null, $resultSetPrototype);
                },
			),
            'invokables'=>array(
                'Settings\Service\SettingsService' => 'Settings\Service\SettingsService',
                'Settings\Service\Form\SettingsFormService' => 'Settings\Service\Form\SettingsFormService',
            )
		);
    }
}
