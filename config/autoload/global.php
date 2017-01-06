<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'logger'=>array(
		'is_log'=>1,
		'sql'=>1
	),
	'db_default' => array(
		'driver' => 'Pdo',
		'dsn' => 'mysql:dbname=iwi;host=localhost',
		'username' => 'root',
		'password' => '123456',
		'driver_options' => array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
		),
	),
	/*
	'db_test' => array(
		'driver' => 'Pdo',
		'dsn' => 'mysql:dbname=test.capital;host=localhost',
		'username' => 'root',
		'password' => '123456',
		'driver_options' => array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
		),
	),
	*/
	'report'=>array(
		'table'=>array(
			'countPerPage'=>10
		)
	),
	
	'service_manager' => array(
		'factories' => array(
			//'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
			/*
			'AdapterDefault' => function ($sm) {
                $config = $sm->get('Config');
                return new \Zend\Db\Adapter\Adapter($config['db_default']);
            },
            'AdapterTest' => function ($sm) {
                $config = $sm->get('Config');
                return new \Zend\Db\Adapter\Adapter($config['db_test']);
            },
			*/
			'Zend\Db\Adapter\Adapter' => function ($sm) {
				//$subdomen = array_shift((explode(".",$_SERVER['HTTP_HOST'])));
				$config = $sm->get('Config');
				/* demo access
				switch($subdomen)
				{
					case 'capital' : 
						$adapter_conf = $config['db_default'];
						break;
					case 'test' :
						$adapter_conf = $config['db_test'];
						break;
					default:
						die('access error');
				}
				*/
				$adapter_conf = $config['db_default'];
				$profiler = new \Zend\Db\Adapter\Profiler\Profiler();	
				
                return new \Zend\Db\Adapter\Adapter($adapter_conf, null, null, $profiler);
            },
		),
		'initializers' => array(
            function ($instance, $sm) {
                if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
                    $instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                }
            }
        ),
	),
	
	

);
