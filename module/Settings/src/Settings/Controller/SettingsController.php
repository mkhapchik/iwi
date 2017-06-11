<?php
namespace Settings\Controller;

use Pages\Controller\PageController;
use Settings\Service\SettingsService;
use Settings\Service\Form\SettingsFormService;

class SettingsController extends PageController
{
    private $settingsServise;
    private $settingsFormService;

    public function __construct(
        SettingsService $settingsServise,
        SettingsFormService $settingsFormService
    ) {
        $this->settingsServise = $settingsServise;
        $this->settingsFormService = $settingsFormService;
    }

    public function viewAction()
    {
        $settings = $this->settingsServise->getSettings();
        return array('settings' => $settings);
    }

    public function addSettingAction()
    {

    }

    public function editSettingAction()
    {

    }

    public function delSettingAction()
    {

    }

    public function getActionList()
    {
        return array_merge(parent::getActionList(), array(
            'addSetting' => 'Добавление настроек',
            'editSetting' => 'Редактирование настройки',
            'delSetting' => 'Удаление настройки'
        ));
    }

    private function getSettingsService()
    {

    }
}
