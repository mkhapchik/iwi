<?php
namespace Reports\Model;

use Application\Model\AbstractTable;
use Zend\Db\Adapter\Adapter;

class Report extends AbstractTable
{
	protected $table = 'users';
	
	public function getReportExpense($date_start=false, $date_end=false)
    {
		if($date_start===false) $date_start=null;
		else $date_start = $date_start;
		
		if($date_end===false) $date_end=null;
		else $date_end = $date_end;

		$r = $this->callProcedure('report_expense', array($date_start, $date_end));
		$result = $r->toArray();
			
		return $result;
	}

}
?>