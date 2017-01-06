<?php
namespace Application\Service;

use Application\TreeObjectInterface;
use Exception;

class Tree
{
    private $sourceData;
    private $preparedData;
	private $tree;
    

    public function setSourceData($sourceData)
    {
        $this->sourceData = $sourceData;
    }
    
    public function createTree($branchId = 0)
    {
        if(!isset($this->sourceData)) throw new Exception("Source data is not installed. Use the method 'setSourceData'");
        
        $this->prepareData();
        
        if(array_key_exists($branchId, $this->preparedData)) return $this->makeTree($this->preparedData[$branchId]);
        else return null;

    }
    
    private function prepareData()
    {
        $this->preparedData = array();
        
        foreach($this->sourceData as $object)
        {
            if($object instanceof TreeObjectInterface)
                $this->preparedData[$object->getParentId()][] = $object;    
            else 
                throw new Exception("The object ".get_class($object) ." must implement the interface Application\TreeObjectInterface");
        }
    }
    
    private function makeTree($branch, $level=0)
    {
        if(!is_array($this->preparedData)) throw new Exception("Data is not prepared");
        
        foreach($branch as $object)
        {
            $id = $object->getId();
            if(array_key_exists($id, $this->preparedData))
            {
                $children = $this->makeTree($this->preparedData[$id], $level+1);
                $object->setChildren($children);
            }
        }
        
        return $branch;
    }
}
?>