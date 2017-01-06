<?php
namespace Auth\Model;

use Application\Model\AbstractTable;
use Zend\Db\Sql\Select;

class IpAllowedListTable extends AbstractTable
{
	protected $table;
	
	public function __construct($table)
	{
		$this->table = $table;
	}
	
	public function is_allowed($ip)
	{
		$select = new Select($this->table);
		$select->columns(array('c' => new \Zend\Db\Sql\Expression('COUNT(*)')));
		$select->where(array('ip'=>$ip, 'is_active'=>1));
	
		$rowset = $this->selectWith($select);
		$row = $rowset->current();
		return (bool)$row['c'];
	}
}