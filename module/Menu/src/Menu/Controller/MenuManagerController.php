<?php
namespace Menu\Controller;

use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;

class MenuManagerController extends PageController
{
    const MENU_ROUTE_NAME = "menu";
    
	public function viewAction()
	{
        $tableReport = $this->serviceLocator->get('TableReport');
		
		$limit=false;
		$allow_sort=array('label', 'is_active');
		$allow_filter=array('header', 'author_id');
		
		$tableReport->init(false, $allow_sort, $allow_filter);
		
		$sort = $tableReport->getSort();
		$filter = $tableReport->getFilter();

		$menuModel = $this->serviceLocator->get('Menu\Model\MenuTable');
		$paginator = $menuModel->fetchAll(null, true, $sort, $filter);
		
		$tableReport->setPaginator($paginator);
		
		$params = $tableReport->view();
		
		$this->layout()->setVariable('title', "Управление меню");
		return new ViewModel($params);
	}
	
	public function addMenuAction()
	{    	    
		$permissionsTable = $this->serviceLocator->get('PermissionsTable');
        $menu = new \Menu\Entity\Menu();
        
        $user = $this->serviceLocator->get('User');
        $selectedRoles = $this->serviceLocator->get('RoleTable')->getAllowedRolesForUser($user->id);
        
        
        $form = $this->serviceLocator->get('Menu\Form\MenuForm');
        $form->setSelectedRoles($selectedRoles);
        $form->init(null, $this->getAllowedMenuActionList());
		$form->bind($menu);
        
        $message=false;
		$is_success = $this->params()->fromQuery('success', 0);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) 
		{
            $postData = $request->getPost();
						
			$form->setData($postData);
			
			$menuModel = $this->serviceLocator->get('Menu\Model\MenuModel');
			$routesTable = $this->serviceLocator->get('RoutesTable');
				
			$menuId = $menuModel->add($menu);
			$routeId = $routesTable->addRoute(self::MENU_ROUTE_NAME, $menuId);
			$menuModel->setRoute($routeId, $menuId);
			
			$permissionsService = $this->serviceLocator->get('PermissionsService');
            $permissionsService->savePermission($routeId, $postData['permissions']);
        }
       
        $title = 'Добавление нового меню';
		
		$can_activeToggle = 1;
		$can_delete = 0;
        $back = $this->back(false);
        
		$params = array(
            'form' => $form, 
            'is_success'=>$is_success, 
            'message'=>$message, 
            'title'=>$title, 
            'can_activeToggle'=>$can_activeToggle, 
            'can_delete'=>$can_delete,
            'back'=>$back,
        );
		
		$view = new ViewModel($params);
		$view->setTemplate('menu/menu/form');
		
		$this->layout()->setVariable('title', $title);
		return $view;
	}

    /*
    * Список разрешенных действий контроллера Menu\Controller\Menu
    */
    private function getAllowedMenuActionList()
    {
        $menuController = $serviceManager->get('ControllerManager')->get('Menu\Controller\Menu');
        return $menuController->getAllowedActionList();
    }
    
    /*
    * Список действий данного контроллера
    */
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'view' => 'Просмотр списка меню',
			'addMenu'=>'Добавление меню',
		));
		
		return $actions;
	}
}
