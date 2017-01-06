<?php
namespace Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Auth\Model\SessionTable;
use Auth\Service\Authorization;
use Exception;

class Authentication implements ServiceLocatorAwareInterface
{
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
	* Попытка аутентификации по логину и паролю
	* @param $login - логин пользователя
	* @param $pwd - пароль пользователя
	* @return BOOL - результат аутентификации
	*/
	public function authenticate($login, $pwd)
	{
		$authService = $this->getServiceLocator()->get('AuthenticationService');
		
		// Получение адаптера - сделать свой, изменить алгоритм хеширования пароля!			
		$authServiceAdapter = $authService->getAdapter();
		$authServiceAdapter->setIdentity($login);
		$authServiceAdapter->setCredential($pwd);

		$result = $authServiceAdapter->authenticate();
	
		if($result->isValid()) return $authServiceAdapter->getResultRowObject(null, array('pwd'));
		else return false;
	}
	
	/**
	*  Создание сессии авторизации и php сессии
	* @param $userId - идентификатор пользователя
	* @param $parent_session_id - идентификатор сессии создающей текущею сессию
	*/
	public function createSession($userId, $parent_session_id=null)
	{		
		$token = md5(uniqid(rand(), 1));
		$lastActivity = time();
		
		
		$sessionTable = $this->getServiceLocator()->get('SessionTable');
		
		$this->setStorage($token, $lastActivity);
		
		
		$sessionData = array();
		
		$sessionData['token'] = $token;
		$sessionData['user_id'] = $userId;
		$sessionData['last_activity'] = date('Y-m-d H:i:s',$lastActivity);
		$sessionData['starttime'] = date('Y-m-d H:i:s',$lastActivity);
		$sessionData['parent_session_id'] = $parent_session_id;
		
		$sessionData['ip'] = $this->getIpAddress();
								
		$sessionTable->save($sessionData);
		
		
		$config = $this->sm->get('AuthConfig');
		if($config->record_history_sessions)
		{
			$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
			$sessionHistory->addSession($sessionData['token'], $sessionData['ip'], $sessionData['user_id'], $sessionData['starttime']);
		}
	}
	
	public function setStorage($token, $lastActivity)
	{
		$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
		$storageService->set('token', $token);
		$storageService->set('last_activity', $lastActivity);
	}
	
	public function updateSession($userId=null)
	{
		try
		{
			$sessionTable = $this->getServiceLocator()->get('SessionTable');
			
			
			$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
			$token = $storageService->get('token');
						
			$ip = $this->getIpAddress();
			if(!$sessionTable->getSession($token, $ip)) throw new Exception("session width token of $token not found");
			
			//closeSession
			$sessionTable->updateSession(array('user_id'=>$userId), $token);
			$updateTime = date("Y-m-d H:i:s");
			
			$config = $this->sm->get('AuthConfig');
			if($config->record_history_sessions)
			{
				$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
				$sessionHistory->closeSession($token, 'change', $updateTime);
				$sessionHistory->addSession($token, $ip, $userId, $updateTime);
			}
		}
		catch(Exception $e)
		{
			$this->createSession($userId);
		}
		
		$this->deleteMultiSession($userId);
	}
	
	private function deleteMultiSession($userId)
	{
		$config = $this->sm->get('AuthConfig');
		if($userId && !$config->multi_session_for_user)
		{
			$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
			$token = $storageService->get('token');
			
			$sessionTable = $this->getServiceLocator()->get('SessionTable');
			$sessionTable->deleteSession($userId, $token);
			
			if($config->record_history_sessions)
			{
				$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
				$sessionHistory->closeMultiSession($userId, $token);
			}
		}
	}
	
	/**
	 *  Удаление всех сессий пользователя
	 */
	public function deleteAllSession($userId)
	{
		$config = $this->sm->get('AuthConfig');
		$user = $this->getServiceLocator()->get('user');
		if($userId)
		{
			$sessionTable = $this->getServiceLocator()->get('SessionTable');
			
			if(!$user->isAuth())
			{
				$storageService = $this->getServiceLocator()->get('\Auth\Service\StorageService');
				$token = $storageService->get('token');
				$sessionTable->deleteSessionByToken($token); //покрывает случай неавторизованного пользователя
			}
			
			$sessionTable->deleteSession($userId);
				
			if($config->record_history_sessions)
			{
				$sessionHistory = $this->getServiceLocator()->get('SessionHistory');
				$sessionHistory->closeMultiSession($userId);
				if(!$user->isAuth()) $sessionHistory->closeSession($token);
			}
		}
	}
	
	public function getIpAddress()
	{
		$remote = new \Zend\Http\PhpEnvironment\RemoteAddress();
		return $remote->getIpAddress();
	}
	
	public function checkSessionLimitForIp()
	{
		$result = 0;	
		$config = $this->sm->get('AuthConfig');
		if(!$config->max_count_sessions_for_ip) $result = 1;
		else
		{
			$sessionTable = $this->getServiceLocator()->get('SessionTable');
			$ip = $this->getIpAddress();
			$count = $sessionTable->getCountSession($ip, $config->period_max_count_sessions_for_ip);
			
			if($count<=$config->max_count_sessions_for_ip) $result = 1;
			
		}
	
		return $result;
	}
}
?>