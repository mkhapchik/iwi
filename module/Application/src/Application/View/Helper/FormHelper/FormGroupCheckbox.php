<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;
use Exception;

class FormGroupCheckbox extends FormGroup
{
	protected $labelAttributes=array();
	
	public function getLabel(ElementInterface $element)
	{
		return '';
	}
	
	public function gethelpBlock(ElementInterface $element)
	{
		return '';
	}
	
	public function getInputGroup(ElementInterface $element)
	{
		$inputGroup = '';
		$inputGroup .= $this->getView()->tag('div')->setAttributes(array('class'=>'checkbox'))->openTag();
		$inputGroup .= $this->getView()->tag('label')->setAttributes($this->getLabelAttributes($element))->openTag();
		
		$inputGroup .= $this->getView()->formElement($element);
		$inputGroup .= 	$element->getLabel();
	
		$inputGroup .= $this->getView()->tag('label')->closeTag();
		$inputGroup .= $this->getView()->tag('div')->closeTag();
			
		return $inputGroup;
	}
}

?>