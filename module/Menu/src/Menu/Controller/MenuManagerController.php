<?php
namespace Menu\Controller;

use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;
use Menu\Entity\Menu;
use \Exception;

class MenuManagerController extends PageController
{
    const MENU_ROUTE_NAME = "menu";
    
	public function viewAction()
	{
        $tableReport = $this->serviceLocator->get('TableReport');

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
        $menu = new Menu();
        $menu->setPermissions(array());
        
        $user = $this->serviceLocator->get('User');
        $selectedRoles = $this->serviceLocator->get('RoleTable')->getAllowedRolesForUser($user->id);

        # получение разрешенных действий
        $allowedActions = $this->getAllowedMenuActionList();
        $allowedActionsKeys = array_keys($allowedActions);
        
        $form = $this->serviceLocator->get('Menu\Form\MenuForm');
        $form->setSelectedRoles($selectedRoles);
        $form->init(null, $allowedActions);
		$form->bind($menu);
        
        $message='';
		$is_success = $this->params()->fromQuery('success', 0);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) 
		{
            try {
                $postData = $request->getPost();
                $form->setData($postData);

                if (!$form->isValid()) throw new Exception('Некорректные данные');

                $menuTable = $this->serviceLocator->get('Menu\Model\MenuTable');
                $routesTable = $this->serviceLocator->get('RoutesTable');

                $menuId = $menuTable->addMenu($menu);
                $menu->route_id = $routesTable->addRoute(self::MENU_ROUTE_NAME, $menuId);
                $menuTable->setRoute($menu->route_id, $menuId);

                $permissionsService = $this->serviceLocator->get('PermissionsService');
                //$permissionsService->savePermissions($routeId, $postData['permissions']);
                $permissionsService->savePermissions($menu->route_id, $menu->getPermissions(), $allowedActionsKeys);

                return $this->redirectToRefererOrDefaultRoute('menu-manager', array('action' => 'view'));

            } catch (Exception $ex) {
                $is_success = 0;
                $message = $ex->getMessage();
            }
        }else{
            $this->registerReferer();
        }
       
        $title = 'Добавление нового меню';

        $referer = $this->getReferer('menu-manager');

        $params = array(
            'form' => $form, 
            'is_success'=>$is_success, 
            'message'=>$message, 
            'title'=>$title, 
            'allowedActions'=>$allowedActions,
            'referer'=>$referer,
            'menu'=>$menu,
            'tree'=>array()
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
        $menuController = $this->serviceLocator->get('ControllerManager')->get('Menu\Controller\Menu');
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
