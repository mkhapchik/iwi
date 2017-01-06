<?php
namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Storage\Session as SessionAuth;
use Zend\Session\Container;
use Auth\Form\LoginForm;
use Auth\Service\Authorization as AuthorizationService;
use Auth\Model\SessionTable;
use \Exception;

class AuthenticationController extends AbstractActionController
{
	public function _isAuthorize()
	{
		return false;
	}
	
	public function loginAction()
	{		
		//$config = $this->getServiceLocator()->get('config');
		$authConfig = $this->getServiceLocator()->get('AuthConfig');
		
		$codeAccess = $this->params()->fromRoute('codeAccess', AuthorizationService::CODE_ACCESS_NULL);
		$is_success = $this->params()->fromRoute('is_success', 1);
		//$is_ajax = $this->params()->fromRoute('is_ajax', 0);		
		
		$authorizationService = $this->getServiceLocator()->get('AuthorizationService');
		$message = $authorizationService->getMessageByCode($codeAccess);

		$form = new LoginForm('loginForm', $authConfig);
		$form->setServiceLocator($this->getServiceLocator());
		
		if($form->getCounter()>$authConfig['max_count_fail_to_show_captcha']) 
		{
			$form->addCaptcha();
		}
		
		/*
		$viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
		$url_function = $viewHelperManager->get('url');
		$form_action = $url_function('auth/login');
		$form->setAttribute('action', $form_action);
		*/
        $request = $this->getRequest();
		$is_xmlhttprequest = $this->isXmlHttpRequest();
		$redirect_link = false;
		$is_post = 0;
		if ($request->isPost() && $request->getPost('submit', false)!==false) 
		{
			$is_post = 1;
			try
			{
				$form->setData($request->getPost());
				
				if ($form->isValid()) 
				{
					$form->resetCounter();
					$dataForm = $form->getData();
					
					$userTable = $this->getServiceLocator()->get('UserTable');
					
					$authentication = $this->getServiceLocator()->get('Authentication');
					$userData = $authentication->authenticate($dataForm['login'], $dataForm['pwd']);
					
					if($userData!==false)
					{
						$user = $this->getServiceLocator()->get('User');
						$user->exchangeArray((array)$userData);
												
						if($user->isBlockedFlag())
						{
							throw new Exception(AuthorizationService::CODE_ACCESS_IS_USER_BLOCKED);
						}
						else if($user->isTemporarilyBlocked())
						{
							throw new Exception(AuthorizationService::CODE_ACCESS_IS_USER_TEMPORARILY_BLOCKED);
						}
						else
						{	
							$userTable->unlockAll($user->id);
							$authentication->updateSession($user->id);
							
							$this->resetCountRefreshCaptcha();
							
							$is_success=1;
							$message = 'Вход выполнен успешно';

							if($codeAccess==AuthorizationService::CODE_ACCESS_NULL)
							{
								$route_success_redirect_name = $authConfig['success_redirect']['route_name'];
								$route_success_redirect_params = $authConfig['success_redirect']['route_params'];
								//$this->redirect()->toRoute($route_success_redirect_name, $route_success_redirect_params);
								$viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
								$urlHelper = $viewHelperManager->get('url');
								
								$redirect_link = $urlHelper($route_success_redirect_name, $route_success_redirect_params);
							}
							else
							{
								$header = $this->params()->fromHeader();
								$redirect_link = $header['Referer'];
								//$this->redirect()->toUrl($url);
							}
							
						}
					}
					else
					{
						//try{
							$userTable->incrementCounterFailures($dataForm['login'], $authConfig['max_counter_failures'], $authConfig['period_temporary_block_super_user']);
						//}
						//catch(Exception $ex)
						//{
							
						//}
						throw new Exception(AuthorizationService::CODE_ACCESS_IDENTITY_FAILED);
					}
				}
				else
				{
					throw new Exception(AuthorizationService::CODE_ACCESS_NULL);
				}
			}
			catch(Exception $e)
			{
				$form->incrementCounter();
				if($form->getCounter()>$authConfig['max_count_fail_to_show_captcha']) 
				{
					$form->addCaptcha();
				}
				$code = $e->getMessage();
				
				$message = $authorizationService->getMessageByCode($code);
				$is_success=0;
			}
		}
		
		$frequency = $authConfig['frequency_of_check_timeout_sec'];
		$refresh_captcha_access = $this->getCountRefreshCaptcha()<$authConfig['max_count_refresh_captcha'] ? 1 : 0;
		
		$title = 'Аутентификация';
		$this->layout()->setVariable('title', $title);
		
		$view_params = array('title'=>$title, 'form' => $form, 'is_success'=>$is_success, 'message'=>$message, 'is_xmlhttprequest' => $is_xmlhttprequest, 'codeAccess'=>$codeAccess, 'frequency'=>$frequency, 'refresh_captcha_access'=>$refresh_captcha_access);
		
		if($is_post)
		{
			if($is_xmlhttprequest)
			{		
				$view_params['form_messages'] = $form->getMessages();
			
				if($is_success && $redirect_link!==false) $view_params['redirect'] = $redirect_link;
				if($form->has('captcha'))
				{
					$formGroupCaptcha = $this->getServiceLocator()->get('ViewHelperManager')->get('formGroupCaptcha');
							
					$view_params['captcha_html'] = $formGroupCaptcha()
						->canRefresh($refresh_captcha_access)
						->render($form->get('captcha')); 
				}
				
				
				echo json_encode($view_params);
				exit();
			}
			elseif($is_success && $redirect_link!==false)
			{
				$this->redirect()->toUrl($redirect_link);
			}
		}
		
		$view = new ViewModel($view_params);
		return $view;
	}

