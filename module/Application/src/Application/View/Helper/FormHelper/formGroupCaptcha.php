<?php
namespace Application\View\Helper\FormHelper;

use Application\View\Helper\FormHelper\FormGroup;
use Zend\Form\ElementInterface;
use Exception;

class FormGroupCaptcha extends FormGroup
{
	protected $wrapperAttributes = array(
		'class'=>'form-group captcha'
	);
	
	protected $canRefresh=0;
	
	public function getAfterElement()
	{
		$content = "";
		if($this->canRefresh)
		{
			$content .= $this->getView()->tag('a')->setAttributes(array('class'=>'refresh_captcha', 'href'=>'#'))->openTag();
			$content .= "Обновить картинку";
			$content .= $this->getView()->tag('a')->closeTag();
		}
		return $content;
	}
	
	public function canRefresh($canRefresh)
	{
		$this->canRefresh = $canRefresh;
		return $this;
	}

}

?>