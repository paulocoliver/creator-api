<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Symfony\Component\Console\Application;
use Zend\Validator\Db\NoRecordExists as DbNoRecordExists;

class ResourcesController extends AbstractController
{
	public function __construct() {
		parent::__construct();
		$this->model = 'Application\Model\ApiTable';
		$this->route_list = 'admin-servico';
	}
	
	/**
	 * @return \Application\Service\RecursoEntity
	 */
	public function serviceRecurso() {
		return $this->getService('Service\RecursoEntity');
	}

	/**
	 * @return \Application\Service\DbExternoApi
	 */
	public function dbExternoApi() {
		return $this->getService('DbExternoApi');
	}
	
	private function getRecursos($api) {
		return $this->getRepository('Recurso')->findBy(array('api' => $api), array('status' => 'ASC', 'db_table' => 'ASC'));
	}
	
	/**
	 * @return \Application\Model\Recurso
	 */
	private function getRecurso($id_resource, $api) {
		return $this->getRepository('Recurso')->findOneBy(array('id' => $id_resource, 'api' => $api));
	}

	/**
	 * @return \Application\Model\Coluna
	 */
	private function getColuna($id_column) {
		return $this->getRepository('Coluna')->findOneBy(array('id' => $id_column/*, 'recurso' => $id_resource*/));
	}
	
	public function indexAction() {
		try {
			$error_msg_conexao = '';
			$id = $this->params('id');
			$reload_db = $this->params('reload', false);
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
			
			if (empty($user_api->api->conexao->getId()))
				throw new \Exception('conexao_nao_cadastrada');
			
			$recursos = $this->getRecursos($user_api->api);
			if ($reload_db || empty($recursos)) {
				$db = $this->dbExternoApi();
				$db->setConexao($user_api->api->conexao);
				$db->sincronizarRecursos($user_api->api);
				if ($reload_db)
					$this->flashMessenger()->addSuccessMessage('Operação realizada com sucesso');
					return $this->redirect()->toRoute('api-resources', array(), array(), true);
				$recursos = $this->getRecursos($user_api->api);
			}
			
			if (empty($recursos))
				$error_msg_conexao = 'Não foi encontrada nenhuma tabela no banco de dados.';
			
			$validadores = $this->getRepository('Validador')->findBy(array('status' => 'ATIVO'), array('nome' => 'ASC'));
			
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			if ($this->isAjaxJson())
				return $this->sendJson(false, $msg);
			
			if ($msg == 'conexao_nao_cadastrada' || $msg == 'error_connecting') {
				$error_msg_conexao = $msg;
			} else {
				$this->flashMessenger()->addErrorMessage($msg);
				return $this->redirect()->toRoute('apis');
			}
		}
		
		$contentView = new ViewModel(array('userApi' => $user_api, 'recursos' => $recursos, 'error_msg_conexao' => $error_msg_conexao, 'validadores' => $validadores));
		$contentView->setTemplate('admin/resources/index');
		return $this->getViewWizard(array('item' => 4), $contentView);
	}
	
	
	public function editAction() {
		try {
			$id = $this->params('id');
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
				
			$id_resource = $this->params('id_resource');
			$resource = $this->getRecurso($id_resource, $user_api->api);
			if (empty($resource))
				throw new \Exception('not_found');
				
			$form = new Form\Recurso();
			$form->bind($resource);
			if ($resource->getTipo() == 'VIEW') {
				$form->get('_post')->setAttribute('disabled', 'disabled');
				$form->get('_put')->setAttribute('disabled', 'disabled');
				$form->get('_delete')->setAttribute('disabled', 'disabled');
			}
			
			if ($this->request->isPost()) {
				$form->setInputFilter($resource->getInputFilter());
				$form->setData($this->request->getPost());
				if ($form->isValid()) {
					$data = $form->getData();
					$validator = new DbNoRecordExists(array(
						'table'   => 'resource',
						'field'   => 'resource',
						'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
					));
					$validator->getSelect()->where(array('id_api' => $user_api->api->id));
					if (!$validator->isValid($data->resource))
						throw new \Exception('URL do recurso já utilizada, informe uma nova URL.');
					
					$resource->setData($data);
					$this->getEntityManager()->persist($resource);
					$this->getEntityManager()->flush();
					
					$resource = $resource->toArray();
					unset($resource['api']);
					unset($resource['colunaCollection']);
					if ($this->request->isXmlHttpRequest())
						return $this->sendJson(true, 'save_ok', array('resource' => $resource));
				} else
					throw new \Exception('form_error');
				
			} elseif ($this->request->isDelete()) {
				$this->getEntityManager()->remove($resource);
				$this->getEntityManager()->flush();
				return $this->sendJson(true, 'del_ok');
			}
			
		} catch (\Exception $e) {
			$msg = $e->getMessage();
            if ($this->request->isXmlHttpRequest())
            	return $this->sendJson(false, $msg);
            
            $this->flashMessenger()->addErrorMessage($msg);
        	return $this->redirect()->toRoute('apis');
		}
		
		$form->get('resource')->setOptions(array('add-on-prepend' => 'http://'.$user_api->api->url.'.criadorapi.com/'));
		
		$contentView = new ViewModel(array('api' => $user_api->api, 'resource' => $resource, 'form' => $form));
		$contentView->setTerminal(true);
		return $contentView;
	}
	
	
	###### COLUMNS ######
	
