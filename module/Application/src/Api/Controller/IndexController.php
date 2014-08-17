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
use Zend\View\Model\JsonModel;
//use Zend\Db\Metadata\Object\ConstraintObject as ConstraintObject;

# http://framework.zend.com/manual/2.3/en/modules/zend.mvc.quick-start.html#create-a-controller
class IndexController extends AbstractRestfulController
{
    /**
     * @var HttpRequest
     */
    protected $request;
    
    private $api;
    private $resource;
    private $adapter_externo;
    private $viewModel;
    
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
        # Api
        $param_api = $this->params('api');
        $apiTable = new Model\ApiTable();
        $api = $apiTable->select(array('url' => $param_api))->current();
        if (empty($api)) {
            $response = $e->getResponse();
            $response->setStatusCode(404);
            $response->setContent('Api not found');
            return $response;
        }
        $this->api = $api->getArrayCopy();
         
        # Resource
        $param_resource = $this->params('resource');
        if (!empty($param_resource)) {
            $resourceTable = new Model\ResourceTable();
            $resource = $resourceTable->select(array('id_api' => $this->api['id'], 'resource' => $param_resource))->current();
            if (empty($resource)) {
                $response = $e->getResponse();
                $response->setStatusCode(404);
                $response->setContent('Resource not found');
                return $response;
            }
            $this->resource = $resource->getArrayCopy();
            $method = strtolower($e->getRequest()->getMethod());
            if (array_key_exists($method, $this->resource)) {
                if ($this->resource[$method] != 'ATIVO') {
                    $response = $e->getResponse();
                    $response->setStatusCode(405);
                    $response->setContent('Method Not Allowed');
                    return $response;
                }
            }
            
            $this->getAdapterByConexao($this->api['id_conexao']);
        }
         
        $this->viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
        $this->viewModel->setTerminal(true);
        parent::onDispatch($e);
    }
    
    public function getList()
    {
        try {
            $results = $this->getTable()->select()->toArray();
            return $this->viewModel->setVariables(array('data' => $results));
        } catch (\Exception $e) {
        }
    }
    
    public function get($id)
    {
        echo 'get:'.$id;
        exit;
        
        
    	$this->response->setStatusCode(405);
    
    	return array(
    			'content' => 'Method Not Allowed'
    	);
    }
    
    public function indexAction()
    {
        echo 'index';
        exit;
    }
    
    public function getTable() {
        return new \Zend\Db\TableGateway\TableGateway($this->resource['table'], $this->adapter_externo);
    }
    
    public function getDriver($db) {
    	$drivers = array(
    			'MySQL'         => 'Mysqli',
    			'Oracle'        => 'Pdo_Pgsql',
    			'SQLServer'     => 'Sqlsrv',
    			'PostgreSQL'    => 'Pgsql',
    			'SQLite'        => 'Pdo_Sqlite'
    	);
    	return $drivers[$db];
    }
    
    private function getAdapterByConexao($id_conexao) {
    
    	$conexaoTable = new Model\ConexaoTable();
    	$conexao = $conexaoTable->select(array('id' => $id_conexao))->current();
    	if (!empty($conexao)) {
    
    		$driver = $this->getDriver($conexao->db);
    		$this->adapter_externo = new \Zend\Db\Adapter\Adapter(array(
				'driver'   => $driver,
				'hostname' => $conexao->host,
				'database' => $conexao->database,
				'username' => $conexao->username,
				'password' => $conexao->password,
    		    'options' => array(
    		    	'buffer_results' => true,
    		    ),
    		));
    	}
    
    }
}
