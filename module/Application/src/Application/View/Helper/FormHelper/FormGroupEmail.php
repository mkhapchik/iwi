<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;

class FormGroupEmail extends FormGroup
{
	protected $inputGroupAttributes=array(
		'class'=>'input-group'
	);
	
	protected function getBeforeElement()
	{
		$c = '';
		$c .= $this->getView()->tag('span')->setAttributes(array('class'=>'input-group-addon'))->openTag();
		$c .= '@';
		$c .= $this->getView()->tag('span')->closeTag();
		
		return $c;
	}
}

?>