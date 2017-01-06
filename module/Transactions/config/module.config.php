<?php
return array(
	'controllers' => array(
        'invokables' => array(
            'Transactions\Controller\TransactionIncome' => 'Transactions\Controller\TransactionIncomeController',	
			'Transactions\Controller\TransactionExpense' => 'Transactions\Controller\TransactionExpenseController',	
        ),
    ),
	
	'router' => array(
        'routes' => array(
            'transactions' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/transactions',
					'defaults' => array(),
                ),
				'child_routes' => array(
					'income' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/income[/:action][/:param]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								'param'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
								'__NAMESPACE__' => 'Transactions\Controller',
								'controller'    => 'TransactionIncome',
								'action'        => 'add',
                            ),
                        ),
                    ),
					'expense'=>array(
						'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/expense[/][:action][/:param]',
							//'route'    => '/expense[/]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								'param'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
								'__NAMESPACE__' => 'Transactions\Controller',
								'controller'    => 'TransactionExpense',
								'action'        => 'add',
                            ),
                        ),
						/*
						'may_terminate' => true,
						'child_routes' => array(
							'view'=>array(
								'type'    => 'Segment',
								'options' => array(
									'route'    => 'view[/]',
									'defaults' => array(
										'__NAMESPACE__' => 'Transactions\Controller',
										'controller'    => 'TransactionExpense',
										'action'        => 'view',
									),
								),
							),
						)
						*/
					)
                ),
				
            ),
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'transactions' => __DIR__ . '/../view',
		)
	),
);