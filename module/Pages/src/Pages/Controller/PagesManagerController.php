<?php
namespace Pages\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Pages\Form\PageForm;
use Pages\Form\PageFilter;
use Pages\Controller\PageController;

class PagesManagerController extends PageController
{
	const PAGE_ROUTE_NAME = 'pages';

    public function addAction()
	{
        $this->registerReferer();
         
        # получение страницы
        $page = new \Pages\Entity\Page();
                        
        $roleTable = $this->serviceLocator->get('RoleTable');
        $user = $this->serviceLocator->get('User');

        # получение разрешенных действий
        $allowedActionsList = $this->getAllowedActionList();
        $allowedActionsKeys = array_keys($allowedActionsList);
		

        $roles = $roleTable->getAllowedRolesForUser($user->id);

        $form = $this->serviceLocator->get('Pages\Form\PageForm');
        $form->setSelectedRoles($roles);

        # инициализация формы
        $form = $this->serviceLocator->get('Pages\Form\PageForm');
        $form->setSelectedRoles($roles);
        $form->init($page->route_id, $allowedActionsList);
		$form->bind($page);
        
        $message=false;
		$is_success = $this->params()->fromQuery('success', 0);
        
        $request = $this->getRequest();
        
		if ($request->isPost()) 
		{
			try
            {
                $form->setData($request->getPost());    
                if(!$form->isValid()) throw new Exception('Некорректные данные'); 
                
                # добавление страницы и маршрута
                $pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
                $routesTable = $this->serviceLocator->get('RoutesTable');
                $page->id = $pageModel->add($page);
                $page->route_id = $routesTable->addRoute(self::PAGE_ROUTE_NAME, $page->id);
                $pageModel->setRoute($page->route_id, $page->id);

                # изменение активности 
                if(in_array('activeToggle', $allowedActionsKeys)) 
                {
                    if($page->isActive()) $pageModel->setActive($page->id);
                    else $pageModel->unsetActive($page->id);
                }
                else
                {
                    $pageModel->setActive($page->id);
                }
                
                # сохранение прав доступа
                $permissionsService = $this->serviceLocator->get('PermissionsService');                
                $permissionsService->savePermissions($page->route_id, $page->getPermissions(), $allowedActionsKeys);
                 
                # сохранение псевдонима
                $this->setAlias(false, $page->uri);
           
                return $this->redirect()->toRoute('pages-manager', array('action'=>'view'));
            }
            catch(Exception $ex)
            {
                $message = $ex->getMessage();
                $is_success=0;
            }
		}
       		
		$title = 'Добавление новой страницы';
		
        $referer = $this->getReferer('pages-manager');
        
		$params = array(
			'form' => $form, 
			'is_success'=>$is_success, 
			'message'=>$message, 
			'page'=>$page, 
			'title'=>$title, 
            'allowedActions'=>$allowedActionsKeys,
			'referer'=>$referer
		);
		
		$view = new ViewModel($params);
		$view->setTemplate('pages/page/form');
		
		$this->layout()->setVariable('title', $title);
		
		return $view;
	}
    
	
	public function viewAction()
	{
		$pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
		
		$route = $this->serviceLocator->get('Route');
		$page = $pageModel->getPageByRouteId($route->id);
		
		$user = $this->serviceLocator->get('User');
		
		if(!$page || !$page->isActive()) return $this->notFoundAction();
		
        $tableReport = $this->serviceLocator->get('TableReport');
		
		$limit=false;
		$allow_sort=array('name', 'date_last_modification', 'date_creation', 'author_id');
		$allow_filter=array('name', 'is_system');
		$pageFilterForm	= new PageFilter();
		$tableReport->init($limit, $allow_sort, $allow_filter, $pageFilterForm);
		
		$sort = $tableReport->getSort();
		$filter = $tableReport->getFilter();
        if(!isset($filter['is_system'])) $filter['is_system']=0;
		if(count($sort)==0) $sort = array('date_last_modification'=>'DESC');
                
		$paginator = $pageModel->fetchAll(array('is_delete'=>0), true, $sort, $filter);
		$tableReport->setPaginator($paginator);
	
		$params = $tableReport->view();
	
		
		$this->layout()->setVariable('title', $page->title);
		return new ViewModel($params);
	}
	
	public function selectAction()
	{
		$pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
		
		$route = $this->serviceLocator->get('Route');
		$page = $pageModel->getPageByRouteId($route->id);
		
		$user = $this->serviceLocator->get('User');
		
		if(!$page || !$page->isActive()) 
		{
			//if(!$user->isSuper()) return $this->notFoundAction();
			//else echo "Эту страницу видет только Суперпользователь";
			return $this->notFoundAction();
		}

		$tableReport = $this->serviceLocator->get('TableReport');
		
		$limit=false;
		$allow_sort=array('name');
		$allow_filter=array('name', 'is_system');
		$pageFilterForm	= new PageFilter();
		$tableReport->init($limit, $allow_sort, $allow_filter, $pageFilterForm);
		
		$sort = $tableReport->getSort();
		$filter = $tableReport->getFilter();
		if(!isset($filter['is_system'])) $filter['is_system']=0;
		
		$paginator = $pageModel->fetchAll(array('is_delete'=>0, 'is_active'=>1), true, $sort, $filter);
		$tableReport->setPaginator($paginator);
	
		$params = $tableReport->view();
		$params['routeParams']=array('action'=>'select');
		
		$view = new ViewModel($params);
		$view->setTerminal(true);
		
		return $view;
	}
	
	public function getActionList()
	{
		$actions = array_merge(parent::getActionList(), array(
            'view' => 'Просмотр списка страниц',
			'add' => "Добавление страницы",
            'select'=>'Выбор страниц'
        ));

		return $actions;
	}
    
}
