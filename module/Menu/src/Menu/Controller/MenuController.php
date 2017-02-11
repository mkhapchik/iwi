<?php
namespace Menu\Controller;

use Menu\Entity\MenuItem;
use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;
use Application\Service\Tree;
use Exception;
use Zend\Form\Form;

class MenuController extends PageController
{
    public function editAction()
    {
        $this->registerReferer();

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) return $this->redirect()->toRoute('menu', array('action' => 'add'));

        $menuTable = $this->serviceLocator->get('Menu\Model\MenuTable');
        $menu = $menuTable->getMenu($id);
        if (!$menu) return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));

        $user = $this->serviceLocator->get('User');
        $route = $this->serviceLocator->get('Route');

        # получение разрешенных действий
        $allowedActionsList = $this->getAllowedActionList();
        $allowedActionsKeys = array_keys($allowedActionsList);

        # получение прав доступа страницы по группам для формы
        $roleTable = $this->serviceLocator->get('RoleTable');
        $permissionsTable = $this->serviceLocator->get('PermissionsTable');

        $selectAllowedRoles = $roleTable->createSelectAllowedRolesByUserId($user->id);
        $permissions = $permissionsTable->getPermissions($route->id, $selectAllowedRoles, $allowedActionsKeys);
        $menu->setPermissions($permissions);

        # получение списка выбираемых ролей для формы
        $roles = $roleTable->getSelectedRolesForPermissions($route->id, $user->id);

        # получение пунктов меню
        $menuItemsTable = $this->serviceLocator->get('Menu\Model\MenuItemsTable');
        $items = $menuItemsTable->getItems($menu->id);
        $menu->setItems($items);

        $serviceTree = new Tree();
        $serviceTree->setSourceData($items);
        $tree = $serviceTree->createTree();

        # инициализация формы
        $form = $this->serviceLocator->get('Menu\Form\MenuForm');
        $form->setSelectedRoles($roles);
        $form->init($route->id, $allowedActionsList);
        $form->bind($menu);

        $is_success = 1;
        $message = '';

        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $form->setData($request->getPost());
                if (!$form->isValid()) throw new Exception('Некорректные данные');

                # сохранение меню
                $menuTable = $this->serviceLocator->get('Menu\Model\MenuTable');
                $menuTable->updateMenu($menu);


                # изменение активности 
                if (in_array('activeToggle', $allowedActionsKeys)) {
                    if ($menu->isActive()) $menuTable->setActive($menu->id);
                    else $menuTable->unsetActive($menu->id);
                }

                # сохранение прав доступа
                $permissionsService = $this->serviceLocator->get('PermissionsService');
                $permissionsService->savePermissions($route->id, $menu->getPermissions(), $allowedActionsKeys);

                # сохранение пунктов меню
                $items = $menu->getItems();
                $this->saveItemsPosition($items);

                return $this->redirectToRefererOrDefaultRoute('pages-manager', array('action' => 'view'));

            } catch (Exception $ex) {
                $is_success = 0;
                $message = $ex->getMessage();
            }
        }

        $title = "Редактирование меню $menu->label";

        $referer = $this->getReferer('pages-manager');

        $params = array(
            'id' => $id,
            'form' => $form,
            'is_success' => $is_success,
            'message' => $message,
            'title' => $title,
            'allowedActions' => $allowedActionsKeys,
            'referer' => $referer,
            'menu' => $menu,
            'tree' => $tree,
        );

        $view = new ViewModel($params);
        $view->setTemplate('menu/menu/form');

        $this->layout()->setVariable('title', $title);

        return $view;
    }

    private function saveItemsPosition($items)
    {
        $menuItemsTable = $this->serviceLocator->get('Menu\Model\MenuItemsTable');
        if (is_array($items)) {
            foreach ($items as $item) {
                $menuItemsTable->setItemPosition($item);
            }
        }
    }

    public function delAction()
    {
        $this->registerReferer();

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) throw new Exception("id is not isset");

        $menuTable = $this->serviceLocator->get('Menu\Model\MenuTable');
        $menu = $menuTable->getMenu($id);
        if (!$menu) throw new Exception("menu #$id not found");

        $permissionsService = $this->serviceLocator->get('PermissionsService');
        $permissionsService->deletePermissions($menu->route_id);

        $routesTable = $this->serviceLocator->get('RoutesTable');
        $routesTable->delRoute($menu->route_id);

        $menuItemsTable = $this->serviceLocator->get('Menu\Model\MenuItemsTable');
        $menuItemsTable->delItems($menu->id);

        $menuTable->delMenu($menu->id);

        $this->redirect()->toRoute('menu-manager', array('action' => 'view'));
    }

    public function activeToggleAction()
    {
        /*
        $is_success = 0;
        $message = '';

        try
        {
            $menuModel = $this->serviceLocator->get('Menu\Model\MenuTable');


            //$status
        }
        catch(Exception $e)
        {
            $message=$e->getMessage();
            $is_success = 0;
        }

        $params = array('is_success'=>$is_success, 'message'=>$message, 'status'=>$status);
        echo json_encode($params);
        exit();
        */
    }

    public function addItemAction()
    {
        $menuId = (int)$this->params()->fromRoute('id', null);
        if (!$menuId) return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));

        return $this->editItem($menuId);
    }

    public function editItemAction()
    {
        $menuId = (int)$this->params()->fromRoute('id', null);
        if (!$menuId) return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));

        $itemId = (int)$this->params()->fromRoute('itemId', null);
        if (!$itemId) return $this->redirect()->toRoute('menu', array('action' => 'addItem', 'id'=>$menuId));

        return $this->editItem($menuId, $itemId);
    }

    private function editItem($menuId, $itemId=null){
        $is_success = 1;
        $message = '';

        $item = $this->getItem($menuId, $itemId);
        $form = $this->getItemForm($item);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray()
            //$request->getFiles()->toArray()
            );

            try {
                $this->processItem($form, $item, $postData);
                return $this->redirect()->toRoute('menu', array('action' => 'edit', 'id'=>$item->parent_menu_id));
            }
            catch (Exception $ex) {
                $is_success = 0;
                $message = $ex->getMessage();
            }
        }


        $title = $itemId ? "Редактирование пункта меню" : "Добавление пункта меню";

        $this->layout()->setVariable('title', $title);

        $allowedActions = $this->getAllowedActionList(true);

        $params = array(
            'title' => $title,
            'form' => $form,
            'is_success' => $is_success,
            'message' => $message,
            'allowedActions' => $allowedActions,
            'item'=>$item
        );

        $view = new ViewModel($params);
        $view->setTemplate('menu/menu/formItem');

        return $view;
    }

    private function getItem($menuId, $itemId=null, $parentId=0){
        if($itemId) {
            $sm = $this->getServiceLocator();
            $menuItemsTable = $sm->get('Menu\Model\MenuItemsTable');
            $item = $menuItemsTable->getMenuItem($itemId);
        }else{
            $item = new MenuItem();
            $item->parent_menu_id = $menuId;
            $item->parent_item_id = $parentId;
        }
        return $item;
    }

    private function getItemForm($item){
        $sm = $this->getServiceLocator();

        $form = $sm->get('Menu\Form\MenuItemForm');
        $form->bind($item);

        if ($item->type === 'page' && $item->route_id) {
            $pageModel = $sm->get('Pages\Model\PageModel');
            $page = $pageModel->getPageByRouteId($item->route_id);
            if ($page) {
                $form->setData(array('page' => $page->id));
            }
        }

        return $form;
    }

    private function processItem(Form &$form, MenuItem &$item, $postData)
    {
        $sm = $this->getServiceLocator();

        $form->setData($postData);

        if (!$form->isValid()) {
            throw new Exception('');
        }

        if ($item->type === 'page') {
            $item->uri = null;
            $pageId = (int)$postData['page'];
            if ($pageId) {
                $pageModel = $sm->get('Pages\Model\PageModel');
                $page = $pageModel->getPageById($pageId);

                if ($page) $item->route_id = $page->route_id;
                else throw new Exception("Page #$pageId not found.");
            }
        } else {
            $item->route_id = null;
        }

        $menuItemsTable = $sm->get('Menu\Model\MenuItemsTable');

        if($item->id){
            $menuItemsTable->updateMenuItem($item);
        }else{
            $item->id = $menuItemsTable->addMenuItem($item);
        }
    }

    public function delItemAction()
    {
        $menuId = (int)$this->params()->fromRoute('id', null);
        $itemId = (int)$this->params()->fromRoute('itemId', null);
        if(!$itemId) throw new Exception('Invalid parameter itemId');

        $tree = $this->createTree($menuId, $itemId);
        $this->delChildrenItems($tree);

        $menuItemsTable = $this->getServiceLocator()->get('Menu\Model\MenuItemsTable');
        $menuItemsTable->delMenuItem($itemId);

        if ($menuId) {
            return $this->redirect()->toRoute('menu', array('action' => 'edit', 'id'=>$menuId));
        }
        else{
            return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));
        }
    }

    private function createTree($menuId, $parentId=0){
        $sm = $this->getServiceLocator();
        $menuItemsTable = $sm->get('Menu\Model\MenuItemsTable');
        $items = $menuItemsTable->getItems($menuId);
        $serviceTree = new \Application\Service\Tree();
        $serviceTree->setSourceData($items);
        $tree = $serviceTree->createTree($parentId);
        return $tree;
    }

    private function delChildrenItems($tree){
        $menuItemsTable = $this->getServiceLocator()->get('Menu\Model\MenuItemsTable');
        foreach($tree as $item){
            if($item->hasChildren()){
                $this->delChildrenItems($item->getChildren());
            }
            $menuItemsTable->delMenuItem($item->id);
        }
    }

    public function activeToggleItemAction()
    {

    }

    public function orderItemsAction()
    {

    }

    public function getActionList()
    {
        $actions = array(
            'edit' => 'Редактирование меню',
            'del' => 'Удаление меню',
            'activeToggle' => 'Изменение активности меню',
            'addItem' => 'Добавление пунктов меню',
            'editItem' => 'Редактирование пунктов меню',
            'delItem' => 'Удаление пунктов меню',
            'activeToggleItem' => 'Изменение активности пунктов меню',
            'orderItems' => 'Сортирвка пунктов меню'
        );

        return $actions;
    }
}