	public function logoutAction()
	{
		//die('qwe');
        // is ajax - logout and echo
        
        //$session = $this->getServiceLocator()->get('Session');
	
		$authentication = $this->getServiceLocator()->get('Authentication');
		$authentication->updateSession(null);
		
		//$user =  $this->getServiceLocator()->get('User');
		//$authentication->deleteAllSession($user->id);

		$AuthorizationService = $this->getServiceLocator()->get('AuthorizationService');
		$AuthorizationService->session_regenerate_id();
		
        if(!$this->isXmlHttpRequest())
        {        
            $config = $this->getServiceLocator()->get('AuthConfig');
            $route_logout_redirect_name = $config->logout_redirect['route_name'];
            $route_logout_redirect_params = $config->logout_redirect['route_params'];
            
            $this->redirect()->toRoute($route_logout_redirect_name, $route_logout_redirect_params);
        }
	}
	
	public function refreshcaptchaAction()
	{
		$authConfig = $this->getServiceLocator()->get('AuthConfig');
		
		$ref_captcha_counter = new Container(__CLASS__);
		
		$src = false;
		$id=false;
		
		if($this->getCountRefreshCaptcha()<$authConfig['max_count_refresh_captcha'])
		{
			$this->incrementCountRefreshCaptcha();
		
			$form = new LoginForm('loginForm');
			
			if($form->getCounter()>$authConfig['max_count_fail_to_show_captcha']) $form->addCaptcha();
			
			$captcha_element = $form->get('captcha');
			if($captcha_element)
			{
				$captcha = $captcha_element->getCaptcha();
				$suf = $captcha->getSuffix();
				$id = $captcha->generate();
				$img_url = $captcha->getImgUrl();
				$src = $img_url.$id.$suf;
			}
		}
		
		$refresh_captcha_access = $this->getCountRefreshCaptcha()<$authConfig['max_count_refresh_captcha'] ? 1 : 0;
		
		echo json_encode(array('captcha_src'=>$src, 'captcha_id'=>$id, 'refresh_captcha_access'=>$refresh_captcha_access));
		exit();
	}
	
	private function getCountRefreshCaptcha()
	{
		$ref_captcha_counter = new Container(__CLASS__);
		if(isset($ref_captcha_counter->counter)) return $ref_captcha_counter->counter;
		else return 0;
	}
	
	private function incrementCountRefreshCaptcha()
	{
		$ref_captcha_counter = new Container(__CLASS__);
		if(!isset($ref_captcha_counter->counter)) $ref_captcha_counter->counter=1;
		else $ref_captcha_counter->counter++;
	}
	
	private function resetCountRefreshCaptcha()
	{
		$ref_captcha_counter = new Container(__CLASS__);
		$ref_captcha_counter->getManager()->getStorage()->clear(__CLASS__);
	}
    
    private function isXmlHttpRequest()
    {
        $request = $this->getRequest();
		$is_xmlhttprequest = (bool)@$request->isXmlHttpRequest();
        return $is_xmlhttprequest;
    }
}