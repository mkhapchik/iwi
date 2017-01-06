<?php
namespace Auth;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Auth\Model\UserTable;
use Auth\Model\SessionTable;
use Auth\Model\IpAllowedListTable;
use Auth\Controller\AuthenticationController;
use Auth\Service\Authorization as AuthorizationService;
use Zend\View\Model\ViewModel;
//use Zend\ModuleManager as ModuleManager;
/*
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
*/

class Module
{
	public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

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
	
	/**
	* Обработчик события "начальная загрузка"
	*/
	public function onBootstrap(MvcEvent $e)
    {		
		$serviceManager = $e->getApplication()->getServiceManager();
		//$config = $serviceManager->get('config');

		$this->initUser($e);
		
		$eventManager        = $e->getApplication()->getEventManager();
		//$moduleRouteListener = new ModuleRouteListener();
        //$moduleRouteListener->attach($eventManager);
		
		$routeListener = $serviceManager->get('\Auth\Listener\RouteListener');
		$routeListener ->attach($eventManager);
		
		$moduleAuthListener = $serviceManager->get('AuthListener');
		$moduleAuthListener ->attach($eventManager);
		
		
		/* Перенести после определения контроллера
		$user = $sm->get('User');
		if($user->isBlocked()) 
		{
			$controller= $e->getTarget();
			$sm->get('ControllerPluginManager')->get('redirect')->toRoute('auth/logout');
		}
		*/
		
		
		//$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'initRoute'));
		//$eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkAccess'));
        
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkBlockUser'));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'checkBlockUser'));
    }

	public function checkBlockUser(MvcEvent $event)
	{
		$sm = $event->getApplication()->getServiceManager();
        $user = $sm->get('User');
        if($user->isBlocked()) 
        {
           $sm->get('ControllerPluginManager')->get('forward')->dispatch('Auth\Controller\Authentication', array('action' => 'logout'));
        }
	}

	private function initUser(MvcEvent $event)
	{
		$sm = $event->getApplication()->getServiceManager();
		$sessionTable = $sm->get('SessionTable');
		try
		{
			$storageService = $sm->get('\Auth\Service\StorageService');
			$token = $storageService->get('token');

			$remote = new \Zend\Http\PhpEnvironment\RemoteAddress();
			$ip = $remote->getIpAddress();
			
			
			//$session = $sessionTable->getSession($storage_data['token'], $ip);
			$session = $sessionTable->getSession($token, $ip);
			
			if(!isset($session->user_id)) throw new \Exception();
			
			$userTable = $sm->get('UserTable');
			$userData = $userTable->getUserById($session->user_id);
			
			$user = $sm->get('User');
			$user->exchangeArray($userData->toArray());
		}
		catch(\Exception $ex)
		{
			$user = $sm->get('User', true);
		}
		
		$roleTable = $sm->get('RoleTable');
		$roles = $roleTable->getRolesByUserId($user->id);
		$user->setRoles($roles);
		
		$authConfig = $sm->get('AuthConfig');
		$sessionTable->deleteOldSession($authConfig->lifetime_inactive_session_sec);
	}
	
	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'AuthenticationService' => function ($sm){
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					//$dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'users', 'login', 'pwd', 'MD5(?)');
					
					$credentialValidationCallback = function($dbCredential, $requestCredential) {
						$bcrypt = new \Zend\Crypt\Password\Bcrypt();
						return $bcrypt->verify($requestCredential, $dbCredential);
					};
					
					$dbTableAuthAdapter = new \Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter($dbAdapter, 'users', 'login', 'pwd', $credentialValidationCallback);
									
					$authService = new AuthenticationService(null, $dbTableAuthAdapter);
					return $authService;
				},
				'AuthorizationService' => function($sm){
					return new AuthorizationService();
				},
				'Authentication' => function($sm){
					return new \Auth\Service\Authentication();
				},
				'User' => function ($sm) {
					return new \Auth\Model\User();
				},
				'UserTable' => function ($sm) {
					return new UserTable('users');
				},
				'Session' => function ($sm) {
					return new \Auth\Model\Session();
				},
				'SessionTable' => function ($sm) {
					return new SessionTable('auth_session');
				},
				'SessionHistory' => function($sm){
					return new \Auth\Model\SessionHistory('auth_session_history');
				},
				'IpAllowedListTable' => function($sm){
					return new IpAllowedListTable('ip_allowed_list');
				},
				'PermissionsTable' => function($sm){
                    $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype($sm->get('Permission'));
                    return new \Auth\Model\PermissionsTable('permissions', null, null, $resultSetPrototype);
				},
                'Permission'=> function($sm){
					return new \Auth\Entity\Permission();
				},
				'RoleTable' => function($sm){
					$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Auth\Model\Role());
                    return new \Auth\Model\RoleTable('users_roles', null, null, $resultSetPrototype);
				},
				'RoutesTable' => function($sm){
					return new \Auth\Model\RoutesTable('routes');
				},
				'Route' => function($sm){
					return new \Auth\Model\Route();
				},
				'AuthenticationController'=>function($sm){
					return new AuthenticationController();
				},
				'AuthConfig'=>function($sm){
					$appConfig = $sm->get('config');
					$authConfig = $appConfig['auth'];
					
					return new \Zend\Config\Config($authConfig);
				},
				'AuthListener'=>function($sm){
					return new \Auth\Listener\AuthListener();
				},
				'\Auth\Listener\RouteListener'=>function($sm){
					return new \Auth\Listener\RouteListener();
				},
				'\Auth\Service\StorageService'=>function($sm){
					$authConfig = $sm->get('AuthConfig');
					if($authConfig['storage']==1)
					{
						$authenticationService = $sm->get('AuthenticationService');
						$auth_storage = $authenticationService->getStorage();
						$storage = new \Auth\Service\SessionService($auth_storage);
					}
					else
					{
						$storage = new \Auth\Service\CookieService();
					}
					
					return new \Auth\Service\StorageService($storage);
				},
				'PermissionsService'=>function($sm){
					return new \Auth\Service\PermissionsService();
				},
			),
		);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'AuthHelper' => function($sm){
						$vh = new \Auth\View\Helper\AuthHelper();
						return $vh;
				},
			)
		);
	}
	
	
}
