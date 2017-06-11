<?php
namespace Settings\Entity;

use Settings\Entity\Category;

class Setting
{
    const CATEGORY_PREFIX = 'category_';

    public $id;
    public $name;
    public $label;
    public $value;
    public $description;

    /**
     * @type Category
     */
    public $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function exchangeArray($data)
    {
        foreach ($data as $prop => $value) {
            if(strpos($prop, self::CATEGORY_PREFIX)===0){
                $categoryProp = substr($prop, strlen(self::CATEGORY_PREFIX));
                if(property_exists('Settings\Entity\Category', $categoryProp)){
                    $this->category->$categoryProp = $value;
                }
            }else{
                if (property_exists(self::class, $prop)){
                    $this->$prop = $value;
                }
            }

        }
    }

}