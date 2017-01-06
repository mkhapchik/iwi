<?php
namespace Users\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserHelper extends AbstractHelper implements ServiceLocatorAwareInterface
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
	
	public function loginAsUser($tpl)
	{
		$session = $this->sm->get('Session');
		$result = '';
		
		if(!empty($session->parent_session_id))
		{
			$view = new ViewModel();
			$user = $this->sm->get('User');
			$view->setVariable('user', $user);
			$view->setTemplate($tpl);
			$partialHelper = $this->view->plugin('partial');
			$result = $partialHelper($view);
			
		}
		
		return $result;
	}
}
?>