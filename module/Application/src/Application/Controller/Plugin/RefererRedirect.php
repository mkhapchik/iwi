<?php

namespace Application\Controller\Plugin;

use Zend\Session;

trait RefererRedirect
{
    /**
     * @var Session\Container
     */
    private $sessionContainer;

    /**
     * @param Session\Container $sessionContainer
     */
    public function setSessionContainer(Session\Container $sessionContainer)
    {
        $this->sessionContainer = $sessionContainer;
    }

    protected function clearReferer()
    {
        $this->sessionContainer->offsetUnset('referer');
    }

    /**
     * @param $forcibly - перезаписать, если уже установлена
     */
    protected function registerReferer()
    {
        if (!$this->sessionContainer->offsetExists('referer')) {
            $referer = $this->getRequest()->getHeader('Referer');
            if($referer) $this->sessionContainer->offsetSet('referer', $referer->getUri());
        }
    }

    protected function getReferer($defaultRoute=false, $defaultParams=array())
    {
        if($this->sessionContainer->offsetExists('referer')) 
        {
            return $this->sessionContainer->offsetGet('referer');
        }
        else if($defaultRoute)
        {
            $viewHelperManager = $this->serviceLocator->get('ViewHelperManager');
            $urlHelper = $viewHelperManager->get('url');
		
            return $urlHelper($defaultRoute, $defaultParams);
        }
        else return false;
    }
    
    protected function redirectToRefererOrDefaultRoute($defaultRoute, $defaultParams = [])
    {
        if ($this->sessionContainer->offsetExists('referer')) {
            $url = $this->sessionContainer->offsetGet('referer');
            $this->clearReferer();

            return $this->redirect()->toUrl($url);
        }

        return $this->redirect()->toRoute($defaultRoute, $defaultParams);
    }
}