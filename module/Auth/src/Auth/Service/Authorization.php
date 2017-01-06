<?php
namespace Auth\Service;

use Auth\Model\SessionTable;
use Auth\Model\User;
use Auth\Controller\AuthenticationController;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Exception;

class Authorization implements ServiceLocatorAwareInterface
{
	const CODE_ACCESS_IS_ALLOWED = 1;
	const CODE_ACCESS_IS_DENIED = -1;
	const CODE_ACCESS_IS_DENIED_BY_TIMEOUT = -2;
	const CODE_ACCESS_NULL = 2;
	const CODE_ACCESS_IS_USER_BLOCKED = -3;
	const CODE_ACCESS_IDENTITY_FAILED = -4;
	const CODE_ACCESS_IS_DENIED_BY_IP_NOT_IN_ALLOWED_LIST = -5;
	const CODE_ACCESS_IS_USER_TEMPORARILY_BLOCKED = -6;
	const CODE_ACCESS_EXCEEDED_NUMBER_ALLOWED_SESSIONS_FOR_IP = -7;
	
	protected $config;
	protected $sm;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->sm = $serviceLocator;
	}

    public function getServiceLocator()
	{
		return $this->sm;
	}
	
	/**
	*  Проверка доступа
	* @param $e
	* @return $code - код доступа, self::CODE_ACCESS_IS_ALLOWED - доступ разрешен
	*/
	public function checkAccess($e)
	{
		$routeMatch = $e->getRouteMatch();
		
		$result = self::CODE_ACCESS_IS_DENIED;
				
		try
		{
			$authentication = $this->sm->get('Authentication');
			/*
			$remote = new \Zend\Http\PhpEnvironment\RemoteAddress();
			$ip = $remote->getIpAddress();
			*/
			$ip = $authentication->getIpAddress();
			
			
			if(!$this->checkIpInAllowedLists($ip)) throw new Exception(self::CODE_ACCESS_IS_DENIED_BY_IP_NOT_IN_ALLOWED_LIST);

			if(!$this->isSessionExist())
			{
				if(!$authentication->checkSessionLimitForIp()) throw new Exception(self::CODE_ACCESS_EXCEEDED_NUMBER_ALLOWED_SESSIONS_FOR_IP);
				
				$authentication->createSession(null);
			}
			
			
			$this->session_regenerate_id();
			
			if($this->checkTimeout())
			{
				if(!$this->checkPermissions($routeMatch)) throw new Exception(self::CODE_ACCESS_IS_DENIED);
				$result = self::CODE_ACCESS_IS_ALLOWED;
			}
			else
			{
				$authentication->updateSession(null);
				throw new Exception(self::CODE_ACCESS_IS_DENIED_BY_TIMEOUT);
			}
		}
		catch(Exception $e)
		{
			$result = $e->getMessage();
		}
		
		return $result;			
	}
	
	public function updateLastActivity()
	{
		if($this->isSessionExist())
		{
			try
			{
				$session = $this->sm->get('Session');
				
				$newLastActivity = time();
				$sessionTable = $this->sm->get('SessionTable');
				
				$sessionTable->save(array('last_activity'=>date('Y-m-d H:i:s',$newLastActivity)), array('id'=>$session->id));
				
				if(!empty($session->parent_session_id))
				{
					$sessionTable->save(array('last_activity'=>date('Y-m-d H:i:s',$newLastActivity)), array('id'=>$session->parent_session_id));
				}
				
				$config = $this->sm->get('AuthConfig');
				if($config->record_history_sessions)
				{
					$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
					$sessionHistory->updateLastActivity($session->token, date('Y-m-d H:i:s', $newLastActivity));
				}
				
				$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
				$storageService->set('last_activity', $newLastActivity);
			}
			catch(Exception $ex)
			{
				$user = $this->sm->get('user');
				$authentication = $this->sm->get('Authentication');
				$authentication->updateSession($user->id);
			}
		}
	}
	
	private function isSessionExist()
	{
		$session = $this->sm->get('Session');
		return isset($session->id);
	}
	
	private function checkTimeout()
	{
		$result = false;
		
		$session = $this->sm->get('Session');
		
		$inactivityTime = $this->getConfig('inactivity_time_sec');
		$lastActivity = strtotime($session->lastActivity);
		
		if((time()-$lastActivity)<=$inactivityTime || !$session->user_id) $result = true;
		
		return $result;
	}
	
	private function checkPermissions($routeMatch)
	{
		$result = false;
		
		$route = $this->sm->get('Route');
	
		$action = $routeMatch->getParam('action');			
		
		if($this->isAllowed($route->id, $action)) $result = true;
		
		return $result;
	}
	
	public function isAllowed($routeId, $action)
	{
		$user = $this->sm->get('User');
		
		if($user->isSuper()) return true;
			
		if(!empty($routeId))
		{
		
			$roles = $user->getRoles();
			if(is_array($roles) && count($roles)>0) $roles = array_keys($roles);
			else $roles = null;
						
			$permissionsTable = $this->sm->get('PermissionsTable');
			
			$result = $permissionsTable->isAllowed($routeId, $roles, $user->id, $action);
		}
		else
		{
			$result = false;
		}
		
		return $result;
	}
	
	
	public function checkIpInAllowedLists($ip)
	{
		$result=false;
			
		if($this->getConfig('use_allow_list_ip'))
		{
			$ipAllowedListTable = $this->sm->get('IpAllowedListTable');
			$result = $ipAllowedListTable->is_allowed($ip);	
		}
		else
		{
			$result=true;
		}
		return $result;
	}
	
	public function getConfig($name=false)
	{
		if(!isset($this->config))
		{
			$config = $this->sm->get('config');
			if(isset($config['auth'])) $this->config = $config['auth'];
			else throw new Exception('Not found section configuration "auth"');
		}
		
		if($name)
		{
			if(!isset($this->config[$name])) throw new Exception("Not found section configuration of auth '$name'");
			else return $this->config[$name];
		}
		else return $this->config;
	}
	
	/**
	*  Возвращает сообщение по коду ошибки
	*/
	public function getMessageByCode($code)
	{
		$vulnerability = 0;
		switch($code)
		{
			# Код ошибки отсутствует
			case self::CODE_ACCESS_NULL:
				$message = '';
				break;
			# Доступ разрешен
			case self::CODE_ACCESS_IS_ALLOWED:
				$message = '';
				break;
			# Доступ запрещен
			case self::CODE_ACCESS_IS_DENIED : 
				$message = 'Доступ запрещен!'; 
				break;
			# Таймаут логина
			case self::CODE_ACCESS_IS_DENIED_BY_TIMEOUT : 
				$message = 'Таймаут логина!'; 
				break;
			# Неверный логин или пароль
			case self::CODE_ACCESS_IDENTITY_FAILED:
				$message = 'Неверный логин или пароль'; 
				break;
			# Пользователь заблокирован
			case self::CODE_ACCESS_IS_USER_BLOCKED:
				$message = 'Пользователь заблокирован'; 
				$vulnerability = 1;
				break;
			# Временная блокировка пользователя
			case self::CODE_ACCESS_IS_USER_TEMPORARILY_BLOCKED:
				$user = $this->getServiceLocator()->get('User');
				if($user->temporary_block) $date_end_temporary_block = date('d.m.Y H:i', strtotime($user->temporary_block));
				else $date_end_temporary_block = "неизвестного времени!";
				$message = 'Пользователь заблокирован до ' . $date_end_temporary_block; 
				$vulnerability = 1;
				break;
			# Превышено число анонимных сессий для данного ip
			case self::CODE_ACCESS_EXCEEDED_NUMBER_ALLOWED_SESSIONS_FOR_IP:
				$authentication = $this->getServiceLocator()->get('Authentication');
				$ip = $authentication->getIpAddress();
				 
				$message = "Превышено число анонимных сессий для $ip. Необходима аутентификация!"; 
				$vulnerability = 1;
				break;
			default: 
				$message = $code;
				$vulnerability = 1;
		}
		
		
		if($vulnerability)
		{
			$authConfig = $this->sm->get('AuthConfig');
			$show_private_access_error = $authConfig['show_private_access_error'];
			if(!$show_private_access_error) $message = 'Неверный логин или пароль'; 
		}
		
		return $message;
	}
	
	/**
	*  Смена идентификатора в целях безопасности кражи куки
	*/
	public function session_regenerate_id()
	{
		$config = $this->sm->get('AuthConfig');
		if($config->session_regenerate) 
		{
			if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH']!="XMLHttpRequest")
			{
				if(session_id()) session_regenerate_id(true);	
			}
		}
	}
}