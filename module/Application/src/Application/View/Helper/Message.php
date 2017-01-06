<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class Message extends AbstractHelper
{
	private $classMap = array(
		1=>"bg-success",
		2=>"bg-info",
		3=>"bg-primary",
		-1=>"bg-warning",
		0=>"bg-danger",
		'success'=>"bg-success",
		'info'=>"bg-info",
		'primary'=>"bg-primary",
		'warning'=>"bg-warning",
		'danger'=>"bg-danger",
	);
	
	public function __invoke($message, $is_success='info', $class='message')
	{
		$content = '';
		if(!empty($message))
		{
			$class_span = array_key_exists($is_success, $this->classMap) ? $this->classMap[$is_success] : '';
			
			$content .= $this->getView()->tag('div')->setAttributes(array('class'=>$class))->openTag();
			$content .= $this->getView()->tag('span')->setAttributes(array('class'=>$class_span))->openTag();
			$content .= $message;
			$content .= $this->getView()->tag('span')->closeTag();
			$content .= $this->getView()->tag('div')->closeTag();
		}
		
		return $content;
	}
	
	
	
	
}

?>