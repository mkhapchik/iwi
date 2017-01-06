<?php
namespace Auth\Model;

use Application\Model\AbstractTable;
use Zend\Db\Sql\Expression;
use Auth\Model\Session;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class SessionTable extends AbstractTable
{
	protected $table;
	
	public function __construct($table)
	{
		$this->table = $table;
	}
 
	protected function setObjectPrototype()
	{
		$this->objectPrototype = new Session();
	}
	
	public function getSession($token, $ip)
	{
		$rowset = $this->select(array('token' => $token, 'ip'=>$ip));
		$row = $rowset->current();
		if (!$row) throw new \Exception("Could not find session by token $token");
		$session = $this->sm->get('Session');
		$session->exchangeArray($row);
		
		return $session;
	}
	
	public function getSessionById($id)
	{
		$id = (int)$id;
		$rowset = $this->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) throw new \Exception("Could not find session");
		$session = $this->sm->get('Session');
		$session->exchangeArray($row);
		
		return $session;
	}
	
	public function updateSession($data, $token)
	{
		$this->update(
			$data, 
			array('token' => $token)
		);
	}
	
	
	public function deleteSessionByToken($token)
	{
		$this->delete(array('token' => $token));
	}
	
	

	public function deleteOldSession($lifetime_inactive_session_sec)
	{
		$where = new Where();
		$where->lessThanOrEqualTo('last_activity', new Expression("NOW() - INTERVAL $lifetime_inactive_session_sec SECOND"));
		$this->delete($where);
	}
	
	public function deleteSession($userId, $except_token=false)
	{
		$where = new Where();
		$where->equalTo('user_id', $userId);
		if($except_token)
		{
			$where->and
				->notEqualTo('token', $except_token);
		}
		$this->delete($where);
	}
	
	
	/**
	*  Получение числа сессий для указанного ip за период
	* @param $ip - ip адрес
	* @param $period_min - период в минутах
	* @return INT количество сессий
	*/
	public function getCountSession($ip, $period_min)
	{
		$select = new Select($this->table);
		
		$select->columns(array('c'=>new Expression('COUNT(*)')));
		
		$select->where
			->greaterThanOrEqualTo('starttime', new Expression("NOW() - INTERVAL $period_min MINUTE"))
			->isNull('user_id')
			->equalTo('ip', $ip);
			
		$result = $this->selectWith($select);
		$current = $result->current();
		$c = isset($current['c']) ? (int)$current['c'] : 0;

		return $c;
	}
	
	
}