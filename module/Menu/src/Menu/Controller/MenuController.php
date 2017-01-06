<?php
namespace Menu\Controller;

use Menu\Entity\MenuItem;
use Pages\Controller\PageController;
use Zend\View\Model\ViewModel;
use Exception;
use Menu\Entity\Menu;

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

        $serviceTree = new \Application\Service\Tree();
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

                $message = '';
                $is_success = 1;

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
        $menuId = (int)$this->params()->fromRoute('id', 0);
        if (!$menuId) return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));

        $is_success = 0;
        $message = '';

        $sm = $this->getServiceLocator();

        $menuItemsTable = $sm->get('Menu\Model\MenuItemsTable');
        $pageModel = $sm->get('Pages\Model\PageModel');

        $item = new MenuItem();

        $form = $sm->get('Menu\Form\MenuItemForm');
        $form->bind($item);

        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $post = $request->getPost()->toArray();

                $form->setData($post);

                if (!$form->isValid()) {
                    throw new Exception('');
                }

                if ($item->type === 'page') {
                    $item->uri = null;
                    $pageId = (int)$request->getPost('page');
                    if($pageId) {
                        $page = $pageModel->getPageById($pageId);
                        if($page) $item->route_id = $page->route_id;
                        else throw new Exception("Page #$pageId not found.");

                    }
                }else{
                    $item->route_id = null;
                }

                $itemId = $menuItemsTable->addMenuItem($item);
                return $this->redirect()->toRoute('menu', array('action' => 'editItem', 'id'=>$menuId, 'itemId'=>$itemId));
            }
            catch (Exception $ex) {
                $is_success = 0;
                $message = $ex->getMessage();
            }
        }

        $title = "Добавление пункта меню";
        $this->layout()->setVariable('title', $title);
        $params = array(
            'title' => $title,
            'form' => $form,
            'is_success' => $is_success,
            'message' => $message
        );

        $view = new ViewModel($params);
        $view->setTemplate('menu/menu/formItem');

        return $view;
    }

    public function editItemAction()
    {
        $menuId = (int)$this->params()->fromRoute('id', 0);
        $itemId = (int)$this->params()->fromRoute('itemId', 0);
        if (!$menuId || !$itemId) return $this->redirect()->toRoute('menu-manager', array('action' => 'view'));

        $is_success = 0;
        $message = '';

        $sm = $this->getServiceLocator();

        $menuItemsTable = $sm->get('Menu\Model\MenuItemsTable');
        $pageModel = $sm->get('Pages\Model\PageModel');

        $item = $menuItemsTable->getMenuItem($itemId);

        $form = $sm->get('Menu\Form\MenuItemForm');
        $form->bind($item);

        if ($item->type === 'page') {
            $page = $pageModel->getPageByRouteId($item->route_id);
            if ($page) {
                $form->setData(array('page' => $page->id));
            }
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            try {
                $post = array_merge_recursive(
                    $request->getPost()->toArray()
                    //$request->getFiles()->toArray()
                );

                $form->setData($post);

                if (!$form->isValid()) {
                    throw new Exception('');
                }

                //var_dump($item->icon_img);

                if ($item->type === 'page') {
                    $item->uri = null;
                    $pageId = (int)$request->getPost('page');
                    if($pageId) {
                        $page = $pageModel->getPageById($pageId);
                        if($page) $item->route_id = $page->route_id;
                        else throw new Exception("Page #$pageId not found.");

                    }
                }else{
                    $item->route_id = null;
                }

                $menuItemsTable->updateMenuItem($item);
                return $this->redirect()->toRoute('menu', array('action' => 'editItem', 'id'=>$menuId, 'itemId'=>$itemId));
            }
            catch (Exception $ex) {
                $is_success = 0;
                $message = $ex->getMessage();
            }
        }


        $title = "Редактирование пункта меню";

        $this->layout()->setVariable('title', $title);

        $params = array(
            'title' => $title,
            'form' => $form,
            'is_success' => $is_success,
            'message' => $message
        );

        $view = new ViewModel($params);
        $view->setTemplate('menu/menu/formItem');

        return $view;
    }

    private function processItem(&$item, &$form){

    }

    public function delItemAction()
    {

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
