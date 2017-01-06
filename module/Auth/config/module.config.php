<?php
return array(
	'auth'=>array(
		/* максимальное количество неудачных попыток аутентификации, после которых пользователь блокируется, false - бесконечно
		* @default 3
		*/
		'max_counter_failures'=>3,
		
		/* маршрут для редиректа при успешной аутентификации на соответствующей странице */
		'success_redirect'=>array(
			'route_name' => 'home',
			'route_params'=>array()
		),
		
		/* маршрут для редиректа при выходе из аккаунта */
		'logout_redirect'=>array(
			'route_name' => 'home',
			'route_params'=>array()
		),
		
		/* период неактивности пользователя, сек 
		* @default 1800
		*/
		'inactivity_time_sec' => 1800,
		
		/* Время жизни неактивной сессии, сек 
		* @default 14400
		*/
		'lifetime_inactive_session_sec'=>14400,
		
		/* периодичность ajax запроса для проверки таймаута 
		* @default 10
		*/
		'frequency_of_check_timeout_sec'=>2,
		
		/* максимальное количество перезагрузки капчи вручную 
		* @default 3
		*/
		'max_count_refresh_captcha'=>3,
		
		/* максимальное количество неудачных попыток входа, после которых показывать капчу
		* @default 5
		*/
		'max_count_fail_to_show_captcha'=>5, 
		
		/* Использовать разрешенные списки ip адресов для доступа 
		* @default 1
		*/
		'use_allow_list_ip'=>0,
		
		/* Разрешить мультисессии - возможность одновременно зайти под одним и тем же логином 
		* @default 0
		*/
		'multi_session_for_user'=>0,
		
		/* Длительность блокировки супер пользователя при неверной аутентификации > max_counter_failures, сек
		* @default 3600
		*/
		'period_temporary_block_super_user'=>3600, 

		/*Количество разрешенных анонимных сессий для одного ip за указанный период, 0 - бесконечное число
		* @default 10
		*/
		'max_count_sessions_for_ip'=>10,
		
		/*Период проверки количества анонимных сессий для одного ip, минуты
		* @default 5
		*/
		'period_max_count_sessions_for_ip'=>5,
		
		/*Флаг необходимости перегенерации идентификатора PHP сессии, для обеспечения безопасности кражи куки
		* @default 0
		*/
		'session_regenerate'=>0,
		
		/*Вести историю сессий
		* @default 1
		*/
		'record_history_sessions'=>1,
		
		/*Показывать сообщения при ошибке аутентификации содержащие информацию о пользователе или ошибки системы
		* @default 0
		*/
		'show_private_access_error'=>0, 
		
		/*Хранилище авторизационных данных 1 - сессия || 2 - куки
		* @default 1
		*/
		'storage'=>1,
		
		/*Время жизни токена для изменения забытого пароля, сек 
		* @default 3600
		*/
		'token_pwd_life_time_sec'=>3600,
		
	),	
	'controllers' => array(
        'invokables' => array(
            'Auth\Controller\Authentication' => 'Auth\Controller\AuthenticationController',	
			'Auth\Controller\Authorization' => 'Auth\Controller\AuthorizationController',	
			'Auth\Controller\ForgotPassword' => 'Auth\Controller\ForgotPasswordController',
        ),
    ),
	'view_helpers' => array(
        'invokables' => array(
          
        )
    ),
	'router' => array(
        'routes' => array(
			'auth' => array(
				'type'    => 'Literal',
                'options' => array(
                    'route'    => '/auth',
					'defaults' => array(),
                ),
                'child_routes' => array(
					'login' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/login',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'Authentication',
								'action'        => 'login',
                            ),
                        ),						
                    ),
					'logout'=> array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/logout',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'Authentication',
								'action'        => 'logout',
                            ),
                        ),						
                    ),
					'timeout'=>array(
						'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/timeout',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'Authorization',
								'action'        => 'checkTimeout',
                            ),
                        ),	
					),
					'refresh_captcha'=>array(
						'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/refresh_captcha',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'Authentication',
								'action'        => 'refreshcaptcha',
                            ),
                        ),	
					),
					
					
                ),
			),
			'password'=>array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/password',
					'defaults' => array(),
				),
				'child_routes' => array(
					'change' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/change[/:token]',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'ForgotPassword',
								'action'        => 'change',
                            ),
                        ),						
                    ),
					'request' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/request',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'ForgotPassword',
								'action'        => 'request',
                            ),
                        ),						
                    ),
					'success' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/success',
                            'defaults' => array(
								'__NAMESPACE__' => 'Auth\Controller',
								'controller'    => 'ForgotPassword',
								'action'        => 'success',
                            ),
                        ),						
                    ),
					
				),
			)
        ),
    ),

	'view_manager' => array(
		'template_path_stack' => array(
			'auth' => __DIR__ . '/../view',
		),
		/*
		'template_map' => array(
			'auth/forgot-password/email' => __DIR__ . '/../view/auth/forgot-password/email.phtml',
		),
		*/
	),
);