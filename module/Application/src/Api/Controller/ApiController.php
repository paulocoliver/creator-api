<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Application\Model;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\JsonModel;
use AP_XmlStrategy\View\Model\XmlModel;
use Zend\Http\Header\CacheControl;
//use Zend\Db\Metadata\Object\ConstraintObject as ConstraintObject;

# http://framework.zend.com/manual/2.3/en/modules/zend.mvc.quick-start.html#create-a-controller
class ApiController extends AbstractRestfulController
{
    /**
     * @var  \Zend\Http\PhpEnvironment\Request
     */
    protected $request;

    /**
     * @var \Zend\Http\PhpEnvironment\Response
     */
    protected $response;
    
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
    
    private $tableGateway;
    
    private $cache = array();
    
    /**
     * @var \Zend\View\Model\ModelInterface
     */
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
		'AP_XmlStrategy\View\Model\XmlModel' => array(
            'application/xml',
        ),
    );
    
    
    public function sendSuccess($data, $statusCode=200, $params=array()) {
    	$this->response->setStatusCode($statusCode);
    	$return['status'] = 'success';
    	$return['result'] = $data;
    	
    	return $this->viewModel->setVariables($return);
    }
    
    public function sendError($statusCode, $error, $error_code=null, $params=array()) {
    	$this->response->setStatusCode($statusCode);
    	$return['status'] = 'error';
    	$return['result']['message'] = $error;
    	if (!empty($error_code))
    		$return['result']['code'] = $error_code;
    	
    	return $this->viewModel->setVariables($return);
    }
    
    public function onDispatch(MvcEvent $e)
    {
    	try {
    		$this->viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
    		$this->viewModel->setTerminal(true);
    		$this->response = $e->getResponse();
    		
    		
    		### Api
    		$param_api = $this->params('api');
    		$this->api = $this->getRepository('Api')->findOneBy(array('url' => $param_api, 'status' => 'ATIVO'));
    		if (empty($this->api)) {
    			$return = $this->sendError(404, 'Api not found', 4041);
    			$e->setViewModel($return);
    			return $return;
    		}
    		
    		
    		### Auth
    		try {
	    		if (empty($_SERVER['HTTP_X_API_TOKEN']) || empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']))
		    		throw new \Exception();
	    		
	    		$this->usuario = $this->getRepository('Usuario')->findOneBy(array(
	    			'token' => $_SERVER['HTTP_X_API_TOKEN'],
	    			'email' => $_SERVER['PHP_AUTH_USER'],
	    			'senha' => $_SERVER['PHP_AUTH_PW'],
	    		));
	    		
	    		if (empty($this->usuario))
		    		throw new \Exception();
	    		
	    		$usuarioApi = $this->getRepository('UsuarioApi')->findOneBy(array('usuario' => $this->usuario, 'api' => $this->api));
	    		if (empty($usuarioApi))
		    		throw new \Exception();
    		} catch (\Exception $exp) {
    			$return = $this->sendError(401, 'Unauthorized', 4011);
    			$e->setViewModel($return);
    			return $return;
    		}
    		
    		
	         
	        ### Resource
	        $param_resource = $this->params('resource');
	        if (!empty($param_resource)) {
	        	
	        	$this->resource = $this->getRepository('Recurso')->findOneBy(array(
	        		'api' => $this->api, 'resource' => $param_resource, 'disponivel' => 'SIM'
	        	));
	            if (empty($this->resource)) {
	            	$return = $this->sendError(404, 'Resource not found', 4042);
	            	$e->setResult($return);
	            	return $return;
	            }
	            
	            $method  = strtolower($e->getRequest()->getMethod());
	            $methods = array('get', 'post', 'put', 'delete');
	            $check_method = '_'.$method;
	            if (!in_array($method, $methods) || $this->resource->$check_method != 'ATIVO') {
	            	$return = $this->sendError(405, 'Method Not Allowed', 4051);
	            	$e->setViewModel($return);
	            	return $return;
	            }
	            
	            $db_externo = new \Application\Service\DbExternoApi();
	            $db_externo->setConexao($this->api->getConexao());
	            $this->adapter_externo = $db_externo->getAdapter();
	            
	            $cache = (int)$this->resource->getCache();
	            if (empty($cache))
	            	$cache = (int)$this->api->getCache();
	            
	            try {
		            $conn = $this->getEntityManager()->getConnection();
		            $conn->insert('log_acesso', array(
		            	'id_usuario' => $this->usuario->getId(),
		            	'id_resource' => $this->resource->getId(),
		            	'metodo' => $method,
		            	'data' => date('Y-m-d H:i:s')
		            ));
	            } catch (\Exception $exp) {
	            }
	            
	        }
	        
            if (!empty($cache))
            	$this->cache = array('max-age' => $cache);

	        return parent::onDispatch($e);
	        
        } catch (\Exception $exp) {
        	$return = $this->sendError(500, $exp->getMessage(), 5001);
        	$e->setViewModel($return);
        	return $return;
        }
         
    }
    
    private function setHeaderCache() {
    	if (!empty($this->cache['max-age']))
    		$this->response->getHeaders()->addHeaderLine('Cache-Control', 'max-age='.$this->cache['max-age']);
    }
    
    /* Actions */
    public function getList()
    {
        try {
        	
        	$page = (int)$this->params()->fromQuery('page', 1);
        	$per_page = (int)$this->params()->fromQuery('per_page', 25);
        	
        	
        	$select = $this->getTable()->getSql()->select();
        	$select->columns($this->getColsDisponiveis());
        	
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
        		'first' 	=> !empty($pages->first) ? $pages->first : null,
        		'previous'  => !empty($pages->previous) ? $pages->previous : null,
        		'current' 	=> !empty($pages->current) ? $pages->current : null,
        		'next' 		=> !empty($pages->next) ? $pages->next : null,
        		'last' 		=> !empty($pages->last) ? $pages->last : null,
        	);
        	
        	
        	foreach ($return as $k => $v) {
        		if (empty($v)) {
        			unset($return[$k]);
        			continue;
        		}
        		$return['_links'][$k] = $this->url()->fromRoute(null, array(), array(), true).$this->getQueryPage($v, $pages->itemCountPerPage);
        	}
        	
        	$return['per_page'] = $pages->itemCountPerPage;
        	$return['total_itens'] = $pages->totalItemCount;
        	
        	$return['data'] = $results;
        	
        	
        	
        	$this->setHeaderCache();
        	
        	return $this->sendSuccess($return);
        	
        } catch (\Exception $e) {
        	$this->sendError(500, $e->getMessage(), 5001);
        }
    }
    
    
    
    public function get($id)
    {
        try {
        	$where = $this->getWhereResource($id);
        	$res = $this->findRecurso($where);
        	if (empty($res))
        		return $this->sendError(404, 'Document Not Found', 4043);
        	
        	$this->setHeaderCache();
	        return $this->sendSuccess($res);
        } catch (\Exception $e) {
        	$this->sendError(500, $e->getMessage(), 5001);
        }
    }
    
    
    
    public function create($data)
    {
    	try {
    		$cols = $this->getColsDisponiveis('_insert');
    		$result = $this->validaRecurso($cols, $data);
    		
    		if (!empty($result['errors']))
    			return $this->sendError(400, $result['errors'], 4001);
    		
	    	$res = $this->getTable()->insert($result['data']);
	    	if (empty($res))
	    		throw new \Exception('error_insert');
	    	
	    	$return['message'] = 'Successfully added';
	    	$id = $this->getTable()->lastInsertValue;
	    	if (!empty($id))
	    		$return['link'] = $this->url()->fromRoute('host-api/resource', array('id' => $id), array(), true);
				    	
	    	return $this->sendSuccess($return, 201);
        
        } catch (\Exception $e) {
        	return $this->sendError(500, $e->getMessage(), 5001);
        }
    }

    public function update($id, $data)
    {
    	try {
    		$where = $this->getWhereResource($id);
	    	$res = $this->findRecurso($where);
	    	if (empty($res))
	    		return $this->sendError(404, 'Document Not Found', 4043);
	    	
	    	$cols = $this->getColsDisponiveis('_insert');
	    	$result = $this->validaRecurso($cols, $data);
	    	if (!empty($result['errors']))
	    		return $this->sendError(400, $result['errors'], 4001);
	    	
	    	$res = $this->getTable()->update($result['data'], $where);
	    	
	    	$return['message'] = 'Successfully changed';
    		return $this->sendSuccess($return);
    		
    	} catch (\Exception $e) {
    		return $this->sendError(500, $e->getMessage(), 5001);
    	}
    }
    

    public function delete($id)
    {
    	try {
    		$where = $this->getWhereResource($id);
    		$res = $this->findRecurso($where);
    		if (empty($res))
    			return $this->sendError(404, 'Document Not Found', 4043);
    	
    		$res = $this->getTable()->delete($where);
    		if (empty($res))
    			throw new \Exception('error_delete');
    	
    		$return['message'] = 'Deleted successfully';
    		return $this->sendSuccess($return);
    	} catch (\Exception $e) {
    		return $this->sendError(500, $e->getMessage(), 5001);
    	}
    }
    
    public function indexAction()
    {
        echo 'index';
        exit;
    }
    
    /* Auxiliadores */
    
    private function getTable() {
    	if (empty($this->tableGateway))
    		$this->tableGateway = new \Zend\Db\TableGateway\TableGateway($this->resource->getDb_table(), $this->adapter_externo);
    	 
    	return $this->tableGateway;
    }
    
    private function getColsDisponiveis($tipo='_select') {
    	$colunas = $this->getRepository('Coluna')->findBy(array(
    			'recurso' => $this->resource, $tipo => 'SIM', 'status' => 'VALIDO'
    	));
    	$cols = array();
    	foreach ($colunas as $coluna) {
    		$cols[$coluna->getId()] = $coluna->getNome();
    	}
    	return $cols;
    }
    
    private function getColValidadores($id_col) {
    	$sql = "SELECT c.*, v.`nome` FROM `column_validator` AS c
				INNER JOIN `validator` AS v ON v.`id` = c.`id_validator`
				WHERE c.`id_column` = :id_column
    			AND v.`status` = 'ATIVO'";
    	return $this->getEntityManager()->getConnection()->fetchAll($sql, array('id_column' => $id_col));
    }
    
    
    
    private function validaRecurso($cols, $data) {
    	$errors = array();
    	$data_db = array();
    	foreach ($cols as $col_id => $col_name) {
    		$value = !empty($data[$col_name]) ? $data[$col_name] : null;
    		$data_db[$col_name] = $value;
    		 
    		$validadores = $this->getColValidadores($col_id);
    		if (!empty($validadores)) {
    			 
    			foreach ($validadores as $validador) {
    
    				$params = !empty($validador['params']) ? @unserialize($validador['params']) : null;
    				$validator_zend = "\Zend\Validator\\{$validador['nome']}";
    				$validator_zend = new $validator_zend($params);
    				if (!$validator_zend->isValid($value)) {
    					$errors[$col_name] =  $validator_zend->getMessages();
    				}
    			}
    		}
    	}
    	 
    	return array('data' => $data_db, 'errors' => $errors);
    }
    
    private function getQueryPage($page, $per_page) {
    	return '?page='.$page.'&per_page='.$per_page;
    }
    
    private function findRecurso($ids) {
    	$select = $this->getTable()->getSql()->select();
    	$select->columns($this->getColsDisponiveis());
    	$select->where($ids);
    	$res = $this->getTable()->selectWith($select)->current();
    	if (!empty($res))
    		return $res->getArrayCopy();
    	 
    	return array();
    }
    
    private function getPrimarKeyResource() {
    	$sql = "SELECT c3.`nome` FROM `constraint` AS c1
				INNER JOIN `column_constraint` AS c2 ON c2.`id_constraint` = c1.`id`
				INNER JOIN `column` AS c3 ON c3.`id` = c2.`id_column`
				WHERE c1.`tipo` = 'PRIMARY_KEY'
				AND c3.`id_resource` = :id_resource";
    	$results = $this->getEntityManager()->getConnection()->fetchAll($sql, array('id_resource' => $this->resource->getId()));
    	if (empty($results))
    		throw new \Exception('PRIMARY_KEY not found');	
    	
    	$new_results = array();
    	foreach ($results as $result)
    		$new_results[] = $result['nome'];
    	
    	return $new_results;
    }
    
    
    private function getWhereResource($id) {
    	$keys = $this->getPrimarKeyResource();
    	if (count($keys) > 1) {
    		$params = (array)json_decode(base64_decode($id));
    		if (empty($params))
    			throw new \Exception('error decode id');
    		
    		$ids = array();
    		foreach ($keys as $key)
    			$ids[$key] = !empty($params[$key]) ? $params[$key] : null;
    		
    		return $ids;
    		
    	} elseif (!empty($keys[0])) {
    		return array($keys[0] => $id);
    	} else {
    		throw new \Exception('error decode id');
    	}
    }
    
    private function prepareId($id) {
        if ($id > 0) {
        	return (int)$id;
        } else {
        	$param = (array)json_decode(base64_decode($id));
        	if (empty($param))
        		throw new \Exception('error decode id');
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
