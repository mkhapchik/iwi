<?php
namespace Cron\Controller;

use Zend\View\Model\ViewModel;
use \Exception;
use SysNotifications\Model\SysNotifications;
use Cron\Controller\AbstractCronController;

class NotifTasksController extends AbstractCronController
{		
	public function __construct()
	{
		parent::__construct();
	}
	
	public function sendAction()
	{
		$settingsTable = $this->getServiceLocator()->get('SettingsTable');
		$settings = $settingsTable->getSettings();
				
		if($settings['EMAIL_NOTIFICATION'])
		{
			$sysNotifications = $this->serviceLocator->get('SysNotifications');
			$notifications = $sysNotifications->getSendNotif();
			
			try
			{
				$options = array(
					'type'=>$settings['SMTP_TYPE'], 
					'smtp'=>array(
						'server'=>$settings['SMTP_SERVER'],
						'login'=>$settings['SMTP_LOGIN'],
						'pswd'=>$settings['SMTP_PSWD'],
						'port'=>$settings['SMTP_PORT'],
					),
				);
		
				$SendMailService = $this->getServiceLocator()->get('SendMailService');
				$SendMailService->setOptions($options);
				$SendMailService->init();
				
				$email = explode(',', preg_replace('/\s+/','',$settings['EMAIL_ADDRESS_TO_SEND_NOTIFICATIONS']));
								
				foreach($notifications as $notif)
				{
					try
					{
						$message = array(
							'to'=>$email,
							'from'=>$settings['MAIL_FROM'],
							'subject'=>"Ошибка системы #$notif[id]",
							'body'=>$notif['error_text']
						);
					
						$SendMailService->sendMessage($message);
					}
					catch(Exception $e)
					{
						$this->setMessage($e->getMessage());
						$next_send_attempt = date("Y-m-d H:i:s", time()+(int)$settings['NOTIF_REPEAT_PERIOD']);
						$id = $notif['id'];
						$sysNotifications->setNextSendAttempt($id, $next_send_attempt);
					}
				}
			}
			catch(Exception $e)
			{
				foreach($notifications as $notif)
				{
					$next_send_attempt = date("Y-m-d H:i:s", time()+(int)$settings['NOTIF_REPEAT_PERIOD']);
					$id = $notif['id'];
					$sysNotifications->setNextSendAttempt($id, $next_send_attempt);
				}
				
				$this->setMessage($e->getMessage());
				$this->_return(0);
			}	
		}
			
		$this->_return(1);
		
		
	}
}