<?php
return array(
	
	
	'tasks'=>array(
		'COUNT_TASKS'=>100, #Количество блоков задач (по количеству радиус серверов)
		'SSH_TIMEOUT'=>1 #Таймаут соединения по ssh в сек
	),
	
	'controllers' => array(
        'invokables' => array(
        	//'Cron\Controller\NotifTasks' => 'Cron\Controller\NotifTasksController'
        ),
    ),

	'router' => array(
        'routes' => array(
		),
    ),
	
	'console' => array(
        'router' => array(
            'routes' => array(
				'tasks'=>array(
					'options' => array(
						'route'    => 'tasks',
						'defaults' => array(
							'__NAMESPACE__' => 'Cron\Controller',
							'controller'    => 'Tasks',
							'action'        => 'tasks',
						),
					),
				),
				'notif_tasks'=>array(
					'options' => array(
						'route'    => 'notif_tasks',
						'defaults' => array(
							'__NAMESPACE__' => 'Cron\Controller',
							'controller'    => 'NotifTasks',
							'action'        => 'send',
						),
					),
				)
            ),
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'tasks' => __DIR__ . '/../view',
		)
	),
);