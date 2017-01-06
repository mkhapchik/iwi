<?php
/**
 *  DEPREACATED
 */
namespace Application\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class MyNavigation extends DefaultNavigationFactory
{
    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {
		if(!$serviceLocator->get('AuthenticationService')->hasIdentity()) return false;
		if (null === $this->pages) 
		{
            //FETCH data from table menu :
            $fetchMenu = $serviceLocator->get('menu')->fetchAllItem();
			
            $configuration['navigation'][$this->getName()] = array();
            foreach($fetchMenu as $key=>$row)
            {
                $configuration['navigation'][$this->getName()][$row['name']] = array(
                    'label' => $row['label'],
                    'route' => $row['route'],
					/*
					'pages'      => array(
						array(
							'label'      => 'Foo Server',
							'module'     => 'application',
							'controller' => 'index',
							'action'     => 'index',
						)
					)
					*/
                )  ;
            }
            
            if (!isset($configuration['navigation'])) throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
            
            if (!isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }

            $mvcEvent = $serviceLocator->get('Application')->getMvcEvent();
			//$application = $serviceLocator->get('Application');
            $routeMatch  =  $mvcEvent->getRouteMatch();
            $router      =  $mvcEvent->getRouter();
            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }
	
        return $this->pages;
		
		/*
		 $navigation = array();

        
           
			
			$navigation[] = array(
				'label'      => 'Products',
				'module'     => 'application',
				'controller' => 'index',
				'action'     => 'index',
				'uri'=> '/qwe',
				'pages'      => array(
					array(
						'label'      => 'Foo Server',
						'module'     => 'account',
						'controller' => 'index',
						'action'     => 'index',
					)
				)
				
			
			);
			
			
			
			

            $mvcEvent = $serviceLocator->get('Application')->getMvcEvent();

            $routeMatch = $mvcEvent->getRouteMatch();
			$router     = $mvcEvent->getRouter();
            $pages      = $this->getPagesFromConfig($navigation);

            $this->pages = $this->injectComponents(
                $pages,
                $routeMatch,
                $router
            );
        

        return $this->pages;
		*/
    }
}
