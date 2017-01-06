<?php
return array(
	'controllers' => array(
        'invokables' => array(
            //'Transactions\Controller\TransactionIncome' => 'Transactions\Controller\TransactionIncomeController',	
			'Reports\Controller\TableCategoriesExpen' => 'Reports\Controller\TableCategoriesExpenController'
        ),
    ),
	
	'router' => array(
        'routes' => array(
            'reports' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/reports',
					'defaults' => array(),
                ),
				'child_routes' => array(
					'table_expen' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/table_expen[/:action][/:id]',
							'constraints' => array(
								'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     => '[0-9]+',
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Reports\Controller',
								'controller'    => 'TableCategoriesExpen',
								'action'        => 'view',
							),
						),
					),
				),
			),	
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'reports' => __DIR__ . '/../view',
		)
	),
);