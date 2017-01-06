<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;
use Exception;

class FormGroupSubmit extends FormGroup
{
	protected $labelAttributes=array();
	protected $inputGroupAttributes = array(
		'class'=>'btn btn-primary btn-lg'
	);
	
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
		$attributes = $this->getInputGroupAttributes();
		foreach($attributes as $name=>$value) $element->setAttribute($name, $value);
		$inputGroup = $this->getView()->formElement($element);
					
		return $inputGroup;
	}
}

?>