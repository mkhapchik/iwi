<?php
namespace Auth\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;
use Auth\Form\ForgotPasswordForm;

class ForgotPasswordController extends AbstractActionController
{
	public function _isAuthorize()
	{
		return false;
	}
	
	public function requestAction()
	{
		$params = array();
		
		$params['is_success']=1;
		$params['message']='';
		$login = '';
		try
		{
			$request = $this->getRequest();
			if($request->isPost() && $request->getPost('submit', false)!==false)
			{				
				$login = $this->params()->fromPost('login', false);
				if(empty($login)) throw new Exception('Введите Ваш логин');
				
				$userTable = $this->getServiceLocator()->get('UserTable');
				$user = $userTable->getUserByLogin($login);
				
				if(!$user) 
				{
					$escaper = new \Zend\Escaper\Escaper('utf-8');
					throw new Exception("Учётная запись с зарегистрированным логином " . $escaper->escapeHtml($login) . " не найдена");
				}
				
				$token_pwd = md5(uniqid(rand(), 1));
				
				$authConfig = $this->getServiceLocator()->get('authConfig');
				$life_time_sec = time()+$authConfig->token_pwd_life_time_sec;
				$userTable->setTokenPwd($token_pwd, $life_time_sec, $user->id);
				
				$renderer = new \Zend\View\Renderer\PhpRenderer();
				$resolver = new \Zend\View\Resolver\AggregateResolver();
				$map = new \Zend\View\Resolver\TemplateMapResolver(array(
					'email'      => realpath(__DIR__ . '../../../../view/auth/forgot-password/email.phtml')
				));
				$resolver->attach($map);
				$renderer->setResolver($resolver);
				
				$life_time_str = sprintf('%02d ч. %02d м. %02d с.', $authConfig->token_pwd_life_time_sec/3600, ($authConfig->token_pwd_life_time_sec % 3600)/60, ($authConfig->token_pwd_life_time_sec % 3600) % 60); 
				$emailView  = new ViewModel(array(
					'name'=>$user->name, 
					'lifetime'=>$life_time_str,
					'link'=>'/password/change/' . $token_pwd));
				$emailView->setTemplate("email");
				$emailBody = $renderer->render($emailView);
				
				/**
				 *  SEND EMAIL
				 */
				echo "<a href='/password/change/$token_pwd'>ОТЛАДКА - ССЫЛКА ИЗ EMAIL</a>"; 
				exit();
				/******************************************************************/
				
				$email_link = $this->getMailLink($user->email);
				$view_response = new ViewModel(array('email_link'=>$email_link));
				$view_response->setTemplate('auth/forgot-password/response');
				return $view_response;
			}
		}
		catch(Exception $ex)
		{
			$params['is_success']=0;
			$params['message']= $ex->getMessage();
		}
		
		$params['login']=$login;
		
		$view = new ViewModel($params);
		return $view;
	}
	
	
	
	public function changeAction()
	{
		$params = array();
		$token = $this->params()->fromRoute('token', false);
		
		try
		{
			if($token==false) throw new Exception("Неверная или устаревшая ссылка!");
			
			$userTable = $this->getServiceLocator()->get('UserTable');
			
			$userData = $userTable->isAccessTokenPwd($token);
			if(!$userData) throw new Exception("Неверная или устаревшая ссылка!");
			
			$form = new ForgotPasswordForm();
			
			$request = $this->getRequest();
			
			if($request->isPost() && $request->getPost('submit', false)!==false) 
			{
				$form->setData($request->getPost());
				
				if($form->isValid()) 
				{
					$dataForm = $form->getData();
					$newUserData = array();
					
					$bcrypt = new \Zend\Crypt\Password\Bcrypt();
					$newUserData['pwd'] = $bcrypt->create($dataForm['pwd']);
					$newUserData['token_pwd']=null;
					$newUserData['token_pwd_life_time']=null;
					
					$userTable->editUser($newUserData, $userData['id']);
					$authentication = $this->getServiceLocator()->get('Authentication');
					
					if($dataForm['close_session'])
					{
						$authentication->deleteAllSession($userData['id']);		
					}
									
					$authentication->updateSession($userData['id']);
									
					$this->redirect()->toRoute('password/success');			
				}
				else
				{
					$params['is_success']=0;
				}
			}
			else
			{
				$params['is_success']=1;
			}
		
			$params['is_acsess_token']=1;
			$params['form']=$form;
			$params['message']='';
		}
		catch(Exception $ex)
		{
			$params['is_success']=0;
			$params['is_acsess_token']=0;
			$params['message']= $ex->getMessage();
		}
				
		$view = new ViewModel($params);
		return $view;
	}
	
	public function successAction()
	{
		$user = $this->getServiceLocator()->get('User');
		return array('user'=>$user);
	}
	
	private function getMailLink($email)
	{
		$domain = substr(strrchr($email, "@"), 1);
		
		switch($domain)
		{
			case 'gmail.com' : 
				$link = 'mail.google.com';
				break;
			case 'yandex.ru' : 
				$link = 'mail.yandex.ru';
				break;	
			case 'mail.ru' : 
				$link = 'e.mail.ru';
				break;		
			case 'rambler.ru':
				$link = 'mail.rambler.ru';
				break;
			default:
				$link = false;
		}
		
		$link = "http://" . $link;
		return $link;
	}
	
}