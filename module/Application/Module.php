<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager; 
use Zend\Session;

class Module
{
    
	
	public function getAutoloaderConfig()
    {
		return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getConfig()
    {
		return include __DIR__ . '/config/module.config.php';
    }
	
	public function getServiceConfig()
    {
		return array(
            'factories' => array(
                'menu' => function($sm){
                    $menutable = new \Application\Model\MenuTable('menu');
                    return $menutable;
                },
 				'Navigation' => function ($sm) {
					$navigation =  new \Application\Navigation\MyNavigation();
					return $navigation->createService($sm);
				},
				'SendMailService'=>function($sm){
					$sms =  new \Application\Service\SendMailService();
					return $sms;
				},
				'TableReport'=>function($sm){
					$r =  new \Application\Service\Reports\TableService();
					return $r;
				},
				'logger'=>function($sm){
					$path = __DIR__ . '/../../logs/';
					$logger = new \Zend\Log\Logger;
					$writer = new \Zend\Log\Writer\Stream($path."log_sql");
					$logger->addWriter($writer);
					return $logger;
				}
			),
            
		);
    }
    
    public function getControllerConfig()
    {
        return array(
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof RefererAwareInterface) {
                        $instance->setSessionContainer(new Session\Container('Referer'));
                    }
                }
            ),
        );
    }
	
    public function init(ModuleManager $m)
    {
        $this->bootstrapSession($m);
    }
    
    public function bootstrapSession(ModuleManager $m)
    {
        $sesionName = md5($_SERVER['SERVER_NAME'].$_SERVER['SERVER_PORT']);
        session_name($sesionName);
    }
    
	/**
	* Обработчик события "начальная загрузка"
	*/
	public function onBootstrap(MvcEvent $e)
    {
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		$app = $e->getApplication();
		$sm = $app->getServiceManager();
		
		$local = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$sm->get('translator')->setLocale($local)->setFallbackLocale('en_US');

       
        
        $app->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'setLayout'));	
		$app->getEventManager()->attach(MvcEvent::EVENT_FINISH, array($this, 'logger'));	
    }
    
    
	
	public function logger($e)
	{
		$app = $e->getApplication();
		$sm = $app->getServiceManager();
		
		$logger = $sm->get('logger');
		
		$adapter = $sm->get('Zend\Db\Adapter\Adapter');
		$driver = $adapter->getDriver();
		$connection = $driver->getConnection();
		$profiler = $connection->getProfiler();
		$profiles = $profiler->getProfiles();
		
		foreach($profiles as $p)
		{
			$sql = $p['sql'];
			if($p['parameters'] instanceof \Zend\Db\Adapter\ParameterContainer)
			{
				$params = $p['parameters']->getNamedArray();
			
				foreach($params as $k=>$v)
				{
					$sql = str_replace(":$k", "'$v'", $sql);
				}
				
				
			}
			
			$elapse = round($p['elapse'], 3);
			
			$mess = "\n sql: \n $sql \n elapse: $elapse \n";
			$logger->info($mess);
			
		}
		
	}
	
    public function setLayout($e)
    {
        try
		{
			$sm = $e->getApplication()->getServiceManager();
			$route = $sm->get('Route');
			if(!empty($route->layout))
			{
				$viewModel = $e->getViewModel();
				$viewModel->setTemplate($route->layout);
			}else{
                // for testing template
               // $viewModel = $e->getViewModel();
               // $viewModel->setTemplate('layout/admin');
            }
		}
		catch(\Exception $ex)
		{
			
		}
		/*
		$matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        
		if (false !== strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }
		*/
        // Set the layout template
        
		
    }
	
	
	public function addRoutes(MvcEvent $e)
	{
		$router = $e->getRouter();
		$route = \Zend\Mvc\Router\Http\Literal::factory(array(
			'route' => '/foo',
			'defaults' => array(
				'__NAMESPACE__' => 'Transactions\Controller',
				'controller'    => 'TransactionExpense',
				'action'        => 'add',
			),
		));
		//$router->addRoute('account/default', $route);
		$router->addRoute('foo', $route);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'formGroup' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroup();
				},
				'formGroupEmail' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupEmail();
				},
				'formGroupPwdGen' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupPwdGen();
				},
				'formGroupPwdShowHide' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupPwdShowHide();
				},
				'formGroupCheckbox' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupCheckbox();
				},
				'formGroupSubmit' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupSubmit();
				},
				'formGroupCaptcha' => function($sm){
					return new \Application\View\Helper\FormHelper\FormGroupCaptcha();
				},
				
				'tag' => function($sm){
					return new \Application\View\Helper\Tag();
				},
				'message' => function($sm){
					return new \Application\View\Helper\Message();
				}
			)
		);
	}
	
}
