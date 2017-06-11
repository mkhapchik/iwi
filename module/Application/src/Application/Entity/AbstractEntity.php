<?php
namespace Application\Entity;

class AbstractEntity{

    public function exchangeArray($data){
        foreach ($data as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
