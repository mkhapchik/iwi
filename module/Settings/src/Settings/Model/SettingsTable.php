<?php
namespace Settings\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class SettingsTable extends TableGateway{

    public function getSettings(){
        $select = new Select();
        $select->columns(array('id', 'name', 'label', 'value', 'description', 'active'));
        $select->from(array('settings'=>$this->table));
        $select->join(array('categories' => 'settings_categories'),
            'settings.category_id = categories.id',
            array('category_id'=>'id','category_label'=>'label', 'category_description'=>'description', 'category_color'=>'color'  ),
            Select::JOIN_LEFT);
        $select->order('category_id', SELECT::ORDER_ASCENDING);
        //$where = new Where();
        $resultSet = $this->selectWith($select);
        return $resultSet;
    }

    public function getSetting($name){

    }

    public function getSettingById($id){

    }

    public function addSetting(){

    }

    public function updateSetting(){

    }

    public function delSetting(){

    }

}