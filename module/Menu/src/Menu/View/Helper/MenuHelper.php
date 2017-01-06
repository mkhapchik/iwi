<?php
namespace Menu\View\Helper;

use Zend\View\Helper\Navigation;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Exception;
use Zend\Navigation\Service\ConstructedNavigationFactory;

class MenuHelper extends Navigation implements ServiceLocatorAwareInterface
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
	
	public function __invoke($menuName=null, $parentId=0)
	{
		$menuService = $this->sm->get('Menu\Service\Menu');
		$menu = $menuService->getMenu($menuName, $parentId);
		
		if($menuName===false) 
		{
			$result = '';
			foreach($menu as $name=>$m)	$result .= $this->makeMenu($m, $partial); 
		}
		else
		{
			$result = $this->makeMenu($menu);
		}
		
		return $result;
	}
	
	protected function makeMenu($config)
	{
		$factory    = new ConstructedNavigationFactory($config);
		$navigation = $factory->createService($this->getServiceLocator());
				
		$result = parent::__invoke($navigation)->menu();
	
		//$result = $this->render();
		 
		return $result;
	}

}
?>