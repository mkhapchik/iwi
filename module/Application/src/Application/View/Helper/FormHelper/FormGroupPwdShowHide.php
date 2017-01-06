<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;

class FormGroupPwdShowHide extends FormGroup
{
	protected $inputGroupAttributes=array(
		'class'=>'input-group'
	);
	
	protected function getAfterElement()
	{
		$c = '';
		$c .= $this->getView()->tag('span')->setAttributes(array('class'=>'input-group-btn'))->openTag();
		$c .= $this->getView()->tag('button')
			->setAttributes(array(
				'class'=>'btn btn-default password-toggle pwd-show',
				'type'=>'button',
				'data-toggle'=>'tooltip',
				'title'=>'Показать пароль',
				))
			->openTag();
			
		$c .= $this->getView()->tag('span')->setAttributes(array('class'=>'glyphicon glyphicon-eye-open'))->render();	
			
		$c .= $this->getView()->tag('button')->closeTag();
		$c .= $this->getView()->tag('span')->closeTag();
		
		return $c;
	}
}

?>