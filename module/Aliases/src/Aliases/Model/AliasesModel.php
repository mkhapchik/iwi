<?php
namespace Aliases\Model;
use Application\Model\AbstractTable;
use Zend\Db\Sql\Select;

class AliasesModel extends AbstractTable
{
	public function __construct($table)
	{
		$this->table = $table;
	}
	
	public function addAlias($uri, $route_id, $name)
	{
		$data = array(
			'uri'=>$uri,
			'route_id'=>$route_id,
			'name'=>$name
		);
		$this->insert($data);
	}
	
	public function updateAlias($old_uri, $new_uri)
	{
		$this->update(array('uri'=>$new_uri), array('uri'=>$old_uri));
	}
	
	public function delAlias($uri)
	{
		$this->delete(array('uri'=>$uri));
	}
	
	public function getAliases()
	{
		$select = new Select();
		$select->columns(array(
			'uri',
			'name',
		));
		$select->from(array('a' => $this->table));
		$select->join(array('r' => 'routes'), 'a.route_id = r.id', array('route_name', 'route_param_id', 'layout', 'is_active'), Select::JOIN_INNER);
				
		$rowset = $this->selectWith($select);
				
		$aliases = $rowset->toArray();
		if(count($aliases)>0)
		{
			//if(!empty($aliases['route_params'])) $aliases['route_params'] = (array)json_decode($aliases['route_params']);
			return $aliases;
		}
		else return false;
	}
	
	public function match($uri)
	{
		$select = new Select();
		$select->columns(array(
			'uri',
			'alias_name'=>'name'
		));
		
		$select->from(array('a' => $this->table));
		$select->join(array('r' => 'routes'), 'a.route_id = r.id', array('route_name', 'route_param_id', 'layout', 'is_active', 'id'), Select::JOIN_INNER);
		
		$select->where->equalTo('a.uri', $uri);
		
		$rowset = $this->selectWith($select);
		
		$aliases = false;
		
		if(count($rowset)>0)
		{
			if($rowset->count()>1) 
				throw new Exception('Incorrect identification of the route. Found more than one route.');
			
			$aliases = (array)$rowset->current();
			
			/*
			if(is_array($aliases) && count($aliases)>0)
			{
				if(!empty($aliases['route_params'])) $aliases['route_params'] = (array)json_decode($aliases['route_params']);
			}
			*/
		}
		
		return $aliases;
	}
	
	private function trim($val)
	{
		$val = '/'.trim($val, '/');
		return $val;
	}
	
	public function getRouteNameByAliasId($aliasId)
	{
		return "route_".$aliasId;
	}
	
	public function generateUniqueAlias($uri, $count = '')
	{
		$select = new Select($this->table);
		$select->columns(array(
			'c' => new \Zend\Db\Sql\Expression("COUNT(*)"),
		));
		
		$select->where(array('uri'=>$uri . $count));
		
		$res = $this->selectWith($select);
		$current = $res->current();
		$c = $current['c'];
		
		if($c>0)
		{
			$count++;
			$uri = $this->generateUniqueAlias($uri, $count);
		}
		else
		{
			$uri = $uri.$count;
		}
		
		return $uri;
	}
}
