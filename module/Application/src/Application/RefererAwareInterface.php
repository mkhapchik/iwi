<?php

namespace Application;

use Zend\Session;

interface RefererAwareInterface
{
    /**
     * @param Session\Container $sessionContainer
     */
    public function setSessionContainer(Session\Container $sessionContainer);
}