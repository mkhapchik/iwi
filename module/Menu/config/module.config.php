<?php
return array(
	
	'navigation'=>array(
		
	),
		
	'controllers' => array(
        'invokables' => array(
			'Menu\Controller\MenuManager'=>'Menu\Controller\MenuManagerController',
            'Menu\Controller\Menu'=>'Menu\Controller\MenuController'
        ),
    ),
	
	'router' => array(
        'routes' => array(
            'menu' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/menu[/:action][/:id][/:itemId][/]',
                    'constraints' => array(
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Menu\Controller',
						'controller'    => 'Menu',
						'action' => 'edit'
					),
                ),
            ),
            'menu-manager' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/menu-manager[/][:action]',
					'constraints' => array(
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
					'defaults' => array(
						'__NAMESPACE__' => 'Menu\Controller',
						'controller'    => 'MenuManager',
						'action' => 'view'
					),
                ),
			),
            /*
           
			'pages-manager'	 => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/pages-manager[/:action][/]',
                    'constraints' => array(
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Pages\Controller',
						'controller'    => 'PagesManager',
						'action' => 'view'
					),
                ),
			),
            */
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'menu' => __DIR__ . '/../view',
		)
	),
	
);