	public function columnsAction() {
	
		try {
			$id = $this->params('id');
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
				
			$id_resource = $this->params('id_resource');
			$resource = $this->getRecurso($id_resource, $user_api->api);
			if (empty($resource))
				throw new \Exception('not_found');
				
		} catch (\Exception $e) {
			throw new \Exception();
		}
	
		$contentView = new ViewModel(array('api' => $user_api->api, 'resource' => $resource));
		$contentView->setTerminal(true);
		return $contentView;
	}

	
	public function columnsEditAction() {
		try {
			$id = $this->params('id');
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
				
			$id_column = $this->params('id_column');
			$column = $this->getColuna($id_column);
			if (empty($column))
				throw new \Exception('not_found');
			
			$form = new Form\Coluna();
			$form->bind($column);
			
			if ($this->request->isPost()) {
				$form->setInputFilter($column->getInputFilter());
				$form->setData($this->request->getPost());
				if ($form->isValid()) {
					$data = $form->getData();
					$column->setData($data);
					$this->getEntityManager()->persist($column);
					$this->getEntityManager()->flush();
					if ($this->request->isXmlHttpRequest())
						return $this->sendJson(true, 'save_ok');
				} else
					throw new \Exception('form_error');
			}
				
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit;
			throw new \Exception();
		}
	
		$contentView = new ViewModel(array('api' => $user_api->api, 'column' => $column, 'form' => $form));
		$contentView->setTerminal(true);
		return $contentView;
	}
	
	public function columnsValidatorsAction() {
	
		try {
			$id = $this->params('id');
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
			
			$id_column = $this->params('id_column');
			$column = $this->getColuna($id_column);
			if (empty($column))
				throw new \Exception('not_found');
			
				
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit;
			throw new \Exception();
			
		}
	
		$contentView = new ViewModel(array('api' => $user_api->api, 'column' => $column));
		$contentView->setTerminal(true);
		return $contentView;
	}
	
	private function getFormValidator($nome) {
		$validator_zend = "\Application\Form\Validator\\{$nome}";
		return new $validator_zend();
	}
	
	public function columnsValidatorsAddAction() {
		try {
			$id = $this->params('id');
			$user_api = $this->getUserApi($id);
			if (empty($user_api))
				throw new \Exception('not_found');
				
			$id_column = $this->params('id_column');
			$column = $this->getColuna($id_column);
			if (empty($column))
				throw new \Exception('not_found');
			
			$id_validator = $this->params('id_validator');
			$validador = $this->getRepository('Validador')->find($id_validator);
			if (empty($validador))
				throw new \Exception('not_found');
			
			$form = $this->getFormValidator($validador->getNome());
			
			$coluna_validador = $this->getRepository('ColunaValidador')->findOneBy(array('coluna' => $column, 'validador' => $validador));
			if (!empty($coluna_validador)) {
				$data = $coluna_validador->getParams();
				$form->setData(!empty($data) ? unserialize($data) : array());
			} else
				$coluna_validador = new Model\ColunaValidador();
			
			if ($this->request->isPost()) {
				$form->setData($this->request->getPost());
				if ($form->isValid()) {
					
					$data = $form->getData();
					$coluna_validador->setColuna($column);
					$coluna_validador->setValidador($validador);
					$coluna_validador->setParams(serialize($data));
					
					$this->getEntityManager()->persist($coluna_validador);
					$this->getEntityManager()->flush();
					
					if ($this->request->isXmlHttpRequest())
						return $this->sendJson(true, 'save_ok');
				} else
					throw new \Exception('form_error');
			
			} elseif ($this->request->isDelete()) {
				
				if (empty($coluna_validador->getId()))
					throw new \Exception('not_found');
				
				$this->getEntityManager()->remove($coluna_validador);
				$this->getEntityManager()->flush();
				
				return $this->sendJson(true, 'del_ok');
			}
			
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit;
			throw new \Exception();
				
		}
		
		$contentView = new ViewModel(array('api' => $user_api->api, 'column' => $column, 'validador' => $validador, 'form' => $form));
		$contentView->setTerminal(true);
		return $contentView;
	}
	
}