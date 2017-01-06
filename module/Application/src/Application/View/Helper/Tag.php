<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class Tag extends AbstractHelper
{
	private $tag_name;
	private $attributes;
	
	public function __invoke($tag_name = 'div')
	{
		$this->tag_name = $tag_name;
		return $this;
	}
	
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
		return $this;
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function openTag()
	{
		$attributes = $this->getAttributes();
        
        if ($attributes) {
            return sprintf("<{$this->tag_name} %s>", $this->createAttributesString($attributes));
        }

        return "<{$this->tag_name}>";
	}
	
	public function closeTag()
	{
		return "</{$this->tag_name}>";
	}
	
	public function render()
	{
		return $this->openTag() . $this->closeTag();
	}
	
	private function createAttributesString($attributes)
	{
		$str = '';
		foreach($attributes as $name=>$value)
		{
			$str .= " $name='$value'";
		}
		
		return $str;
	}
}

?>