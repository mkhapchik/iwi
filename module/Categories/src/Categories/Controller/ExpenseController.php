<?php
namespace Categories\Controller;

use Zend\View\Model\ViewModel;
use Categories\Model\Category;
use Categories\Model\CategoryTable;
use Categories\Form\ExpenseForm;
use Pages\Controller\PageController;

class ExpenseController extends PageController
{
	public function viewAction()
	{
		$categoryTable = $this->getCategoryTable();
		$categoryTable->setType(0);
		$categories = $categoryTable->fetchAll();
			
		$this->layout()->setVariable('title', 'Категории расхода');
		
		$view = new ViewModel(array(
            'categories' => $categories,
        ));
		
        return $view;
		
	}
	
	public function addAction()
	{
		$form = new ExpenseForm();
		
        $form->get('submit')->setValue('Добавить');
 
        $request = $this->getRequest();
        if ($request->isPost()) 
		{
            $form->setData($request->getPost());
 
            if ($form->isValid()) 
			{
                $category = new Category();
				$category->exchangeArray($form->getData());
                $categoryTable = $this->getCategoryTable();
				$categoryTable->setType(0);
				
				$categoryTable->saveCategory($category);
 
                
				return $this->redirect()->toRoute('categories/expense');
            }
        }
		return array('form' => $form);
	}
	
	public function editexAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		$categoryTable = $this->getCategoryTable();
		$categoryTable->setType(0);
		$old_category = $categoryTable->getCategory($id);
		
		$form = new ExpenseForm();
		$form->get('submit')->setValue('Сохранить');
		$form->setData((array)$old_category);
		
		$request = $this->getRequest();
        if ($request->isPost()) 
		{
            $form->setData($request->getPost());
 
            if ($form->isValid()) 
			{
                $category = new Category();
				$category->exchangeArray($form->getData());
                $categoryTable = $this->getCategoryTable();
				$categoryTable->setType(0);
				
				$categoryTable->saveCategory($category);
                
				return $this->redirect()->toRoute('categories/expense');
            }
        }
		
		return array('form' => $form);
	}
	
	public function delexAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		$categoryTable = $this->getCategoryTable();
		$categoryTable->setType(0);
		$categoryTable->deleteCategory($id);
		return $this->redirect()->toRoute('categories/expense');
	}
	
	private function getCategoryTable()
	{
		$sm = $this->getServiceLocator();
		$categoryTable = $sm->get('CategoryTable');
		return $categoryTable;
	}
	
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'add' => "Добавление расходов",
			'editex' => 'Редактирование расходов',
			'delex' => 'Удаление расходов',
		));
		
		return $actions;
	}
	
}
?>