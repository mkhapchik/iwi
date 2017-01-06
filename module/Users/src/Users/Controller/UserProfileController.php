<?php
namespace Users\Controller;

use Zend\View\Model\ViewModel;
use Pages\Controller\PageController;

class UserProfileController extends PageController
{

	public function viewAction()
	{
		//echo date('Y-m-d H:i:s');
		//echo date_default_timezone_get();
		//$this->historyAction();
		
	}
	
	public function historyAction()
	{
		$from = $this->params()->fromPost('from', strtotime(date('Y-m-d 0:00:00', strtotime("-1 day"))) * 1000);
		$to = $this->params()->fromPost('to', strtotime(date('Y-m-d 0:0:0', strtotime("+1 day"))) * 1000);
		$width = $this->params()->fromPost('width', 600);
		
		$from = ($from)/1000;
		$to = ($to)/1000;
		
		$start = date('Y-m-d H:i:s', $from);
		$end = date('Y-m-d H:i:s', $to);

		$period = $to-$from;
		
		if($period<30) $step = 1;
		else if($period < 3600) $step = 3;
		else $step = (int)($period/$width)*3;
		
		if($step<1) $step=1;
		
		//$step = 100;
		$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
		$data = $sessionHistory->getHistoryByUserId($step, $start,$end, 1);
		
		echo json_encode(array('is_success'=>1, 'message'=>'', 'data'=>$data));
		exit();
	}
	
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'history' => "История сессий пользователя",
		));
		
		return $actions;
	}
}