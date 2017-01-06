<?php
return array(
	'navigation'=>array(
		'admin'=>array(
			/*
			array(
				'label'=>'Администрирование',
				'uri'=>'',
				//'route'=>'pages_admin',
				//'params'=>array('action'=>'list'),
				'pages'=>array(
					array(
						'label'=>'Страницы',
						'route'=>'pages_admin',
						'params'=>array('action'=>'list'),
					)
				)
			),
			
			array(
				'label'=>'Страницы',
				'route'=>'pages-manager',
				'params'=>array('action'=>'view'),
			)*/
		)
	),
	
	'controllers' => array(
        'invokables' => array(
			'Pages\Controller\Page'=>'Pages\Controller\PageController',
			'Pages\Controller\PagesManager'=>'Pages\Controller\PagesManagerController',
        ),
    ),
	
	'router' => array(
        'routes' => array(
			'pages' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/pages[/:action][/:id][/]',
                    'constraints' => array(
						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Pages\Controller',
						'controller'    => 'Page',
						'action' => 'view'
					),
                ),
			
            ),
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
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'pages' => __DIR__ . '/../view',
		)
	),
	/*
	'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'pages' => __DIR__ . '/../view',
        ),
    ),
	*/
	/*
	'service_manager' => array(

        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
	*/
	'translator' => array(
        //'locale' => 'en_US',
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