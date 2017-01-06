<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;

class FormGroupPwdGen extends FormGroup
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
				'class'=>'btn btn-default password-generator',
				'type'=>'button',
				'data-toggle'=>'tooltip',
				'title'=>'Сгенерировать',
				))
			->openTag();
			
		$c .= $this->getView()->tag('span')->setAttributes(array('class'=>'glyphicon glyphicon-refresh'))->render();	
			
		$c .= $this->getView()->tag('button')->closeTag();
		$c .= $this->getView()->tag('span')->closeTag();
		
		return $c;
	}
	
	

	

	
}

?>