<?php
return array(
	'controllers' => array(
        'factories' => array(
			'Settings\Controller\Settings'=>'Settings\Factory\Controller\SettingsControllerFactory',
        ),
    ),
	
	'router' => array(
        'routes' => array(
            'settings' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/admin/settings[/:action][/:id][/]',
                    'constraints' => array(
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Settings\Controller',
						'controller'    => 'Settings',
						'action' => 'view'
					),
                ),
            ),
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'settings' => __DIR__ . '/../view',
		)
	),
	
);