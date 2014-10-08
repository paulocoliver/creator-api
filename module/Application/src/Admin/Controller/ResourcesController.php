<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Symfony\Component\Console\Application;

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
		
		$contentView = new ViewModel(array('userApi' => $user_api, 'recursos' => $recursos, 'error_msg_conexao' => $error_msg_conexao));
		$contentView->setTemplate('admin/resources/index');
		return $this->getViewWizard(array('item' => 4), $contentView);
	}
	
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
			}
			
			
			
		} catch (\Exception $e) {
			$msg = $e->getMessage();
            if ($this->request->isXmlHttpRequest())
            	return $this->sendJson(false, $msg);
            
            $this->flashMessenger()->addErrorMessage($msg);
        	return $this->redirect()->toRoute('apis');
		}
		
		$contentView = new ViewModel(array('api' => $user_api->api, 'resource' => $resource, 'form' => $form));
		$contentView->setTerminal(true);
		return $contentView;
	}
	
}