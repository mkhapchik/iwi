<?php
namespace Categories\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;
use Categories\Model\Category;
use Zend\Db\Sql\Select;

class CategoryTable extends AbstractTableGateway implements AdapterAwareInterface
{
	protected $table = 'capital_categories';
	
	/**
	* Тип 1 - доход, 0 - расход, null - все типы
	*/
	private $type;
 
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new HydratingResultSet();
		$this->initialize();
    }
 
    public function fetchAll($date_start=false, $date_end=false)
    {
		//if($date===false) $date = "DATE_FORMAT(CURRENT_DATE() ,'%Y-%m-01 00.00.00')";
		//else $date = in_array($date, array('null', 'CURRENT_DATE()')) ? $date : "'$date'";
		if($date_start===false) $date_start='null';
		else $date_start = "'$date_start'";
		
		if($date_end===false) $date_end='null';
		else $date_end = "'$date_end'";
		
		$query = "CALL getOverflow($date_start, $date_end, {$this->type})";
		$r = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		
		$result = $r->toArray();
			
		return $result;
	}
 
    public function getCategory($id)
    {
		$id  = (int) $id;
        $rowset = $this->select(array('id' => $id, 'type' => $this->type));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
 
    public function saveCategory(Category $category)
    {
		$data = array(
			'name' => $category->name,
			'type' => $this->type,
			'statistic' => $category->statistic,
			'amount_limit' => $category->amount_limit,
			'color' => $category->color,
			'f_deleted'=>0
        );

        $id = (int)$category->id;
		
		if ($id == 0) 
		{
			$this->insert($data);
        } 
		else 
		{
            if ($this->getCategory($id)) $this->update($data, array('id' => $id));
			else  throw new \Exception('Form id does not exist');
        }
		
    }
 
    public function deleteCategory($id)
    {
        $id = (int)$id;
		$this->update(array('f_deleted'=>1), array('id' => $id, 'type'=>$this->type));
		//$this->tableGateway->delete(array('id' => $id));
    }
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getGuide($columns = array('id','name'), $where=false)
	{
		$type = $this->type;
		$resultSet = $this->select(function (Select $select) use($type){
			$select->columns(array('id','name'));
			$select->where->equalTo('f_deleted', 0)->equalTo('type', $type);
			$select->order('statistic DESC');
		});
		
		return $resultSet->toArray();
	}
	
}
?>