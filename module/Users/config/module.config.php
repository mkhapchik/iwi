<?php
return array(
	'controllers' => array(
        'invokables' => array(
			'Users\Controller\UserProfile'=>'Users\Controller\UserProfileController',
			'Users\Controller\UsersManager'=>'Users\Controller\UsersManagerController',
			'Users\Controller\RolesManager'=>'Users\Controller\RolesManagerController'
        ),
    ),
	
	'router' => array(
        'routes' => array(
			'users-manager' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/users-manager[/][:action][/:id]',
					'constraints' => array(),
					'defaults' => array(
						'__NAMESPACE__' => 'Users\Controller',
						'controller'    => 'UsersManager',
						'action' => 'view'
					),
                ),
			),
			'roles-manager' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/roles-manager[/][:action][/:id]',
					'constraints' => array(),
					'defaults' => array(
						'__NAMESPACE__' => 'Users\Controller',
						'controller'    => 'RolesManager',
						'action' => 'view'
					),
                ),
			),
			'profile' => array(
				'type'    => 'Segment',
                'options' => array(
                    'route'    => '/users/profile[/][:action]',
					'constraints' => array(),
					'defaults' => array(
						'__NAMESPACE__' => 'Users\Controller',
						'controller'    => 'UserProfile',
						'action' => 'view'
					),
                ),
			),
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'users' => __DIR__ . '/../view',
		)
	),
	
);