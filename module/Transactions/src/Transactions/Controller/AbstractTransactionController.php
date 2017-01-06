<?php
namespace Transactions\Controller;

use Zend\View\Model\ViewModel;
use Transactions\Form\TransactionForm;
use Transactions\Entity\Transaction;
use Pages\Controller\PageController;

abstract class AbstractTransactionController extends PageController
{
	/**
	* Тип операции 1 - доход, 0 - расход
	*/
	protected $type;
		
	public function addAction()
	{
		$form = new TransactionForm();
		
		$categories = $this->getCategories();
		$form->setCategories($categories);
		$accounts = $this->getAccounts();
		$form->setAccounts($accounts);
		
		$form->init();
        $form->get('submit')->setValue('Сохранить');
 
		$message=false;
		$is_success = $this->params()->fromQuery('success', 0);
 
        $request = $this->getRequest();
        
		if ($request->isPost()) 
		{
          	$form->setData($request->getPost());

            if($form->isValid()) 
			{
				$data = $form->getData();
				
				$transactionTable = $this->getTransactionTable();
				$transactionTable->setType($this->type);
				$transactionTable->beginTransaction();
				$err = 0;
				try
				{
					foreach($data['transaction'] as $k=>$values)
					{
						$t = new Transaction();
						$t->exchangeArray($values);
						$new_id = $transactionTable->save($t);
						if(!$new_id) throw new \Exception();
					}
					
					$transactionTable->commit();
					
					$url = $this->plugin('url')->fromRoute()."?success=1";
					return $this->redirect()->toUrl($url);
				}
				catch(\Exception $e)
				{
					$transactionTable->rollback();
					$message = 'Ошибка сохранения данных!';
					$is_success = 0;
				}
            }
			else
			{
				$message = '';
				$is_success=0;
			}
		}
		
		if($is_success) $message = 'Данные успешно добавлены';
		return array('form' => $form, 'is_success'=>$is_success, 'message'=>$message);
	}
	
	public function getCommentsAction()
	{
		$request = $this->getRequest();
		$str = $request->getPost()->get('param', false);
		
		$result = array();
		if($str)
		{
			$transactionTable = $this->getTransactionTable();
			$transactionTable->setType($this->type);
			$res = $transactionTable->getComments($str);
			if(is_array($res) && count($res)>0)
			{
				foreach($res as $v) $result[]=$v['comment'];
			}
		}
		
		echo json_encode($result);
		exit();
	}
	
	public function viewAction()
	{
		$transactionTable = $this->getTransactionTable();
		$transactionTable->setType($this->type);
		
		$paginator = $transactionTable->getTransaction(true);
		
		$paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
		
		$paginator->setItemCountPerPage(10);
		
		$routName   = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
		
		$routeParams = array('action' => 'view');
		
		return new ViewModel(array(
			'paginator' => $paginator,
			'routName' => $routName,
			'routeParams'=>$routeParams
		));
	}
	
	protected function getCategories()
	{
		$sm = $this->getServiceLocator();
		$categoryTable = $sm->get('CategoryTable');
		$categoryTable->setType($this->type);
		$categories = $categoryTable->getGuide();
		$result = array();
		if($categories)
		{
			foreach($categories as $category)
			{
				$result[$category['id']] = $category['name'];
			}
		}
		
		return $result;
	}
	
	protected function getAccounts()
	{
		$sm = $this->getServiceLocator();
		$accountTable = $sm->get('AccountTable');
		$accounts = $accountTable->getGuide();
		$result = array();
		if($accounts)
		{
			foreach($accounts as $account)
			{
				$result[$account['id']]=$account['name'];
			}
		}
		
		return $result;
	}
	
	protected function getTransactionTable()
	{
		$sm = $this->getServiceLocator();
		$transactionTable = $sm->get('TransactionTable');
		
		return $transactionTable;
	}
	
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'add' => "Добавление транзакций",
			'getComments' => 'Подгрузка комментариев',
		));
		
		return $actions;
	}
}