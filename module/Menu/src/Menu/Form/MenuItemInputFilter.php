<?php
namespace Menu\Form;
use Zend\InputFilter\InputFilter;

class MenuItemInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'=>'label',
            'required'=>true,
            'validators'=>array(
                array(
                    'name'=>'StringLength',
                    'options'=>array(
                        'encoding'=>'UTF-8',
                        'min'=>2,
                        'max'=>255
                    )
                )
            )
        ));
        /*
        $this->add(array(
            'name'=>'icon_img',
            'filters'=>array(
                array(
                    'name' => 'File\RenameUpload',
                    'options' => array(
                        'target'          => './data/menu/test.jpg',
                        'overwrite'       => true,
                        //'use_upload_name' => true,
                        //'use_upload_extension'=>true
                    )
                ),

            ),
            'validators'=>array(

            )
        ));
        */
    }
}