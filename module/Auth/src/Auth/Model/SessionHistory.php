<?php
namespace Auth\Model;

use Application\Model\AbstractTable;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class SessionHistory extends AbstractTable
{
	protected $table;
	
	public function __construct($table)
	{
		$this->table = $table;
	}
	
	public function addSession($token, $remote_addr, $user_id=0, $starttime = null)
	{
		$remote_addr = $remote_addr ? $this->quoteValue($remote_addr) : null;
		$starttime = $starttime ? $starttime : date('Y-m-d H:i:s');
		$user_id = $user_id ? $user_id : 0;
		$data = array(
			'token'=>$token,
			'user_id'=>$user_id,
			'remote_addr'=>$remote_addr ? new Expression("INET_ATON($remote_addr)") : null,
			'starttime' => $starttime, 
			'last_activity'=>$starttime,
			'endtime' => null,
			'closing_reason'=>null,
			'http_accept_language'=> $_SERVER['HTTP_ACCEPT_LANGUAGE'],
			'http_user_agent'=> $_SERVER['HTTP_USER_AGENT'],
		);
		
		
		$this->save($data);
		
	}
	
	public function closeSession($token, $closing_reason=null, $endtime=null)
	{
		$data = array(
			'last_activity'=>$endtime,
			'endtime' => $endtime,
			'closing_reason'=>$closing_reason,
		);
		
		$where = array(
			'token'=>$token,
			'endtime'=>null,
		);
		
		$this->update($data, $where);
	}
	
	public function closeMultiSession($userId, $except_token=false)
	{
		$data = array(
			'endtime'=>date('Y-m-d H:i:s'),
			'closing_reason'=>'closing_reason_close_multisession',
		);
		
		$where = new Where();
		$where->equalTo('user_id', $userId)
			->and
			->equalTo('endtime', null);
		if($except_token) $where->notEqualTo('token', $except_token);
		
		$this->update($data, $where);
	}
	
	public function updateLastActivity($token, $last_activity)
	{
		$data = array(
			'last_activity'=>$last_activity
		);
		
		$where = array(
			'token'=>$token,
			'endtime'=>null,
		);
		
		$this->update($data, $where);
	}
	
	public function getHistoryByUserId($step, $from_datetime, $to_datetime, $user_id)
	{
		/*
		SELECT ROUND((MAX(UNIX_TIMESTAMP(starttime)) - MIN(UNIX_TIMESTAMP(starttime)))/1000) AS step_sec FROM auth_session_history;

SELECT S.gperiod*1000 as gperiod, SUM(S.c) AS c, FROM_UNIXTIME(S.gperiod) AS d, IF(SUM(S.is_guest)=0, 1, 0) AS is_guest
FROM
 (
SELECT UNIX_TIMESTAMP(DATE_FORMAT(ash.starttime, '%Y-%m-%d %H:%i:00')) AS gperiod, COUNT(*) AS c, SUM(ash.user_id) as is_guest  
FROM auth_session_history ash
WHERE ash.user_id IN(1,0)
GROUP BY ash.token, gperiod
  ) AS S
GROUP BY S.gperiod 
ORDER BY S.gperiod;
		
		
		*/
		
		
		$result = $this->callProcedure('report_session_history', array($step, $from_datetime, $to_datetime, $user_id));
		
		$data = array();
		while($d = $result->current())
		{
			$result->next();
			$data[]= array((int)$d['gperiod']*1000, (int)$d['c'], date('d.m.Y H:i:s', $d['gperiod']),  $d['is_guest']);
		}
		
		return $data;
		/*
		$select = $this->getSql()->select();
		$select->columns(array('token', 'user_id', 'starttime', 'last_activity', 'closing_reason'));
		$select_tokens = $this->getSql()->select();
		$select_tokens->columns(array('token'));
		$select_tokens->where
			->equalTo('user_id', $userId);
		
		$select->where
			->In('token', $select_tokens)
			->nest()
				->equalTo('user_id', $userId)
				->or
				->IsNull('user_id')
			->unnest();
		
		$result = $this->selectWith($select);
		return $result->toArray();
		*/
		
		/*CALL report_session_history(30, '2016-03-01 0:00:00','2016-03-22 0:00:00', 1);*/
	}
	
	
}