<?php
namespace Account\Controller;


use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Account\Model\Account;
use Account\Model\AccountTable;
use Account\Form\AccountForm;
use Pages\Controller\PageController;

//class IndexController extends AbstractActionController
class IndexController extends PageController
{
    public function viewAction()
    {
		$accountTable = $this->getAccountTable();
		$accounts = $accountTable->fetchAll();
		
		$this->layout()->setVariable('title', 'Счета');
		
		$view = new ViewModel(array(
            'accounts' => $accounts,
        ));
		
        return $view;
    }
	
	public function addAction()
    {
       
		$form = new AccountForm();
		
        $form->get('submit')->setValue('Добавить');
 
        $request = $this->getRequest();
        if ($request->isPost()) 
		{
            $form->setData($request->getPost());
 
            if ($form->isValid()) 
			{
                $account = new Account();
				$account->exchangeArray($form->getData());
                $this->getAccountTable()->saveAccount($account);
 
                
				return $this->redirect()->toRoute('account/default');
            }
        }
		return array('form' => $form);
    }
	
	public function editaccAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		$accountTable = $this->getAccountTable();
		$old_account = $accountTable->getAccount($id);
		
		$form = new AccountForm();
		$form->get('submit')->setValue('Сохранить');
		$form->setData((array)$old_account);
		
		$request = $this->getRequest();
        if ($request->isPost()) 
		{
            $form->setData($request->getPost());
 
            if ($form->isValid()) 
			{
                $account = new Account();
				$account->exchangeArray($form->getData());
                $accountTable->saveAccount($account);
                
				return $this->redirect()->toRoute('account/default');
            }
        }
		
		return array('form' => $form);
	}
	
	public function delaccAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		$accountTable = $this->getAccountTable();
		$accountTable->deleteAccount($id);
		return $this->redirect()->toRoute('account/default');
	}
	
	
	
	private function getAccountTable()
	{
		$sm = $this->getServiceLocator();
		$accountTable = $sm->get('AccountTable');
		return $accountTable;
	}
	
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'add' => "Добавление счетов",
			'editacc' => 'Редактирование счетов',
			'delacc' => 'Удаление счетов',
		));
		
		return $actions;
	}
}
