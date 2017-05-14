<?php
namespace \Users\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Auth\Model\Role;

class RoleService implements ServiceLocatorAwareInterface{
    protected $sm;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->sm;
    }

    public function addRole(Role $role){
        $roleTable = $this->getRoleTable();
        $roleTable->addRole($role);
        $id = $roleTable->getLastInsertValue();
        $role->exchangeArray(array('id'=>$id));

        $user = $this->getUser();
        $this->getPermissionsModel()->add($id, null, $user->id);
    }

    public function editRole(Role $role){

    }

    public function deleteRole(Role $role){
        $roleTable = $this->getRoleTable();
        // delete from permissions role
        // delete from user role map
        // delete from role table
    }



    private function getRoleTable(){
        return $this->sm->get('RoleTable');
    }

    private function getPermissionsModel(){
        return $this->sm->get('Users\Model\PermissionsRolesModel');
    }

    private function getUser(){
        $user = $this->serviceLocator->get('User');
        return $user;
    }


}