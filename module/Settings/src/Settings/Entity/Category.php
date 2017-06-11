<?php
namespace Settings\Entity;

use Application\Entity\AbstractEntity;

class Category extends AbstractEntity{
    public $id;
    public $label;
    public $description;
    public $color;
}