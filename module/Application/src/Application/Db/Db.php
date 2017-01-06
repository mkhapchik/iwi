<?php
namespace Application\Db;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSetInterface;

class Db implements AdapterAwareInterface
{
	protected $adapter;
	protected $connection;
	protected $driver;
	
	public function setDbAdapter(Adapter $adapter)
    {
       	$this->adapter = $adapter;
		$this->driver = $this->adapter->getDriver();
		$this->connection = $this->driver->getConnection();
    }
	
	public function getDbAdapter()
	{
		return $this->adapter;
	}
	
	public function getDbConnection()
	{
		return $this->connection;
	}
	
	public function getDbDriver()
	{
		return $this->driver;
	}
	
	public function query($query, $mode = Adapter::QUERY_MODE_EXECUTE, ResultSetInterface $resultPrototype = null)
	{
		return $this->adapter->query($query, $mode, $resultPrototype);
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
	
	
}
?>
