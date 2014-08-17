<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\Adapter\DbTable as AuthDbDbTable;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $serviceManager 	 = $e->getApplication()->getServiceManager();
        $dbAdapter 			 = $serviceManager->get('Zend\Db\Adapter\Adapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);
        
        $config = $e->getApplication()->getConfig();
        if ($config['phpSettings']) {
        	foreach($config['phpSettings'] as $key => $value)
        		ini_set($key, $value);
        }
    }
    
    public function init(ModuleManager $moduleManager) {
    	$sharedEvents = $moduleManager->getEventManager()->getSharedManager();
    	$sharedEvents->attach("Application", 'dispatch', function(MvcEvent $e) {
    
    		$serviceManager = $e->getApplication()->getServiceManager();
    		$auth = $serviceManager->get('Auth\Application');
    		
    		$routeName = $e->getRouteMatch()->getMatchedRouteName();
    		if (!$auth->hasIdentity() && $routeName != "login")
    			return $e->getTarget()->redirect()->toRoute('login');
    
    		$headTitleHelper = $serviceManager->get('viewHelperManager')->get('headTitle');
    		$headTitleHelper('Admin');
    
    	}, 99);
    	
    	$sharedEvents->attach("Api", 'dispatch', function(MvcEvent $e) {
    
    	}, 99);
    }
    
    public function getServiceConfig() {
    	return array(
    		'factories' => array(
    			'Auth\Application' => function($service) {
    				$dbAdapter = $service->get('Zend\Db\Adapter\Adapter');
    				$auth = new AuthenticationService();
    				$auth->setStorage(new SessionStorage("ApplicationSite"));
    				$auth->setAdapter(new AuthDbDbTable($dbAdapter, 'usuario', 'email','senha'));
    				return $auth;
    			},
    		),
    	);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Api'  =>  __DIR__ . '/src/Api',
                ),
            ),
        );
    }
}
