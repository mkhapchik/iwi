<?php
namespace Pages\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
		$this->layout()->setVariable('title', 'Мой капитал');
		
		$view = new ViewModel(array(
            'message' => 'Hello <br> world',
        ));
 
		//$view->setTerminal(true);
        return $view;
    }
}
