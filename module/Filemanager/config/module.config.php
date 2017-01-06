<?php
return array(
	'controllers' => array(
        'invokables' => array(
			'Filemanager\Controller\Filemanager'=>'Filemanager\Controller\FilemanagerController'
        ),
    ),
	
	'router' => array(
        'routes' => array(
            'filemanager' => array(
                'type'    => 'Segment',
                'options' => array(
					'route'    => '/filemanager[/][:action]',
					'constraints' => array(
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Filemanager\Controller',
                        'controller'    => 'Filemanager',
                        'action'        => 'view',
					),
				),
			),
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'filemanager' => __DIR__ . '/../view',
		)
	),
);