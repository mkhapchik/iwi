<?php
namespace Auth\Model;

use Application\Model\AbstractTable;
use Zend\Db\Sql\Expression;
use Auth\Model\User;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\ResultSet\ResultSet;

class UserTable extends AbstractTable
{
	protected $table;
	
	public function __construct($table)
	{
		$this->table = $table;
	}
	
	
	protected function setObjectPrototype()
	{
		$this->objectPrototype = new User();
	}
	
		
	/**
	*  Инкремент счетчика неуспешных аутентификаций
	* @param $login - логин
	* @param $max_counter_failures - максимальное количество неуспешных аутентификаций,
	*			при которых происходит блокировка пользователя. false - бесконечно
	* @param $period_temporary_block_super_user - период на который блокируется суперпользователь, сек
	*/
	public function incrementCounterFailures($login, $max_counter_failures, $period_temporary_block_super_user)
	{
		$columns = array();
		$columns['counter_failures'] = new Expression('counter_failures + 1');
		if($max_counter_failures!==false) 
		{
			$expression_blocked = "(counter_failures>$max_counter_failures AND is_super IS NULL)";
			
			$columns['blocked'] = new Expression($expression_blocked);
			
			$date_blocked = $this->quoteValue(date('Y-m-d H:i:s'));
			$columns['date_blocked'] = new Expression("IF($expression_blocked, $date_blocked, null)");
									
			
			$expression_temporary_block = "(counter_failures>$max_counter_failures AND is_super=1)";
			
			$temporary_block = $this->quoteValue(date('Y-m-d H:i:s', time()+$period_temporary_block_super_user));
			$columns['temporary_block'] = new Expression("IF($expression_temporary_block, $temporary_block, null)");
			
			$date_temporary_block = $this->quoteValue(date('Y-m-d H:i:s'));
			$columns['date_temporary_block'] = new Expression("IF($expression_temporary_block, $date_temporary_block, null)");
			
		}

		$this->update(
			$columns, 
			array('login' => $login, 'blocked'=>0)
		);
		
	}
	
	/**
	*  Снятие всех видов блокировок
	*/
	public function unlockAll($user_id)
	{
		$this->update(
			array('blocked' => 0, 'date_blocked'=>null, 'counter_failures' => 0, 'temporary_block'=>null, 'date_temporary_block'=>null), 
			array('id' => $user_id)
		);
	}
	
	/**
	*  Снятие полной блокировки
	*/
	public function unlock($user_id)
	{
		$this->update(
			array('blocked' => 0, 'date_blocked'=>null, 'counter_failures' => 0), 
			array('id' => $user_id)
		);
	}
	
	
	public function lock($user_id)
	{
		$this->update(array('blocked' => 1, 'date_blocked'=>date('Y-m-d H:i:s'),  'counter_failures' => 0), array('id' => $user_id));
	}
	
	/**
	* Временная блокировка
	* @param $user_id - идентификатор пользовател¤
	* @param $date_end - дата окончани¤ блокировки
	*/
	public function temporarily_lock($user_id, $date_end)
	{
		$date_end = date('Y-m-d H:i:s', strtotime($date_end));
		$date_temporary_block = date('Y-m-d H:i:s');
		$this->update(array('temporary_block' => $date_end, 'date_temporary_block'=>$date_temporary_block), array('id' => $user_id));
	}
	
	/**
	*  Снять временную блокировку
	*/
	public function temporarily_unlock($user_id)
	{
		$this->update(
			array('temporary_block'=>null, 'date_temporary_block'=>null), 
			array('id' => $user_id)
		);
	}
		
	public function getUserById($userId)
	{
		$userId = (int)$userId;
		
		//$userPrototype = $this->sm->get('User');
		$rowset = $this->select(array('id' => $userId));
        $rowset->setObjectPrototype($this->objectPrototype);
		/*
		$row = $rowset->current();
        if (!$row) throw new \Exception("Could not find user");
        
		
		$user = $this->sm->get('User');
		$user->exchangeArray($row);
		*/
		$user = $rowset->current();
        return $user;
	}
	
	public function getUserByLogin($login)
	{
		$select = new Select($this->table);
		$select->columns(array('id','login', 'counter_failures', 'blocked', 'date_blocked', 'name', 'is_super', 'temporary_block', 'date_temporary_block','email'));
		$where = new Where();
		$where->isNull('is_super')
			->equalTo('login', $login)
			->equalTo('blocked', 0)
			->NEST
			->lessThan('temporary_block', date('Y-m-d H:i:s'))
			->or
			->isNull('temporary_block')
			->UNNEST;
		
		$select->where($where);
				
		$rowset = $this->selectWith($select);
        $rowset->setObjectPrototype($this->objectPrototype);

		$user = $rowset->current();
        return $user;
	}
	
	
	public function getUsers($sort, $filter, $is_super=false)
	{
		$select = new Select($this->table);
		$select->columns(array('id','login', 'counter_failures', 'blocked', 'date_blocked', 'name', 'is_super', 'temporary_block', 'date_temporary_block','email'));
		if(!$is_super) $select->where(array('is_super'=>null));
		if(is_array($filter)) $select->where($filter);
		$select->order($sort);
		
		$resultSetPrototype = new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new User());		
		
		$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->getAdapter(), $resultSetPrototype);
		$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
		
		
		return $paginator;
       
	}
	
	public function addUser($data)
	{
		$data = $this->filterSaveData($data);
		$this->insert($data);
	}
	
	public function editUser($data, $id)
	{
		$id = (int)$id;
		$data = $this->filterSaveData($data);
		$this->update($data, array('id'=>$id));
	}
	
	public function deleteUser($id)
	{
		$id = (int)$id;
		$where = new Where();
		$where->isNull('is_super')->equalTo('id', $id);
		$this->delete($where);
	}
	
	public function isAccessTokenPwd($token)
	{
		$select = new Select($this->table);
		$select->columns(array('id'));
		
		$where = new Where();
		$where->isNull('is_super')
			->equalTo('token_pwd', $token)
			->equalTo('blocked', 0)
			->NEST
			->lessThan('temporary_block', date('Y-m-d H:i:s'))
			->or
			->isNull('temporary_block')
			->UNNEST
			->greaterThanOrEqualTo('token_pwd_life_time', date('Y-m-d H:i:s'));
		
		$select->where($where);
		
		$rowset = $this->selectWith($select);
		return $rowset->current();
	}
	
	public function setTokenPwd($token_pwd, $token_pwd_life_time_sec, $userId)
	{
		$userId = (int)$userId;
		$this->update(
			array('token_pwd'=>$token_pwd, 'token_pwd_life_time'=>date('Y-m-d H:i:s',$token_pwd_life_time_sec)), 
			array('id'=>$userId));
	}
	
	private function filterSaveData($data)
	{
		$allowed_field = array('login','pwd','email','name','token_pwd', 'parent_user_id');
		
		$result = array_filter($data, function($k) use($allowed_field) {
			return in_array($k, $allowed_field);
		}, ARRAY_FILTER_USE_KEY);
		
		return $result;
	}
	
	

}