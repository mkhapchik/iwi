<?php
namespace Auth\Model;

use Application\Model\AbstractTable;
use Zend\Db\Sql\Select;
use Exception;

class RoutesTable extends AbstractTable
{
	protected $table;
	
	public function __construct($table)
	{
		$this->table = $table;
	}
	
	public function getRoute($route_name, $route_param_id = null)
	{
		if(!empty($route_param_id)) 
		{
			$route_param_id = (int)$route_param_id;
			$route_param_id_cond = "(route_param_id = $route_param_id OR route_param_id IS NULL)";
		}
		else
		{
			$route_param_id_cond = "route_param_id IS NULL";
		}
	
		$route_name = $this->quoteValue($route_name);
		
		$query = "SELECT id, route_name, route_param_id, layout, is_active
			FROM routes r 
			WHERE route_name = $route_name AND $route_param_id_cond";
		
		$rowset=$this->query($query);
		if($rowset->count()>1) 
			throw new Exception('Incorrect identification of the route. Found more than one route.');
		
		$result = $rowset->current();
		
		if($result) return (array)$result;
		else return false;
	}
	
	public function addRoute($route_name, $route_param_id=null)
	{
		$data = array(
			'route_name'=>$route_name,
			'route_param_id'=>$route_param_id,
			'is_active'=>1
		);
		$this->insert($data);
		
		return $this->getLastInsertValue();
	}
	
	public function setActive($routeId)
	{
		$routeId = (int)$routeId;
		$this->update(array('is_active'=>1), array('id'=>$routeId));
	}
	
	public function setInactive($routeId)
	{
		$routeId = (int)$routeId;
		$this->update(array('is_active'=>0), array('id'=>$routeId));
	}
}