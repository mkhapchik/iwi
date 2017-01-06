<?php
namespace Auth\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Auth\Service\Authorization as AuthorizationService;
use Zend\View\Model\ViewModel;
use Exception;

class AuthorizationController extends AbstractActionController
{
	public function _isAuthorize()
	{
		return false;
	}
		
	public function checkTimeoutAction()
	{
		try
		{
			$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
			$authConfig = $this->getServiceLocator()->get('authConfig');
			$lastActivity = $storageService->isExist('last_activity') ? $storageService->get('last_activity') : 0;
			
			if((time()-$lastActivity)>$authConfig['inactivity_time_sec']) 
			{
				throw new Exception(AuthorizationService::CODE_ACCESS_IS_DENIED_BY_TIMEOUT);
			}

			exit();
		}
		catch(Exception $ex)
		{
			$view = $this->forward()->dispatch('Auth\Controller\Authentication', array(
				'action' => 'login',
				'is_success'=>0,
				'codeAccess'=>$ex->getMessage(),
				'is_ajax'=>true,
			));
			
			$view->setVariable('title', '');
			
			$viewDialog = new ViewModel(array('title'=>'Аутентификация'));
			$viewDialog->setTerminal(true);
			$viewDialog->addChild($view, 'view');
			
			
			return $viewDialog;
		}
	}
	
	
	
	
}