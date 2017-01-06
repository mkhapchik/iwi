<?php
namespace Reports\Controller;

use Zend\View\Model\ViewModel;
use Pages\Controller\PageController;

class TableCategoriesExpenController extends PageController
{	
	public function viewAction()
	{
		$categoryTable = $this->getCategoryTable();
		$categoryTable->setType(0);
		
		$report = $this->getReport();
		
		$report_table = array();
		$summary_table = array();
		
		$params = $this->params()->fromQuery();
		$start 	= isset($params['start']) && !empty($params['start']) 	? 	strtotime($params['start']) : 	strtotime(date('Y-m-1') . "- 1 month");
		$end 	= isset($params['end'] 	) && !empty($params['end'])		?	strtotime($params['end'])	:	strtotime("now");
				
		$year_start = date('Y', $start);
		$month_start = date('n', $start);
		$day_start = date('d', $start);
		
		$year_end = date('Y', $end);
		$month_end = date('n', $end);
		$day_end = date('d', $end);
		
		
		for($y=$year_start; $y<=$year_end; $y++)
		{
			if($y==$year_start) $m_start = $month_start;
			else $m_start = 1;
			
			if($y==$year_end) $m_end = $month_end;
			else $m_end = 12;
			
			for($m=$m_start; $m<=$m_end; $m++)
			{
				$d_start = '01';
				$d_end = false;
				
				if($y==$year_start && $m==$month_start) $d_start = $day_start;
				if($y==$year_end && $m==$month_end)$d_end = $day_end;
												
				$result_start = "$y-$m-$d_start";
				$result_end   = $d_end ? "$y-$m-$d_end" : false;
				
				$report_table[$y][$m] = $categoryTable->fetchAll($result_start, $result_end);
				
				$summary_table[$y][$m]= $report->getReportExpense($result_start, $result_end);
			}
		}

		return array('report_table'=>$report_table, 'start'=>date('d.m.Y',$start), 'end'=>date('d.m.Y',$end), 'summary_table'=>$summary_table);
		
	}
	
	private function getCategoryTable()
	{
		$sm = $this->getServiceLocator();
		$categoryTable = $sm->get('CategoryTable');
		return $categoryTable;
	}
	
	private function getReport()
	{
		$sm = $this->getServiceLocator();
		$report = $sm->get('Report');
		return $report;
	}
}