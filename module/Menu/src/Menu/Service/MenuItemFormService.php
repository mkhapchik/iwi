<?php
namespace Menu\Service;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MenuItemFormService implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;

    /**
     * Utility function for all those cases where we process forms to store entities within their respective mappers.
     * Psst. This happens often.
     *
     * @param $form
     * @param $response
     * @return Array
     */
    public function processFormWithMapper( Form $form, Array $data, Array &$response )
    {
        $object = null;

        try
        {
            /** @var Form $data */
            $object = $form->getObject();
            $form->setData( $data );

            if( !empty($data['id']) )
                if( $object = $mapper->getRepository()->findOneBy(['id' => $data['id']]) )
                    $form->bind( $object );

            if( $form->isValid() )
            {
                if( $object->getId() )
                    $mapper->update( $form->getObject() );
                else
                    $mapper->save( $form->getObject() );

                $response['success'] = true;
                $response['message'] = "The configuration was successfully saved!";
            }
            else
            {
                $response['message']        = "Hm. That didn't work - check the errors on the form.";
                $response['form_errors']    = $form->getMessages();
            }
        }
        catch( \Exception $x )
        {
            $response['messsage']       = $x->getMessage();
        }

        return $object;
    }
}