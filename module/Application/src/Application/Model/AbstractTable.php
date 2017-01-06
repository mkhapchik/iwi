<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

use Categories\Model\Category;
use Zend\Db\Sql\Select;
use ArrayObject;
use Exception;

abstract class AbstractTable extends AbstractTableGateway implements ServiceLocatorAwareInterface, AdapterAwareInterface, EventManagerAwareInterface
{
	protected $table;
	protected $adapter;
	protected $connection;
	protected $objectPrototype;
	protected $sm;
	protected $events;
	
	protected function setObjectPrototype()
	{
		$this->objectPrototype = new ArrayObject();
	}
	
	public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
		//$this->events->attach('preSelect', array($this, 'preSelect'), 100);
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

	
    public function setDbAdapter(Adapter $adapter)
    {
       	$this->adapter = $adapter;
	
		$this->resultSetPrototype = new HydratingResultSet();
		$this->initialize();
		
		$driver = $this->adapter->getDriver();
		$this->connection = $driver->getConnection();
		
		$this->setObjectPrototype();
		/*
		echo get_class($this);
		
		var_dump(get_class($this->getFeatureSet()));
		//var_dump($this->getFeatureSet()->getFeatureByClassName('Auth\Model\SessionTable'));
		exit();
		$eventManager = $this->getFeatureSet()->getFeatureByClassName(get_class($this))->getEventManager();
		//$eventManager->attach('preInsert', array($this, 'preSelect'), 100);
		*/
    }
	
	/*test preSelect*/
	public function preSelect($select)
	{
		var_dump($select);
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function query($query, $mode = Adapter::QUERY_MODE_EXECUTE)
	{
		$this->log($query);
		return $this->adapter->query($query, $mode);
	}
  
	/**
	* Fetch all records from the table
	
    public function fetchAll($where=null, $paginated=false, $sort=array(), $filter=null)
	{
		throw new Exception('DEPRECATED METHOD ' . __FUNCTION__);
		$select = new Select($this->table);
		if(!empty($where)) $select->where($where);
		if(is_array($filter)) $select->where($filter);
		$select->order($sort);
		
		if($paginated) 
		{
            $paginator = $this->getPaginator($select);
			return $paginator;
        }
		else
		{
			$resultSet = $this->selectWith($select);
			$resultSet->setObjectPrototype($this->objectPrototype);
			return $resultSet->toArray();
		}
	}
	*/
	protected function getPaginator(Select $select)
	{
		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter(), $this->resultSetPrototype);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
            
		return $paginator;
	}
	    
	/**
	* Getting a table row by id
	* @param $id - ID string
	* @throw Exception
	*/
    public function get($where)
	{
        $rowset = $this->select($where);
        $rowset->setObjectPrototype($this->objectPrototype);
		$row = $rowset->current();
        if (!$row) throw new \Exception("Could not find row");
        
        return $row;
	}
 
 
	/**
	* Saving data
	* @param $data - an array whose keys are field names of the table, and values - their new values
	* @param $id - where array of date records || false - addition (by default false)
	* @throw Exception
	*/
    public function save($data, $where=false)
	{
		if ($where===false) 
		{
			$this->insert($data);
        } 
		else 
		{
           	if($this->get($where)) $this->update($data, $where);
			else throw new \Exception('Form id does not exist');
        }
	}
     
	/**
	* Removing records from the table
	* @param $id - row identifier
	*/	
    public function del($where)
	{
		//$id = (int)$id;
		$this->delete($where);
	}
    
	/**
	* Gets guide
	* @return array[array('id'=>'id', 'name'=>'name')]
	*/
	public function getGuide($columns = array('id','name'), $where=false)
	{
		$resultSet = $this->select(function(Select $select)
		{
			$select->columns($columns);
			if($where) $select->where($where);
		});
		
		return $resultSet->toArray();
	}
	
	/**
	*  quote value
	*  @param $val
	*  @return quote val
	*/
	public function quoteValue($val)
	{
		return $this->adapter->platform->quoteValue($val);
	}
	
	/**
	* Call procedure
	* @param $name - procedure's name
	* @param $params - procedure's params
	* @return array
	*/
	public function callProcedure($name, $params=false)
	{
		if(is_array($params) && count($params)>0)
		{
			
			foreach($params as &$param)
			{
				if($param==null) $param = 'null';
				else $param = $this->quoteValue($param);
			}
			
		}
		else
		{
			$params = array();
		}

		$query = "CALL $name(".implode(',', $params).")";
		return  $this->query($query);
	}
	
	
	public function beginTransaction()
	{
		return $this->connection->beginTransaction();
	}
	
	public function commit()
	{
		return $this->connection->commit();
	}
		
	public function rollBack()
	{
		return $this->connection->rollBack();
	}

	public function __destruct()
	{
		
		try
		{
			$sql = $this->sql->getSqlPlatform()->getSqlString();
			$this->log($sql);
		}
		catch(\Exception $e)
		{
			
		}
		//$sql =  $this->sql->getSqlPlatform()->getSqlString() . "\n";
		//$sql = $this->adapter->getPlatform()->getSqlString();
		//echo $sql;
		//file_put_contents('log_sql.txt', $sql, FILE_APPEND);
	}
	
	public function log($sql)
	{
		
		//file_put_contents($path . "log_sql.txt", $sql . "\n", FILE_APPEND);
		
		//$this->sm->get('logger')->info($sql);
	}
		
}
?>