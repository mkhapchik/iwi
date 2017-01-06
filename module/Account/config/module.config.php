<?php
return array(
	 'router' => array(
        'routes' => array(
            'account' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/accounts',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Account\Controller',
                        'controller'    => 'Index',
                        'action'        => 'view',
                    ),
                ),
                 'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action][/:id]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     => '[0-9]+',
                            ),
                            'defaults' => array(
								'__NAMESPACE__' => 'Account\Controller',
								'controller'    => 'Index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
  
    'controllers' => array(
        'invokables' => array(
            'Account\Controller\Index' => 'Account\Controller\IndexController'
        ),
    ),
	
	'view_manager' => array(
		'template_path_stack' => array(
			'account' => __DIR__ . '/../view',
		)
	),
	'translator' => array(
        'locale' => 'en_US',
		//'locale' => 'ru_RU',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
	
);