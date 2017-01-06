<?php
namespace Application\View\Helper\FormHelper;

use Zend\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class FormGroup extends AbstractHelper //extends FormRow
{
	protected $is_error=0;
	
	protected $options=array();
	
	protected $wrapperAttributes = array(
		'class'=>'form-group'
	);
	
	protected $labelAttributes=array(
		'class'=>'control-label'
	);
	
	protected $inputGroupAttributes=array(
		'class'=>''
	);
	
	public function __invoke(ElementInterface $element=null)
	{
		if(!$element) return $this;
		return $this->render($element, array());
	}
	
	public function isError(ElementInterface $element)
	{
		return (bool)count($element->getMessages());
	}
	
	public function setWrapperAttributes($wrapperAttributes)
	{
		$this->wrapperAttributes = $wrapperAttributes;
		return $this;
	}
	
	public function getWrapperAttributes()
	{
		return $this->wrapperAttributes;
	}
	
	public function setLabelAttributes($labelAttributes)
	{
		$this->labelAttributes = $labelAttributes;
		return $this;
	}
	
	public function getLabelAttributes($element)
	{
		$labelAttributes = $element->getLabelAttributes();
		$result_attributes = array_merge($labelAttributes, $this->labelAttributes);
		return $result_attributes;
	}
	
	public function setInputGroupAttributes($inputGroupAttributes)
	{
		$this->inputGroupAttributes	= $inputGroupAttributes;
		return $this;
	}
	
	public function getInputGroupAttributes()
	{
		return $this->inputGroupAttributes;
	}
	
	public function render(ElementInterface $element, $options = array())
    {
		$content = '';
		
		#wrapper
		$content .= $this->getView()->tag('div')->setAttributes($this->getWrapperAttributes())->openTag();
		$content .= $this->getView()->tag('div')->setAttributes(array('class'=>$this->isError($element) ? 'has-error' : ''))->openTag();
		
		#label
		$content .= $this->getLabel($element);
		
		#input_group
		$content .= $this->getInputGroup($element);
				
		#help-block
		$content .= $this->gethelpBlock($element);
			
		#/wrapper
		$content .= $this->getView()->tag('div')->closeTag();
		$content .= $this->getView()->tag('div')->closeTag();
		
        return $content;
    }
	
	// добавить for для label если есть id
	public function getLabel(ElementInterface $element)
	{
		$label = '';
		$label .= $this->getView()->tag('label')->setAttributes($this->getLabelAttributes($element))->openTag();
		$label .= $element->getLabel();
		$label .= $this->getView()->tag('label')->closeTag();
		
		return $label;
	}
	
	public function getInputGroup(ElementInterface $element)
	{
		$inputGroup = '';
		$inputGroup .= $this->getView()->tag('div')->setAttributes($this->getInputGroupAttributes())->openTag();
		
		$class = $element->getAttribute('class');
		if(stripos($class, 'form-control')===false) 
		{
			$class .= (empty($class) ? '' : ' ') . 'form-control';
			$element->setAttribute('class', $class);
		}
		
		$inputGroup .= $this->getBeforeElement();
		
		$inputGroup .= $this->getView()->formElement($element);
		
		$inputGroup .= $this->getAfterElement();
		
		$inputGroup .= $this->getView()->tag('div')->closeTag();
		
		return $inputGroup;
	}
	
	public function gethelpBlock(ElementInterface $element)
	{
		$hb = '';
		$hb .= $this->getView()->tag('span')->setAttributes(array('class'=>'help-block'))->openTag();
		$hb .= $this->getView()->formElementErrors($element, array('class' => 'list-unstyled help-inline'));
		$hb .= $this->getView()->tag('span')->closeTag();
		
		return $hb;
	}
	
	protected function getOptions()
	{
		return $this->options;
	}
	
	protected function setOptions($options)
	{
		$this->options = $options;
	}
	
	protected function getBeforeElement()
	{
		return "";
	}
	
	protected function getAfterElement()
	{
		return "";
	}
}

?>