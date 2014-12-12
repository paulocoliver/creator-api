<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Zend\Validator\Db\NoRecordExists as DbNoRecordExists;
use Symfony\Component\Console\Application;

class IndexController extends AbstractController
{
	public function __construct() {
		parent::__construct();
	}
	
	public function userInfoAction() {
		
		$user = $this->getUser();
		$form = new Form\Usuario();
		$form->setData($user->toArray());
		$form->get('senha')->setValue('');
		
		if ($this->getRequest()->isPost()) {
			try {
				$form->setInputFilter($user->getInputFilter());
				$post = $this->request->getPost();
				$form->setData($post);
				if ($form->isValid()) {
					$data = $form->getData();
					$validator = new DbNoRecordExists(array(
						'table'   => 'usuario',
						'field'   => 'email',
						'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
					));
					
					if ($data['email'] != $user->getEmail() && !$validator->isValid($data['email']))
						throw new \Exception('email_existe');
					
					$validator->setField('username');
					if ($data['username'] != $user->getUsername() && !$validator->isValid($data['username']))
						throw new \Exception('username_existe');
					
					if (empty($data['senha']))
						unset($data['senha']);
					 
					$user->setData($data);
					
					$this->getEntityManager()->persist($user);
					$this->getEntityManager()->flush();
					return $this->sendJson(true, 'save_ok');
				} else
					throw new \Exception('form_error');
				
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				return $this->sendJson(false, $msg);
			}
		}
		
		$contentView = new ViewModel(array('form' => $form));
		$contentView->setTemplate('admin/index/user-info');
		return $this->getViewUserSidebar(array('item' => 1), $contentView);
	}

	public function userTokenAction() {
		if ($this->getRequest()->isPost()) {
			
			try {
				$user = $this->getUser();
				$user->setToken($user->createToken());
				$this->getEntityManager()->persist($user);
				$this->getEntityManager()->flush();
				return $this->sendJson(true, 'Novo token gerado com sucesso', array('token' => $user->getToken()));
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				return $this->sendJson(false, $msg);
			}
		}
		$contentView = new ViewModel();
		$contentView->setTemplate('admin/index/user-token');
		return $this->getViewUserSidebar(array('item' => 2), $contentView);
	}
	
	public function publicApisAction() {
		
		if ($this->request->isPost()) {
			try {
				
				$id = $this->request->getPost('id');
				$api = $this->getRepository('Api')->findOneBy(array('id' => $id, 'acesso' => 'PUBLICO'));
				if (empty($api))
					throw new \Exception();
	
				$usuario_api = new Model\UsuarioApi();
				$usuario_api->setApi($api);
				$usuario_api->setUsuario($this->getUser());
				$usuario_api->setPermissao('ACCESS');
				$this->getEntityManager()->persist($usuario_api);
				$this->getEntityManager()->flush();
				return $this->sendJson(true, 'Movido para minhas APIs');
			
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				return $this->sendJson(false, $msg);
			}
		}
		
		
		$this->title = 'My Apis';
		
		$sql = "SELECT api.*, usuario_acesso_api.permissao FROM api
    			LEFT JOIN usuario_acesso_api ON usuario_acesso_api.id_api = api.id AND usuario_acesso_api.id_usuario = :id_usuario
    			WHERE api.acesso = 'PUBLICO'
				AND usuario_acesso_api.permissao IS NULL";
		$q = $this->request->getQuery('q');
		if (!empty($q))
			$sql .= " AND api.titulo LIKE :query";
		
		$apis = $this->getEntityManager()->getConnection()->fetchAll($sql, array('id_usuario' => $this->getUser()->id, 'query' => '%'.$q.'%'));
		return new ViewModel(array('results' => $apis));
	}
	
	private function getViewUserSidebar($variables=null, $child=null) {
		$view = new ViewModel($variables);
		$view->setTemplate('admin/index/user-sidebar');
		if (!empty($child))
			$view->addChild($child, 'content');
		return $view;
	}
}