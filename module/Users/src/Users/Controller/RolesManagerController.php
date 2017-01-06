<?php
namespace Users\Controller;

use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;
use Exception;

class RolesManagerController extends PageController
{
	public function viewAction()
	{
		$tableReport = $this->serviceLocator->get('TableReport');
		
		$limit=false;
		$allow_sort=array();
		$allow_filter=array('label');
		
		$tableReport->init(false, $allow_sort, $allow_filter);
		
		$sort = $tableReport->getSort();
		$filter = $tableReport->getFilter();

		$user = $this->serviceLocator->get('User');
		
		$roleTable = $this->serviceLocator->get('RoleTable');
        $paginator = $roleTable->getRolesPaginator($user->id, $sort, $filter);
        
        $tableReport->setPaginator($paginator);

		$params = $tableReport->view();
        
        $title = "Управление ролями пользователей";
		$params['title'] = $title;
		$this->layout()->setVariable('title', $title);
		
		$view = new ViewModel($params);
        
        return $view;
		
	}
	
	public function addRoleAction()
	{
		$result = false;
		$params = $this->process(0, $result);
		
		$request = $this->getRequest();
		$is_xmlhttprequest = (bool)@$request->isXmlHttpRequest();
		if($result===true && !$is_xmlhttprequest) return $this->redirect()->toRoute('roles-manager');
				
		$title = "Добавление новой роли пользователя";
		$params['title'] = $title;
		
		return $this->setFormView($params, $result);
	}
	
	public function editRoleAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if(!$id) die('404');//return $this->redirect()->toRoute('roles-manager', array('action' => 'add-role'));
       
		$result = false;
		$params = $this->process($id, $result);
		
		$request = $this->getRequest();
		$is_xmlhttprequest = (bool)@$request->isXmlHttpRequest();
         
		if($result===true && !$is_xmlhttprequest) return $this->redirect()->toRoute('roles-manager');
		
		$role = $params['role'];
		$title = "Редактирование роли '{$role->label}'";
		$params['title'] = $title;
		
		return $this->setFormView($params, $result);
	}
	
	private function setFormView($params, $result)
	{
		$request = $this->getRequest();
		$is_xmlhttprequest = (bool)@$request->isXmlHttpRequest();
		
		if($is_xmlhttprequest && $request->getPost('form_name', false)==$params['form']->getName())
		{
			echo json_encode($params);
			exit();
		}
		
		$view = new ViewModel($params);
		$view->setTemplate('users/roles-manager/form');
		
		if($is_xmlhttprequest) $view->setTerminal(true);
		else $this->layout()->setVariable('title', $params['title']);
		
		return $view;
	}
	
	public function delRoleAction()
	{
		/*
		 $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('album');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
                 $this->getAlbumTable()->deleteAlbum($id);
             }

             // Redirect to list of albums
             return $this->redirect()->toRoute('album');
         }

         return array(
             'id'    => $id,
             'album' => $this->getAlbumTable()->getAlbum($id)
         );
		*/
	}
	
	private function process($id, &$result)
	{
		$user = $this->serviceLocator->get('User');
       
        $roleTable = $this->serviceLocator->get('RoleTable');
        $selectedRoles = $roleTable->getSelectedRolesForRole($id, $user->id);
        
        $result = false;
		$form  = $this->serviceLocator->get('Users\Form\RoleForm');
        $form->setSelectedRoles($selectedRoles);
        $form->init();
        
		if($id)
		{
			$role = $roleTable->getRole($id);
            
            
            $setAllowedRolesResultSet = $roleTable->getSetAllowedRolesForRole($id, $user->id);
            
            $setAllowedRoles =array();
            foreach($setAllowedRolesResultSet as $roles) $setAllowedRoles[]=$roles;
            
            $role->setAllowedRoles($setAllowedRoles);

            //$form->setData(array('roles'=>$setAllowedRoles));
            
            $form->get('submit')->setAttribute('value', 'Сохранить');
		}
		else
		{
			$role = new \Auth\Model\Role();
            
            $form->get('submit')->setAttribute('value', 'Добавить');
		}
        
        $form->bind($role);       
		
		$is_success = 1;
		$message = '';
		
        $request = $this->getRequest();
		
        if($request->isPost() && $request->getPost('form_name', false)==$form->getName()) 
		{
			try
			{
				$form->setData($request->getPost());
                
				
				if($form->isValid()) 
				{                  
					$addRoles = array();
                    $delRoles = array();
                    
                    $permissionsRolesModel = $this->serviceLocator->get('Users\Model\PermissionsRolesModel');
                    
                    if($id)
					{
                        $roleTable->editRole($role);
                        
                        $rolesOld = array();
                        $rolesNew = array();
                       
                        if(isset($setAllowedRoles) && count($setAllowedRoles)>0)
                        {
                            foreach($setAllowedRoles as $r) $rolesOld[]=$r->id;
                        }

                        if(count($role->roles) > 0)
                        {
                            foreach($role->roles as $r) if($r->id) $rolesNew[]=$r->id;
                        }
                                    
                        $addRoles = array_diff($rolesNew, $rolesOld);
                        $delRoles = array_diff($rolesOld, $rolesNew);
                        
                        if(count($delRoles)>0) $permissionsRolesModel->del($delRoles, $id);
                        if(count($addRoles)>0) foreach($addRoles as $roleId) $permissionsRolesModel->add($roleId, $id);
                       
					}
					else
					{
                        $role->exchangeArray($form->getData());
                        $roleTable->addRole($role);
                        $id = $roleTable->getLastInsertValue();
						$role->exchangeArray(array('id'=>$id));
                        
                        $permissionsRolesModel->add($id, null, $user->id);
                        
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
        }
		
        $params = array(
            'id' => $id,
            'form' => $form,
			'form_messages'=>$form->getMessages(),
			'is_success' => $is_success,
			'message' => $message,
			'role'=>$role,
        );
		
		return $params;
	}
    
    public function getActionList()
	{
		$def_actions = parent::getActionList();
		$actions = array_merge($def_actions, array(
			'add-role' => 'Добавление ролей',
            'edit-role' => 'Редактирование ролей',
			'del-role' => 'Удаление ролей',
		));
		
		return $actions;
	}
}