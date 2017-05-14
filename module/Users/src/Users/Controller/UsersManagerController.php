<?php
namespace Users\Controller;

use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;
use Exception;

class UsersManagerController extends PageController
{
	public function authorize($e)
	{
		//use Auth\Service\Authorization as AuthorizationService;
		$AuthorizationService = $this->getServiceLocator()->get('AuthorizationService');
		$action = $e->getRouteMatch()->getParam('action');
		
		if($action=='restoreSession') return $AuthorizationService::CODE_ACCESS_IS_ALLOWED;
		else return $AuthorizationService->checkAccess($e);
		
	}
	
	public function viewAction()
	{
		$tableReport = $this->serviceLocator->get('TableReport');
		
		$limit=false;
		$allow_sort=array();
		$allow_filter=array();
		
		$tableReport->init($limit, $allow_sort, $allow_filter);
		
		$sort = $tableReport->getSort();
		$filter = $tableReport->getFilter();

		$user = $this->serviceLocator->get('User');
		
		$userTable = $this->serviceLocator->get('UserTable');
		$paginator = $userTable->getUsers($sort, $filter, $user->isSuper());
		$tableReport->setPaginator($paginator);
		
		$params = $tableReport->view();
		
		$sm = $this->serviceLocator;
        $userAuthId = $user->id;
        
		$getRoles = function($id) use($sm, $userAuthId){
			$roleTable = $sm->get('RoleTable');
			
            return $roleTable->getSetAllowedRolesForUser($id, $userAuthId);
		};
		$params['getRoles'] = $getRoles;
        $params['userAuthId'] = $userAuthId;
				
		$this->layout()->setVariable('title', "Управление пользователями");

        return new ViewModel($params);
	}
	
	public function addUserAction()
	{
		$params = $this->process(0, $result);
		if($result===true) return $this->redirectToRefererOrDefaultRoute('users-manager');
				
		$title = "Добавление нового пользователя";
		$params['title'] = $title;
		$params['can_delete']=0;
		$this->layout()->setVariable('title', $title);
		$view = new ViewModel($params);
		$view->setTemplate('users/users-manager/edit-user');
		return $view;
	}
	
	public function editUserAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if(!$id) return $this->redirect()->toRoute('users-manager', array('action' => 'addUser'));
       
		$params = $this->process($id, $result);
		if($result===true) return $this->redirectToRefererOrDefaultRoute('users-manager');
		
		$edit_user = $params['edit_user'];
		
		$title = "Редактирование пользователя '{$edit_user->name}'";
		$params['title'] = $title;
		$params['can_delete'] = 0;
		if(!$edit_user->isSuper())
		{
			$route = $this->serviceLocator->get('route');
			$authorizationService = $this->serviceLocator->get('AuthorizationService');
			if($authorizationService->isAllowed($route->id, 'deleteUser'))
			{
				$params['can_delete'] = 1;	
			}
		}
		
		$this->layout()->setVariable('title', $title);
		
