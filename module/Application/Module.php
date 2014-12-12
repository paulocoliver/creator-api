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
use Zend\Http\Request as HttpRequest;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $config = $e->getApplication()->getConfig();
        if ($config['phpSettings']) {
        	foreach($config['phpSettings'] as $key => $value)
        		ini_set($key, $value);
        }
        
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $serviceManager 	 = $e->getApplication()->getServiceManager();
        $dbAdapter 			 = $serviceManager->get('Zend\Db\Adapter\Adapter');
        GlobalAdapterFeature::setStaticAdapter($dbAdapter);
        
        
        $eventManager->attach('render', array($this, 'setLayout'));
        
        // attach the JSON view strategy
        $app      = $e->getTarget();
        $locator  = $app->getServiceManager();
        $view     = $locator->get('ZendViewView');
        $strategy = $locator->get('ViewJsonStrategy');
        $view->getEventManager()->attach($strategy, 100);
        
        // attach a listener to check for errors
        $events = $e->getTarget()->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderError'));
        
        $events->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'));
        
        /*$eventManager->attach(
        		MvcEvent::EVENT_DISPATCH_ERROR,
        		function(MvcEvent $e) {
        			$e->stopPropagation();
        			return;
        			
        			$exception = $e->getParam('exception');
        			if (! $exception instanceof \Application\Exception\MyUserNotFoundException) {
        				return;
        			}
        
        			$model = new ViewModel(array(
        					'message' => $exception->getMessage(),
        					'reason' => 'error-user-not-found',
        					'exception' => $exception,
        			));
        			$model->setTemplate('error/application_error');
        			$e->getViewModel()->addChild($model);
        
        			$response = $e->getResponse();
        			$response->setStatusCode(404);
        
        			$e->stopPropagation();
        
        			return $model;
        	},
        	100
        );*/
    }
    
    public function init(ModuleManager $moduleManager) {
    	$sharedEvents = $moduleManager->getEventManager()->getSharedManager();
    	$sharedEvents->attach("Admin", MvcEvent::EVENT_DISPATCH, function(MvcEvent $e) {
    
    		$serviceManager = $e->getApplication()->getServiceManager();
    		$auth = $serviceManager->get('Auth\Admin');
    		
    		//$routeName = $e->getRouteMatch()->getMatchedRouteName();
    		if (!$auth->hasIdentity()/* && $routeName != "login"*/)
    			return $e->getTarget()->redirect()->toRoute('login');
    
    		
    		$headTitleHelper = $serviceManager->get('viewHelperManager')->get('headTitle');
    		$headTitleHelper('Admin');
    
    	}, 99);
    }
    
    public function setLayout(MvcEvent $e)
    {
    	$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
    	$layout  = $viewHelperManager->get('Layout');
    	
    	if (!empty($_SERVER['HTTP_X_PJAX'])) {
    		$layout->setTemplate('layout/ajax');
    	} else {
    		
	    	# Head Title
	    	$headTitleHelper = $viewHelperManager->get('headTitle');
	    	$headTitleHelper->setSeparator(' - ')
	        	->setAutoEscape(false)
	        	->setTranslatorEnabled(true)
	        	->setDefaultAttachOrder('PREPEND');
	        $headTitleHelper('Constrututor API');
	        	 
	    	$matches = $e->getRouteMatch();
	        if (!$e->isError() && !empty($matches)) {
	    
	        	$controller = $matches->getParam('controller');
	        	
	        	$moduleNamespace = substr($controller, 0, strpos($controller, '\\'));
	        	
	        	if ($moduleNamespace == 'Admin') {
	        	   $layout->setTemplate('layout/layout-admin');
	        	   //$headTitleHelper('title_admin');
	        	}
	    	}
    	}
    
	}
	
	public function onRenderError(\Zend\Mvc\MvcEvent $e)
	{
		$currentModel = $e->getResult();
		if ($currentModel instanceof \Zend\View\Variables && $currentModel->getVariables()->offsetExists('message'))
			$currentModel->getVariables()->offsetUnset('message');
		
		// must be an error
		if (!$e->isError()) {
			return;
		}

		
		// Check the accept headers for application/json
		$request = $e->getRequest();
		if (!$request instanceof HttpRequest) {
			return;
		}
		
	
		$headers = $request->getHeaders();
		if (!$headers->has('Accept')) {
			return;
		}
	
		$accept = $headers->get('Accept');
		$match  = $accept->match('application/json');
		if (!$match || $match->getTypeString() == '*/*') {
			// not application/json
			return;
		}
		
		// make debugging easier if we're using xdebug!
		ini_set('html_errors', 0);
	
		// if we have a JsonModel in the result, then do nothing
		$currentModel = $e->getResult();
		if ($currentModel instanceof JsonModel) {
			return;
		}
	
		// create a new JsonModel - use application/api-problem+json fields.
		$response = $e->getResponse();
		$model = new JsonModel(array(
			"httpStatus" => $response->getStatusCode(),
			"title" => $response->getReasonPhrase(),
		));
	
		// Find out what the error is
		$exception  = $currentModel->getVariable('exception');
	
		if ($currentModel instanceof ModelInterface && $currentModel->reason) {
			switch ($currentModel->reason) {
				case 'error-controller-cannot-dispatch':
					$model->detail = 'The requested controller was unable to dispatch the request.';
					break;
				case 'error-controller-not-found':
					$model->detail = 'The requested controller could not be mapped to an existing controller class.';
					break;
				case 'error-controller-invalid':
					$model->detail = 'The requested controller was not dispatchable.';
					break;
				case 'error-router-no-match':
					$model->detail = 'The requested URL could not be matched by routing.';
					break;
				default:
					$model->detail = $currentModel->message;
					break;
			}
		}
	
		if ($exception) {
			if ($exception->getCode()) {
				$e->getResponse()->setStatusCode($exception->getCode());
			}
			$model->detail = $exception->getMessage();
	
			// find the previous exceptions
			$messages = array();
			while ($exception = $exception->getPrevious()) {
				$messages[] = "* " . $exception->getMessage();
			};
			if (count($messages)) {
				$exceptionString = implode("n", $messages);
				$model->messages = $exceptionString;
			}
		}
	
		// set our new view model
		$model->setTerminal(true);
		$e->setResult($model);
		$e->setViewModel($model);
	}
    
	public function onFinish($e)
    {
        /*$response = $e->getResponse();
        $headers = $response->getHeaders();
        $contentType = $headers->get('Content-Type');
        if (strpos($contentType->getFieldValue(), 'application/json') !== false
            && strpos($response->getContent(), 'httpStatus')) {
            // This is (almost certainly!) an api-problem
            $headers->addHeaderLine('Content-Type', 'application/api-problem+json');
        }*/
    }
	
    public function getServiceConfig() {
    	return array(
    		'factories' => array(
    			'Auth\Admin' => function($service) {
    				$dbAdapter = $service->get('Zend\Db\Adapter\Adapter');
    				$auth = new AuthenticationService();
    				$auth->setStorage(new SessionStorage("Auth_Admin"));
    				$auth->setAdapter(new AuthDbDbTable($dbAdapter, 'usuario', 'email','senha'));
    				return $auth;
    			},
    			'Service\RecursoEntity' => function($sm) {
    				//$dbAdapter = $sm->get('DbAdapter');
    				return new Service\RecursoEntity();
    			},
    			'DbExternoApi' => function($sm) {
    				return new Service\DbExternoApi();
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
                    'Api'   =>  __DIR__ . '/src/Api',
                    'Admin'   =>  __DIR__ . '/src/Admin',
                ),
            ),
        );
    }
}
