<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
		'routes' => array(
            ## Site
			'index' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action'     => 'index',
					),
				),
			),
			'login' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/login',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action'     => 'login',
					),
				),
			),
			'register' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/register',
					'defaults' => array(
						'controller' => 'Application\Controller\Index',
						'action'     => 'register',
					),
				),
			),
			'apis' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/apis',
					'defaults' => array(
						'controller' => 'Admin\Controller\Api',
						'action'     => 'index',
					),
				),
			),
			'api' => array(
				'type' => 'Segment',
				'options' => array(
					'route'    => '/api[/:id]',
					'defaults' => array(
						'controller' => 'Admin\Controller\Api',
						'action'     => 'add',
					),
				),
			),
			'api-share' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/share/:id',
					'defaults' => array(
						'controller' => 'Admin\Controller\Api',
						'action'     => 'share',
					),
				),
			),
			'api-connection' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/connection/:id',
					'defaults' => array(
						'controller' => 'Admin\Controller\Api',
						'action'     => 'connection',
					),
				),
			),
			'api-resources' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/resources/:id',
					'defaults' => array(
						'controller' => 'Admin\Controller\Resources',
						'action'     => 'index',
					),
				),
			),
			'api-resources-reload' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/resources/:id/reload',
					'defaults' => array(
						'controller' => 'Admin\Controller\Resources',
						'action'     => 'index',
						'reload'     => true,
					),
				),
			),
			'api-resources-edit' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/resources/:id/edit[/:id_resource]',
					'defaults' => array(
						'controller' => 'Admin\Controller\Resources',
						'action'     => 'edit',
					),
				),
			),
			'api-resources-columns' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/api/resources/:id/columns[/:id_resource]',
					'defaults' => array(
						'controller' => 'Admin\Controller\Resources',
						'action'     => 'columns',
					),
				),
			),
			'connection' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/connections',
					'defaults' => array(
						'controller' => 'Admin\Controller\Connection',
						'action'     => 'index',
					),
				),
			),
			'connection-save' => array(
				'type' => 'Segment',
				'options' => array( 
					'route'    => '/connection/save[/:id]',
					'defaults' => array(
						'controller' => 'Admin\Controller\Connection',
						'action'     => 'save',
					),
				),
			),
			## API
			'host-api' => array(
				'type'    => 'Hostname',
				'options' => array(
					'route'    => ':api.api.criadorapi.com',
					'defaults' => array(
						'controller'    => 'Api\Controller\Index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/',
							'constraints' => array(
								'recurso' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'action'     => 'index',
							),
						),
					),
					'resource' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/:resource[/:id]',
							'constraints' => array(
								'recurso' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(),
						),
					),
				
				),
			),
			## DOC
			'host-doc' => array(
				'type'    => 'Hostname',
				'options' => array(
					'route'    => ':api.doc.localhost',
					'defaults' => array(
						'controller'    => 'Api\Controller\Index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'index' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/',
							'constraints' => array(
								'recurso' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
								'action'     => 'index',
							),
						),
					),
				),
			),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
	        'Doctrine\ORM\EntityManager' => function($sm) {
	        	$config = $sm->get('Configuration');
	        
	        	$doctrineConfig = new \Doctrine\ORM\Configuration();
	        	$cache = new $config['doctrine']['driver']['cache'];
	        	$doctrineConfig->setQueryCacheImpl($cache);
	        	$doctrineConfig->setProxyDir('/tmp');
	        	$doctrineConfig->setProxyNamespace('EntityProxy');
	        	$doctrineConfig->setAutoGenerateProxyClasses(true);
	        
	        	$driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
	        			new \Doctrine\Common\Annotations\AnnotationReader(),
	        			array($config['doctrine']['driver']['paths'])
	        	);
	        	$doctrineConfig->setMetadataDriverImpl($driver);
	        	$doctrineConfig->setMetadataCacheImpl($cache);
	        	
	        	\Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
	        			__DIR__. '/../../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
	        	);
	        	$em = \Doctrine\ORM\EntityManager::create(
	        			$config['doctrine']['connection'],
	        			$doctrineConfig
	        	);
	        	return $em;
	        
	        },
        ),
    ),
    'translator' => array(
        'locale' => 'pt_BR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' 	=> 'Application\Controller\IndexController',
            'Api\Controller\Index'  => 'Api\Controller\IndexController',
            /* Admin */
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Api' => 'Admin\Controller\ApiController',
            'Admin\Controller\Connection' => 'Admin\Controller\ConnectionController',
            'Admin\Controller\Resources' => 'Admin\Controller\ResourcesController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'strategies' => array(
        	'ViewJsonStrategy',
        	'ViewFeedStrategy'
        ),
    ),
    'view_helpers' => array(
		'invokables'=> array(
			'api_users' => 'Admin\View\Helper\ApiUsers'
		)
    ),
    'doctrine' => array(
    	'driver' => array(
    		'cache' => 'Doctrine\Common\Cache\ArrayCache',
    		'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Model')
    	),
    )
);
