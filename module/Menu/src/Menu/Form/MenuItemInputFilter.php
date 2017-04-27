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
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
            'validators'=>array(
                array(
                    'name' =>'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения'
                        ),
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max'      => 255,
                        'messages' => array(
                            \Zend\Validator\StringLength::TOO_LONG => 'Максимальная длина поля не должна превышать %max% символов'
                        )
                    ),
                ),
            )
        ));

        $this->add(array(
            'name'=>'type',
            'required'=>true,
            'validators'=>array(
                array(
                    'name' =>'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения'
                        ),
                    ),
                ),

            )
        ));

        $this->add(array(
            'name'=>'page',
            'required'=>false,
            'validators'=>array(
                array(
                    'name' =>'Callback',
                    'options' => array(
                        'callback' => function($value, $context=array()) {
                            return $context['type']=='page' ? $value!=='' : true;
                        },
                        'messages'=>array(
                            \Zend\Validator\Callback::INVALID_VALUE => 'Это поле обязательно для заполнения'
                        )
                    )
                ),
            )
        ));

        $this->add(array(
            'name'=>'uri',
            'required'=>false,
            'validators'=>array(
                array(
                    'name' =>'Callback',
                    'options' => array(
                        'callback' => function($value, $context=array()) {
                            return $context['type']=='url' ? $value!=='' : true;
                        },
                        'messages'=>array(
                            \Zend\Validator\Callback::INVALID_VALUE => 'Это поле обязательно для заполнения'
                        )
                    )
                ),
            )
        ));

        $this->add(array(
            'name'=>'icon_class',
            'required'=>true,
            'validators'=>array(
                array(
                    'name' =>'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\NotEmpty::IS_EMPTY => 'Это поле обязательно для заполнения'
                        ),
                    ),
                ),
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