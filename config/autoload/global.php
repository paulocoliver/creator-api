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
		
	'doctrine' => array(
		'connection' => array(
			'driver'   => 'pdo_mysql',
			'host'     => 'localhost',
			'port'     => '3306',
			'user'     => 'root',
			'password' => 'root',
			'dbname'   => 'tcc',
			'charset' => 'UTF8',
			'driverOptions' => array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
			),
		)
	),
		
	'db' => array(
	    
	    'driver'   => 'Pdo',
	    'dsn'      => 'mysql:dbname=tcc;host=localhost',
	    'username' => 'root',
	    'password' => 'root',
	    'driver_options' => array(
	    		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
	    ),
	    
		/*'driver'   => 'Mysqli',
	    'hostname' => 'localhost',
		'database' => 'tcc',
		'username' => 'root',
		'password' => 'root',
		'driver_options' => array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
		),*/
	),
    
	'service_manager' => array(
		'factories' => array(
			'Zend\Db\Adapter\Adapter' => function ($serviceManager) {
				$adapterFactory = new Zend\Db\Adapter\AdapterServiceFactory();
				$adapter = $adapterFactory->createService($serviceManager);
				\Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
				return $adapter;
			}
		),
	),
	'phpSettings'   => array(
		'display_startup_errors'        => true,
		'display_errors'                => true,
		'max_execution_time'            => 60,
		'date.timezone'                 => 'America/New_York',
		'mbstring.internal_encoding'    => 'UTF-8',
	),
);
