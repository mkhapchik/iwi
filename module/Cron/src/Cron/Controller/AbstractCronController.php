<?php
namespace Cron\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use \Exception;

abstract class AbstractCronController extends AbstractActionController
{		
	protected $exclusive_filepath;
		
	public function __construct()
	{
		$file_name = strtolower(str_replace("\\", "_", get_class($this)));
		$this->exclusive_filepath = getcwd() . '/tmp/' . $file_name . '.txt';
	}
	
	protected function exclusive()
	{
		$f = @fopen($this->exclusive_filepath,"a+");
		if(!$f) throw new Exception('Cannot open exclusive file');
		
		$that = $this;
		register_shutdown_function(function() use($f, $that){
			$that->exit_function($f);
		}); 
		
		if(!flock($f, LOCK_EX | LOCK_NB)) throw new Exception("CRON: Another process started, Cannot obtain exclusive on ".$this->exclusive_filepath);
	}
	
	protected function setMessage($msg)
	{
		$msg = $msg . "\n";
		//$msg = nl2br($msg);
		echo $msg;
	}
	
	protected function _return($res)
	{
		$this->setMessage("exit $res");
		exit((int)$res);
	}
	
	public function exit_function($f)
	{
		
		$this->setMessage("call exit function");
		if($f)
		{
			flock($f, LOCK_UN);	
			fclose($f);
		}
		else
		{
			$this->setMessage("CANNOT unlock file. File resource not found!");
			$this->_return(0);
		}
		
	}

}