		return new ViewModel($params);
	}
	
	private function process($id, &$result)
	{
        $result = false;
		$userTable = $this->serviceLocator->get('UserTable');
		$roleTable = $this->serviceLocator->get('RoleTable');
		
        $form  = $this->serviceLocator->get('Users\Form\UserForm');
        
        $authUser = $this->serviceLocator->get('User');
        $selectedRoles = $roleTable->getSelectedRolesForUserId($id, $authUser->id);
       
        $form->setSelectedRoles($selectedRoles);
        $form->init($id);
		 
		$default_data = array();
		$default_data['roles'] = array();

		if($id)
		{
			$edit_user = $userTable->getUserById($id);
			if(!$edit_user) return $this->notFoundAction();
			$default_data = array_merge($default_data, $edit_user->toArray());
			
			$temporary_block_time = isset($default_data['temporary_block']) && $default_data['temporary_block']>0 ? strtotime($default_data['temporary_block']) : 0;
			if($temporary_block_time>time()) $default_data['temporary_block'] = date('d.m.Y H:i', $temporary_block_time);
			else $default_data['temporary_block'] = '';
			
            $roles = $roleTable->getSetAllowedRolesForUser($id, $authUser->id);
			$default_data['roles'] = $roles->toArray();
            
			
			$form->get('submit')->setAttribute('value', 'Сохранить');
		}
		else
		{
			$default_data['temporary_block'] = '';
			$edit_user = null;
			$form->get('submit')->setAttribute('value', 'Добавить');
		}
		
		
		$form->setData($default_data); 
			
		$is_success = 1;
		$message = '';
		
        $request = $this->getRequest();
		
        if($request->isPost()) 
		{
			try
			{
				$auth_user = $this->serviceLocator->get('User');
				
				if($id)
				{
					if($edit_user->isSuper() && !$auth_user->isSuper())
					{
						throw new Exception("Доступ запрещен!");
					}
				}
				
				$form->setData($request->getPost());

				if($form->isValid()) 
				{
					$data = $form->getData();
				
					if(isset($data['pwd']))
					{
						if(!empty($data['pwd']))
						{
							$bcrypt = new \Zend\Crypt\Password\Bcrypt();
							$data['pwd'] = $bcrypt->create($data['pwd']);
						}
						else
						{
							unset($data['pwd']);
						}
					}

					if($id)
					{
						$userTable->editUser($data, $id);
					}
					else
					{
						$data['parent_user_id'] = $auth_user->id;
						$userTable->addUser($data);
						$id = $userTable->getLastInsertValue();
					}
					
					if(isset($default_data['blocked']) && $default_data['blocked']!=$data['blocked'])
					{
						if($data['blocked']) $userTable->lock($id);
						else $userTable->unlock($id);
					}
					
					if(isset($default_data['temporary_block']) && $default_data['temporary_block']!=$data['temporary_block'])
					{
						if(!empty($data['temporary_block'])) $userTable->temporarily_lock($id, $data['temporary_block']);
						else $userTable->temporarily_unlock($id);
					}
					
					$rolesOld = array();
					$rolesNew = array();

					if(isset($default_data['roles']) && count($default_data['roles'])>0)
					{
						foreach($default_data['roles'] as $role) $rolesOld[]=$role['id'];
					}
					if(isset($data['roles']) && count($data['roles']) > 0)
					{
						foreach($data['roles'] as $role) if($role['id']) $rolesNew[]=$role['id'];
					}
								
					$addRoles = array_diff($rolesNew, $rolesOld);
					$delRoles = array_diff($rolesOld, $rolesNew);
					
					$usersRolesMapTable = $this->serviceLocator->get('Users\Model\UsersRolesMapTable');
					if(count($delRoles)>0) 
                    {
                        // сделать через сервис менеджер
                        $conditionAllowRoles = $roleTable->createSelectAllowedRolesByUserId($auth_user->id);
                        $usersRolesMapTable->del($id, $delRoles, $conditionAllowRoles);
                    }
					if(count($addRoles)>0) 
					{
						foreach($addRoles as $roleId) $usersRolesMapTable->add(array('user_id'=>$id, 'role_id'=>$roleId));	
					}
  				
					$result=true;
				}
				else
				{
					throw new Exception("Ошибка! Некорректные данные.");
				}
			}
			catch(Exception $ex)
			{
				$is_success = 0;
				$message = $ex->getMessage();
			}
        }else{
            $this->registerReferer();
        }
		
		if(empty($form->get('temporary_block')->getValue())) 
		{
			$form->get('swich_disable_temporary_block')->setValue(0);
			$form->get('temporary_block')->setAttribute('disabled', 'disabled');
		}
		else
		{
			$form->get('swich_disable_temporary_block')->setValue(1);
		}
		
		$form_messages = $form->getMessages();
		if(!is_array($form_messages)) $form_messages = array();
		
		$back = $this->getReferer('users-manager');
        $params = array(
            'id' => $id,
            'form' => $form,
			'form_messages'=>$form_messages,
			'is_success' => $is_success,
			'message' => $message,
			'edit_user'=>$edit_user,
			'back'=>$back
        );
		
		return $params;
	}
	
	public function deleteUserAction()
	{
		$message = '';
		$is_success = 0;
		$user = null;
		
		try
		{
			$id = (int) $this->params()->fromRoute('id', 0);
			if (!$id) throw new Exception('Id is not received!');
			
			$userTable = $this->serviceLocator->get('UserTable');
            $roleTable = $this->serviceLocator->get('RoleTable');
            
			$user = $userTable->getUserById($id);
            if(!$user) throw new Exception('User is not found!');
            
            if($user->isSuper()) throw new Exception('Removing forbidden!');
            
            $roles = $roleTable->getRolesByUserId($user->id);
			$user->setRoles($roles);

            // удалить сессии
			$authenticationService = $this->serviceLocator->get('Authentication');
			$authenticationService->deleteAllSession($user->id);
			
			//удалить связи c ролями
			$usersRolesMapTable = $this->serviceLocator->get('Users\Model\UsersRolesMapTable');           
            $conditionAllowRoles = $roleTable->createSelectAllowedRolesByUserId($user->id);
            $usersRolesMapTable->del($user->id, $user->getRolesIDs(), $conditionAllowRoles);
          
            
			// удалить самого пользователя
			$userTable->deleteUser($user->id);
			
			$message = "Пользователь {$user->name} успешно удалён";	
			$is_success = 1;
		}
		catch(Exception $ex)
		{
			$message = $ex->getMessage();
		}
		
        $json = array(
			'is_success' => $is_success,
			'message' => $message,
			'user' => $user
		);
		
		echo json_encode($json);
		exit();
	}
	
	public function loginAsUserAction()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
		if (!$id) throw new Exception('Id is not received!');
				
		$authentication = $this->serviceLocator->get('Authentication');
		
		$user = $this->serviceLocator->get('User');
		
        if($user->id==$id) throw new Exception('You are already using the selected user session!');
        
        $userTable = $this->serviceLocator->get('UserTable');		
        $newUser = $userTable->getUserById($id);
        
        if(!$newUser) throw new Exception('User not found!');
        
        if($newUser->isBlocked()) throw new Exception('User is blocked!');
        
		$session = $this->serviceLocator->get('Session');
		if(!$session) throw new Exception('Session not found!');
					
		$authentication->createSession($newUser->id, $session->id);
		$this->redirect()->toRoute('profile', array('action'=>'view'));	
	}
	
	public function restoreSessionAction()
	{
		$this->registerReferer();
         
        $sessionTable = $this->serviceLocator->get('SessionTable');
		$session = $this->serviceLocator->get('Session');
		if(!$session) throw new Exception('Session not found!');
		
		$oldSessionToken = $session->token;
		
		$authentication = $this->serviceLocator->get('Authentication');
		
		try
		{
			$restoreSession = $sessionTable->getSessionById($session->parent_session_id);

			$authentication->setStorage($restoreSession->token, $restoreSession->lastActivity);
					
			$sessionTable->deleteSessionByToken($oldSessionToken);
		}
		catch(Exception $ex)
		{
			$authentication->deleteAllSession($session->user_id);
		}
	
		return $this->redirectToRefererOrDefaultRoute('profile', array('action' => 'view'));
	}
	
	public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'editUser' => 'Редактирование пользователей',
			'addUser' => 'Добавление пользователей',
			'deleteUser' => 'Удаление пользователей',
			'loginAsUser'=>'Вход под именем пользователей'
		));
		
		return $actions;
	}
	
	
}