<?php
namespace Application\Db\TableGateway;

use Application\Db\Db;

use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

class TableGateway extends AbstractTableGateway implements AdapterAwareInterface
{
    private $db;
	protected $adapter;
	
	public function __construct($table, AdapterInterface $adapter=null, $features = null, ResultSetInterface $resultSetPrototype = null, Sql $sql = null)
    {
        // table
        if (!(is_string($table) || $table instanceof TableIdentifier || is_array($table))) {
            throw new Exception\InvalidArgumentException('Table name must be a string or an instance of Zend\Db\Sql\TableIdentifier');
        }
        $this->table = $table;

      
        // process features
        if ($features !== null) {
            if ($features instanceof Feature\AbstractFeature) {
                $features = array($features);
            }
            if (is_array($features)) {
                $this->featureSet = new Feature\FeatureSet($features);
            } elseif ($features instanceof Feature\FeatureSet) {
                $this->featureSet = $features;
            } else {
                throw new Exception\InvalidArgumentException(
                    'TableGateway expects $feature to be an instance of an AbstractFeature or a FeatureSet, or an array of AbstractFeatures'
                );
            }
        } else {
            $this->featureSet = new Feature\FeatureSet();
        }

        // result prototype
        $this->resultSetPrototype = ($resultSetPrototype) ?: new ResultSet;

        if($adapter !==null)
		{
			$this->adapter = $adapter;
			// Sql object (factory for select, insert, update, delete)
			$this->sql = ($sql) ?: new Sql($this->adapter, $this->table);

			// check sql object bound to same table
			if ($this->sql->getTable() != $this->table) {
				throw new Exception\InvalidArgumentException('The table inside the provided Sql object must match the table of this TableGateway');
			}

			$this->initialize();
		}
    }
	
	public function setDbAdapter(Adapter $adapter)
    {
		$this->adapter = $adapter;
		$this->initialize();
    }
	
	public function query($query, $mode = Adapter::QUERY_MODE_EXECUTE, ResultSetInterface $resultPrototype = null)
	{
		return $this->adapter->query($query, $mode);
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
	public function callProcedure($name, $params=false, $mode = Adapter::QUERY_MODE_EXECUTE, ResultSetInterface $resultPrototype = null)
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
		
		return  $this->query($query, $mode, $resultPrototype);
	}
	
	public function __get($property)
	{
		try
		{
			$result = parent::__get($property);
		}
		catch(Exception $ex)
		{
			throw new Exception($property . ": " . $ex->getMessage());
		}
		return $result;
	}
}
