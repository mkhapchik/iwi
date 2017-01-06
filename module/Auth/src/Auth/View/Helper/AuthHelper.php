<?php
namespace Auth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
	protected $sm;
	protected $pm;
	protected $events;
    
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->pm = $serviceLocator;
		$this->sm = $serviceLocator->getServiceLocator();
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	public function __invoke()
	{
		return $this;
	}
	
	public function timeoutScript()
	{
		$moduleAuthListener = $this->sm->get('AuthListener');
		$e = $this->sm->get('Application')->getMvcEvent();
		
		if($moduleAuthListener->isAuthorize($e))
		{	
			$config = $this->sm->get('config');
			$view = new ViewModel(array(
				'frequency' => $config['auth']['frequency_of_check_timeout_sec'],
				'url' => $this->pm->get('url')->__invoke('auth/timeout')
			));
	 
			$view->setTemplate('auth/authHelper/timeoutScript');

			$partialHelper = $this->view->plugin('partial');
			
			return $partialHelper($view);
		}
		else return '';	
	}
	
	public function user($user_tpl=false, $guest_tpl=false)
	{
		$user = $this->sm->get('User');
		$view = new ViewModel();
		
		if($user->isAuth())
		{
			if(!$user_tpl) return '';
			$logout_url = $this->pm->get('url')->__invoke('auth/logout');
			$view->setVariable('logout_url', $logout_url);
			$view->setVariable('user', $user);
			$view->setTemplate($user_tpl);
		}
		else
		{
			if(!$guest_tpl) return '';
			$login_url = $this->pm->get('url')->__invoke('auth/login');
			$view->setVariable('login_url', $login_url);
			$view->setTemplate($guest_tpl);
		}

		$partialHelper = $this->view->plugin('partial');
		return $partialHelper($view);
	}
	
	public function isAllowed($routeId, $action='view')
	{
		$authorizationService = $this->sm->get('AuthorizationService');
		return $authorizationService->isAllowed($routeId, $action);
	}
}
?>