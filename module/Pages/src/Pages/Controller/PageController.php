<?php
namespace Pages\Controller;

use Zend\View\Model\ViewModel;
use Pages\Form\PageForm;
use Zend\Mvc\Controller\AbstractActionController;
use Exception;
use Application\RefererAwareInterface;
use Application\Controller\Plugin\RefererRedirect;

class PageController extends AbstractActionController implements RefererAwareInterface
{
	use RefererRedirect;
    
    const PAGE_ROUTE_NAME = 'pages';
    
    protected $allowedActionList;
    
    public function viewAction()
	{
		$pageId = (int) $this->params()->fromRoute('id', 0);
		$pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
		$page = $pageModel->getPageById($pageId, 0);
		
		if(!$page || !$page->isActive()) return $this->notFoundAction();
		
		$view = new ViewModel(array('page'=>$page));
		
		if($page->template) $view->setTemplate('pages/page/templates/' . $page->template);
		
		$this->layout()->setVariable('title', $page->title);
		
		return $view;
	}
	
	public function editAction()
	{
        $this->registerReferer();
         
        # получение страницы
        $page = $this->getPage();
		if (!$page) return $this->redirectToRefererOrDefaultRoute('pages-manager', array('action' => 'view'));
        
        $oldAlias = $page->uri;
                
        $roleTable = $this->serviceLocator->get('RoleTable');
        $user = $this->serviceLocator->get('User');
        $route = $this->serviceLocator->get('Route');
        
        # получение разрешенных действий
        $allowedActionsList = $this->getAllowedActionList();
        $allowedActionsKeys = array_keys($allowedActionsList);
		       
        # получение прав доступа страницы по группам для формы
        $selectAllowedRoles = $roleTable->createSelectAllowedRolesByUserId($user->id);
        $permissionsTable = $this->serviceLocator->get('PermissionsTable');
        $permissions = $permissionsTable->getPermissions($route->id,  $selectAllowedRoles, $allowedActionsKeys);
        $page->setPermissions($permissions);
        
        # получение списка выбираемых ролей для формы
        $roles = $roleTable->getSelectedRolesForPermissions($route->id, $user->id);
        
        # инициализация формы
        $form = $this->serviceLocator->get('Pages\Form\PageForm');
        $form->setSelectedRoles($roles); 
        $form->init($route->id, $allowedActionsList);
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
                     
                # сохранение страницы
                $pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
                $pageModel->savePage($page);	
                
                # изменение активности 
                if(in_array('activeToggle', $allowedActionsKeys)) 
                {
                    if($page->isActive()) $pageModel->setActive($page->id);
                    else $pageModel->unsetActive($page->id);
                }
               
                # сохранение прав доступа
                $permissionsService = $this->serviceLocator->get('PermissionsService');                
                $permissionsService->savePermissions($route->id, $page->getPermissions(), $allowedActionsKeys);
                
                # сохранение псевдонима
                $this->setAlias($oldAlias, $page->uri);
           
                $message = '';
                $is_success=1;

                return $this->redirectToRefererOrDefaultRoute('pages-manager', array('action' => 'view'));
            }
            catch(Exception $ex)
            {
                $message = $ex->getMessage();
                $is_success=0;
            }
		}
       		
		$title = "Редактирование страницы $page->name";
		
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
    
    protected function setAlias($oldAlias, $newAlias)
    {
        if($oldAlias!=$newAlias)
        {
            $aliasesModel = $this->serviceLocator->get('Aliases\Model\AliasesModel');
            if(!empty($newAlias))
            {                
                $uri = '/'.trim($newAlias, '/');
                $uri = $aliasesModel->generateUniqueAlias($uri);
                
                if(!empty($oldAlias))
                {
                    $aliasesModel->updateAlias($oldAlias, $uri);
                }
                else
                {
                    $route = $this->serviceLocator->get('Route');
                    $name = uniqid();
                    $aliasesModel->addAlias($uri, $route->id, $name);
                }
            }
            else
            {
                $aliasesModel->delAlias($oldAlias);
            }
        }
    }
	
	public function delAction()
	{
        $page = $this->getPage();
		if(!$page) throw new Exception('page not found');

		$pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
        $pageModel->remove($page->id);
		
		//return $this->redirectToRefererOrDefaultRoute('pages-manager', array('action' => 'view'));
        $this->redirect()->toRoute('pages-manager', array('action' => 'view'));
	}
	
	public function activeToggleAction()
	{
		$this->registerReferer();
        
        $page = $this->getPage();
		if(!$page) throw new Exception('page not found');
		
        $pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
        if($page->isActive()) $pageModel->unsetActive($page->id);
        else $pageModel->setActive($page->id);

		return $this->redirectToRefererOrDefaultRoute('pages-manager', array('action' => 'view'));
	}
	
	/**
	* Список возможных действий страницы 
	*/
    public function getActionList()
	{
		return array(
			'view' => 'Просмотр страницы', 
			'edit' => 'Редактирование страницы', 
			'del'=> 'Удаление страницы', 
			'activeToggle'=>'Изменение активности страницы'
		);
	}
    
    /**
    * Список разрешенных действий страницы
    * $returnKeys флаг возвращать только ключи (действия)
    */
    protected function getAllowedActionList($returnKeys=false)
    {
        if(isset($this->allowedActionList)) return $this->allowedActionList;
        
        $user = $this->serviceLocator->get('User');
        $route = $this->serviceLocator->get('Route');
        $permissionsTable = $this->serviceLocator->get('PermissionsTable');
        
        $pageActionsList = $this->getActionList();
        
        if($user->isSuper())
        {
            $allowedActionsList = $pageActionsList;
        }
        else
        {
            $allowedActionsKeys = $permissionsTable->getAllowedActions($route->id, $user->id, array_keys($pageActionsList));
            
            $allowedActionsList = array_filter($pageActionsList, function($key) use($allowedActionsKeys){
                return in_array($key, $allowedActionsKeys);    
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return $returnKeys ? array_keys($allowedActionsList) : $allowedActionsList;
    }
	
	protected function back($is_redirect=true)
	{
		/*
        $redirectUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
        $referer = $this->getRequest()->getHeader('Referer');
        $redirectUrl = $referer ? $referer->getUri() : false;

		$requestUri = $this->getRequest()->getRequestUri();
		
		if($redirectUrl == $requestUri) $is_redirect = false;
				
		if($is_redirect)
		{
			return $this->redirect()->toUrl($redirectUrl);
		}
		else return $redirectUrl;
        */
        throw new Exception('Функция back устарела! Необходимо использовать трейт referer!');
	}
    
    private function getPage()
    {
        $pageId = (int)$this->params()->fromRoute('id', 0);		
		$route = $this->serviceLocator->get('Route');
        $pageModel = $this->serviceLocator->get('Pages\Model\PageModel');
				
		if($pageId && $route->route_name==self::PAGE_ROUTE_NAME) $page = $pageModel->getPageById($pageId);
		else $page = $pageModel->getPageByRouteId($route->id);
        
        return $page;
    }
	
}
