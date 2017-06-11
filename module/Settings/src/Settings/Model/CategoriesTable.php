<?php
namespace Settings\Model;

use Application\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class CategoriesTable extends TableGateway{

    public function getCategories(){
        $select = new Select();
        $select->columns(array('id', 'label', 'description', 'color'));
        $select->from(array('c'=>$this->table));
        $resultSet = $this->selectWith($select);
        return $resultSet;
    }

    public function getCategory(){

    }

    public function addCategory(){

    }

    public function updateCategory(){

    }

    public function delCategory(){

    }

}