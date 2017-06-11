<?php
namespace Settings\Form;

use Zend\Form\Form;

class SettingsForm extends Form
{
    public function __construct($name)
    {
        parent::__construct('settings');
    }
}