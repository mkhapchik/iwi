<?php
namespace Transactions\Model;

//use Transactions\Model\AbstractTable;
use Application\Model\AbstractTable;
use Transactions\Entity\Transaction;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
class TransactionTable extends AbstractTable
{
	protected $table = 'capital_transactions';
	
	/**
	* Тип 1 - доход, 0 - расход
	*/
	private $type;
 
	public function setType($type)
	{
		$this->type = $type;
	}
    	
    public function save($transaction, $id=0)
    {
		$query = "CALL transactions('{$transaction->date}', '{$transaction->amount}', '{$transaction->categories_id}', '{$transaction->account_id}', '{$transaction->comment}')";
		$r = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		$result = $r->toArray();
		
		$id = 0;
		if(is_array($result))
		{
			$row = array_shift($result); 
			$id = $row['id'];
		}			
		return $id;
    }

	public function getGuide($columns = array('id','name'), $where=false)
	{
		throw new \Exception('Function can not be used!');
	}
	
	public function getComments($tmp)
	{
		$op_sign = $this->type==1 ? 1 : -1;
		
		$resultSet=$this->select(function(Select $select) use($tmp, $op_sign){
			$select->quantifier(SELECT::QUANTIFIER_DISTINCT);
			$select->columns(array('comment'));
			$select->order(array('date '.SELECT::ORDER_DESCENDING));
			$select->where(function(Where $where) use($tmp, $op_sign) 
			{
				$where->like("comment", "%$tmp%");
				$where->equalTo('op_sign', $op_sign);
			});
			
			$select->limit(10);
		});
		
		return $resultSet->toArray();
	}
	
	public function getTransaction($paginated=false)
	{
		$select = new Select();
		$select->from(array('t' => $this->table));
		$select->join(array('c' => 'capital_categories'), 'c.id = t.categories_id', array('categories_name'=>'name'));
		$select->join(array('a' => 'capital_account'), 'a.id = t.account_id', array('account_name'=>'name'));
		
		if(isset($this->type))
		{
			$op_sign = $this->type==1 ? 1 : -1;
			$select->where(array('t.op_sign'=>$op_sign));
		}
		
		$select->order(array('t.date'=>'DESC'));
		return $this->getPaginator($select);
	}
}
?>