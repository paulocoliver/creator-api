<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Http\PhpEnvironment\Request AS HttpRequest;
use Zend\Mvc\MvcEvent;
use Application\Model;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\JsonModel;
//use Zend\Db\Metadata\Object\ConstraintObject as ConstraintObject;

# http://framework.zend.com/manual/2.3/en/modules/zend.mvc.quick-start.html#create-a-controller
class IndexController extends AbstractRestfulController
{
    /**
     * @var HttpRequest
     */
    protected $request;
    
    /**
     * @var \Application\Model\Api
     */
    private $api;
    
    /**
     * @var \Application\Model\Recurso
     */
    private $resource;

    /**
     * @var \Application\Model\Usuario
     */
    private $usuario;
    
    private $adapter_externo;
    private $viewModel;
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    
    public function __construct() {
    }
    
    /**
     * @var JsonModel
     */
    protected $acceptCriteria = array(
        'Zend\View\Model\ViewModel' => array(
        	'text/html'
        ),
		'Zend\View\Model\JsonModel' => array(
			'application/json',
		),
		'Zend\View\Model\FeedModel' => array(
			'application/rss+xml',
		),
    );
    
    public function onDispatch(MvcEvent $e)
    {
    	try {
    		$response = $e->getResponse();
    		
    		### Api
    		$param_api = $this->params('api');
    		 
    		$this->api = $this->getRepository('Api')->findOneBy(array('url' => $param_api, 'status' => 'ATIVO'));
    		if (empty($this->api)) {
    			$response->setStatusCode(404);
    			$response->setContent('Api not found');
    			return $response;
    		}
    		
    		### Auth
    		if (empty($_SERVER['HTTP_X_API_TOKEN']) || empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']))
	    		throw new \Exception('error_acesso');
    		
    		$this->usuario = $this->getRepository('Usuario')->findOneBy(array(
    			'token' => $_SERVER['HTTP_X_API_TOKEN'],
    			'email' => $_SERVER['PHP_AUTH_USER'],
    			'senha' => $_SERVER['PHP_AUTH_PW'],
    		));
    		if (empty($this->usuario)) {
    			$response->setStatusCode(401);
    			//$response->setContent('Api not found');
    			return $response;
    		}
    		
    		$usuarioApi = $this->getRepository('UsuarioApi')
    			->findOneBy(array('usuario' => $this->usuario, 'api' => $this->api));
    		if (empty($usuarioApi)) {
    			$response->setStatusCode(401);
    			//$response->setContent('Api not found');
    			return $response;
    		}
	         
	        ### Resource
	        $param_resource = $this->params('resource');
	        if (!empty($param_resource)) {
	        	
	        	$this->resource = $this->getRepository('Recurso')->findOneBy(array(
	        		'api' => $this->api, 'resource' => $param_resource, 'disponivel' => 'SIM'
	        	));
	            if (empty($this->resource)) {
	                $response->setStatusCode(404);
	                $response->setContent('Resource not found');
	                return $response;
	            }
	            
	            $method  = strtolower($e->getRequest()->getMethod());
	            $methods = array('get', 'post', 'put', 'delete');
	            $check_method = '_'.$method;
	            if (!in_array($method, $methods) || $this->resource->$check_method != 'ATIVO') {
                    $response = $e->getResponse();
                    $response->setStatusCode(405);
                    $response->setContent('Method Not Allowed');
                    return $response;
	            }
	            
	            $db_externo = new \Application\Service\DbExternoApi();
	            $db_externo->setConexao($this->api->getConexao());
	            $this->adapter_externo = $db_externo->getAdapter();
	        }
        
	        $this->viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
	        $this->viewModel->setTerminal(true);
	        parent::onDispatch($e);
	        
        } catch (\Exception $exp) {
        	$response = $e->getResponse();
        	$response->setStatusCode(500);
        	$response->setContent('Error');
        	return $response;
        }
         
    }
    
    public function getList()
    {
        try {
        	
        	$page = (int)$this->params()->fromQuery('page', 1);
        	$per_page = (int)$this->params()->fromQuery('per_page', 25);
        	
        	$select = $this->getTable()->getSql()->select();
        	$paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->adapter_externo);
        	
        	$paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        	$paginator->setCurrentPageNumber($page);
        	$paginator->setItemCountPerPage($per_page);
        	
        	$results = $paginator->getCurrentItems();
        	if ($results instanceof \Zend\Db\ResultSet\ResultSet) {
	            $results = $results->toArray();
        	} else {
        		$results = array();
        	}
        	
        	$pages = $paginator->getPages('sliding');
        	
        	$return = array(
        		'first' 	=> $pages->first,
        		'previous'  => $pages->previous,
        		'current' 	=> $pages->current,
        		'next' 		=> $pages->next,
        		'last' 		=> $pages->last,
        		'per_page' 	=> $pages->itemCountPerPage,
        		'total_itens' => $pages->totalItemCount,
        		'_links' 	=> array(
        			'first' 	=> $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($pages->first, $pages->itemCountPerPage),
        			'previous' 	=> $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($pages->previous, $pages->itemCountPerPage),
        			'current' 	=> $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($pages->current, $pages->itemCountPerPage),
        			'next' 		=> $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($pages->next, $pages->itemCountPerPage),
        			'last' 		=> $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($pages->last, $pages->itemCountPerPage),
        		)
        	);
        	
        	$return['data'] = $results;
        	
        	
            return $this->viewModel->setVariables($return);
        } catch (\Exception $e) {
        }
    }
    
    private function getQueryPage($page, $per_page) {
    	return '?page='.$page.'&per_page='.$per_page;
    }
    
    public function get($id)
    {
        //echo 'get:'.$id;
        //exit;
        
        $this->prepareId($id);
        
        
        
        
        $results = $this->getTable()->select()->toArray();
        return $this->viewModel->setVariables(array('data' => $results));
        
        
    	$this->response->setStatusCode(405);
    
    	return array(
    			'content' => 'Method Not Allowed'
    	);
    }
    
    public function create()
    {
    	echo 'create';
    	exit;
    }

    public function update()
    {
    	echo 'update';
    	exit;
    }
    

    public function delete()
    {
    	echo 'delete';
    	exit;
    }
    
    public function indexAction()
    {
        echo 'index';
        exit;
    }
    
    public function getTable() {
        return new \Zend\Db\TableGateway\TableGateway($this->resource->getDb_table(), $this->adapter_externo);
    }
    
    private function prepareId($id) {
        if (is_integer($id) && $id > 0) {
        	return $id;
        } else {
            echo base64_decode($id);
            
        	$param = base64_decode(json_decode($id));
        	echo '<pre>';
        	print_r($param);
        	echo '</pre>';
        	exit;
        }
    } 
    
    public function getService($name)
    {
    	return $this->getServiceLocator()->get($name);
    }
    
    public function setEntityManager(EntityManager $em)
    {
    	$this->em = $em;
    }
    
    public function getEntityManager()
    {
    	if (null === $this->em) {
    		$this->em = $this->getService('Doctrine\ORM\EntityManager');
    	}
    	return $this->em;
    }
    
    public function getRepository($repository=null)
    {
    	if (empty($repository))
    		$repository =  $this->repository;
    
    	$repositorys = \Application\Service\Entity::getRepositorys();
    		
    	if (key_exists($repository, $repositorys))
    		$repository = $repositorys[$repository];
    
    	return $this->getEntityManager()->getRepository($repository);
    }
    
}
