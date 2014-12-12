<?php
namespace Api\Controller;

use Zend\View\Model\ViewModel;
use Admin\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Application\Model;
use Doctrine\ORM\EntityManager;

class DocController extends AbstractController
{
	/**
	 * @var \Application\Model\Api
	 */
	private $api;
	
	private $view;
	
    public function __construct() {
    }
    
    public function onDispatch(MvcEvent $e)
    {
    	try {
    		
    		$this->view = new ViewModel();
    		$this->view->setTerminal(true);
    		
    		### Api
    		$param_api = $this->params('api');
    		$this->api = $this->getRepository('Api')->findOneBy(array('url' => $param_api, 'status' => 'ATIVO'));
    		if (empty($this->api)) {
    			$return = $this->sendError(404, 'Api not found', 4041);
    			$e->setViewModel($return);
    			return $return;
    		}
    		
    		### Auth
    		if ($this->api->getAcesso() == 'PRIVADO') {
    			$res =  $this->getUserApi($this->api);
    			if (empty($res)) {
    				$this->view->setTemplate('api/doc/401')->setTerminal(false);
    			}
    		}

	        return parent::onDispatch($e);
	        
        } catch (\Exception $exp) {
        	$this->view->setTemplate('api/doc/500')->setTerminal(false);
        }
         
    }
    
    private function teste () {
    	
    	$request = new \Zend\Http\Request();
    	
    	// Performing a POST request
    	$request->setMethod(Request::METHOD_POST);
    	
    	$client = new \Zend\Http\Client('http://example.org');
    	$client->setRequest($request);
    	$client = new \Zend\Http\Client();
    	$client->setRawBody(json_encode($value));
    	$client->setMethod($method);
    	
    	$client->setHeaders(array('X-Api-Token' => 'd2UyM3dlMjN3ZTIz'));
    	$response = $client->dispatch();
    }
    
    public function docAction()
    {
    	$recursos = $this->getRepository('Recurso')->findBy(
    		array('api' => $this->api, 'disponivel' => 'SIM'), 
    		array('resource' => 'ASC')
    	);
    	$this->view->setVariables(array('api' => $this->api, 'recursos' => $recursos, 'user' => $this->getUser()));
    	return $this->view;
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
