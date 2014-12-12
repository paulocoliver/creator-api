<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Application\Form;
use Application\Model;
use Zend\Validator\Db\NoRecordExists as DbNoRecordExists;

class ApiController extends AbstractController
{
    public function __construct() {
    	parent::__construct();
    	$this->route_list = 'admin-servico';
    	$this->repository = 'Api';
    }
    
    public function indexAction()
    {
        $this->title = 'My Apis';
    	$sql = "SELECT api.*, usuario_acesso_api.permissao FROM api 
    			INNER JOIN usuario_acesso_api ON usuario_acesso_api.id_api = api.id 
    			WHERE usuario_acesso_api.id_usuario = :id_usuario";
    	$apis = $this->getEntityManager()->getConnection()->fetchAll($sql, array('id_usuario' => $this->getUser()->id));
        return new ViewModel(array('results' => $apis));
    }

    public function addAction()
    {
        try {
        	$id = $this->params('id');
        	if (!empty($id)) {
	        	$user_api = $this->getUserApi($id);
	        	if (empty($user_api))
	        		throw new \Exception('not_found');
	        	
	        	$api = $user_api->api;
        	} else {
	        	$api = new Model\Api();
        	}
        	
        	$form = new Form\Api();
        	$form->setData($api->toArray());
            
            if ($this->request->isPost()) {
            	
	    		$form->setInputFilter($api->getInputFilter());
	    		$post = $this->request->getPost();
	    		$form->setData($post);
	    		if ($form->isValid()) {
					$data = $form->getData();
					
					if ($api->url != $data['url']) {
						$validator = new DbNoRecordExists(array(
							'table'   => 'api',
							'field'   => 'url',
							'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
						));
						if (!$validator->isValid( $data['url'] ))
							throw new \Exception('URL já utilizada por outra API, informe uma nova URL.');
					}
					
		    		$api->setData($data);
		    		$this->getEntityManager()->persist($api);
		    		$this->getEntityManager()->flush();
		    		
		    		if (empty($id)) {
		    			$usuario_api = new Model\UsuarioApi();
		    			$usuario_api->setApi($api);
		    			$usuario_api->setUsuario($this->getUser());
		    			$usuario_api->setPermissao('OWNER');
			    		$this->getEntityManager()->persist($usuario_api);
			    		$this->getEntityManager()->flush();
		    		}
		    		
					if ($this->request->isXmlHttpRequest())
                		return $this->sendJson(true, 'save_ok', array('next' => $this->url()->fromRoute('api-share', array('id' => $api->id))));
		    		
	    		} else
					throw new \Exception('form_error');
            } elseif ($this->request->isDelete()) {
            	
            	if (empty($api->getId()) || $user_api->getPermissao() != 'OWNER')
            		throw new \Exception('not_found');
            	
            	$this->getEntityManager()->remove($api);
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
        
        $contentView = new ViewModel(array('form' => $form));
        $contentView->setTemplate('admin/api/add');
        return $this->getViewWizard(array('item' => 1), $contentView);
    }

    public function shareAction()
    {
        try {
        	$id = $this->params('id');
        	$user_api = $this->getUserApi($id);
        	if (empty($user_api))
        		throw new \Exception('not_found');
        	
            # Add user Api
        	if ($this->request->isPost()) {
        		$permissao = ($this->request->getPost('edit') == 'SIM') ? 'EDIT' : 'ACCESS';
        		$usuario = $this->request->getPost('user');
        		if (empty($usuario))
        			throw new \Exception('not_found');
        		
        		$user_add = $this->getRepository('Usuario')->findOneBy(array('email' => $usuario));
        		if (empty($user_add)) {
	        		$user_add = $this->getRepository('Usuario')->findOneBy(array('username' => $usuario));
	        		if (empty($user_add))
	        			throw new \Exception('not_found');
        		}
        		
        		try {
	        		$usuario_api = new Model\UsuarioApi();
	        		$usuario_api->setApi($user_api->api);
	        		$usuario_api->setUsuario($user_add);
	        		$usuario_api->setPermissao($permissao);
	        		$this->getEntityManager()->persist($usuario_api);
	        		$this->getEntityManager()->flush();
        		} catch (\Exception $e) {
        			throw new \Exception('error');
        		}
        		
        		$user = $user_add->toArray();
        		$user['permissao'] = $permissao;
        		$user['id_usuario'] = $user['id'];
        		unset($user['senha']);
        		unset($user['token']);
        		$gravatar = $this->getServiceLocator()->get('viewhelpermanager')->get('gravatar');
        		$user['gravatar'] = $gravatar($user['email'], array('img_size' => 50))->getImgTag();
        		
        		return $this->sendJson(true, 'add_ok', array('user' => $user));
        		
        	# Delete user Api	
        	} elseif ($this->request->isDelete()) {
        		$id_usuario = $this->request->getQuery('id_usuario');
        		$user_api = $this->getRepository('UsuarioApi')->findOneBy(array('api' => $user_api->api, 'usuario' => $id_usuario));
        		
        		if (empty($user_api) || $user_api->permissao == 'OWNER')
        			throw new \Exception('not_found');
        		
        		$this->getEntityManager()->remove($user_api);
        		$this->getEntityManager()->flush();
        		
        		return $this->sendJson(true, 'del_ok', array('id_usuario' => $id_usuario));
        	}
        
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if ($this->isAjaxJson())
            	return $this->sendJson(false, $msg);
            
            $this->flashMessenger()->addErrorMessage($msg);
            return $this->redirect()->toRoute('apis');
        }
        
        
        $sql = "SELECT usuario_acesso_api.*, usuario.nome AS nome, usuario.email AS email, usuario.username AS username 
        		FROM usuario_acesso_api 
        		INNER JOIN usuario ON usuario.id = usuario_acesso_api.id_usuario 
        		WHERE id_api = :id_api";
        $users = $this->getEntityManager()->getConnection()->fetchAll($sql, array('id_api' => $user_api->api->id));
        
        $contentView = new ViewModel(array('users' => $users, 'userApi' => $user_api));
        $contentView->setTemplate('admin/api/share');
        return $this->getViewWizard(array('item' => 2), $contentView);
    }

    public function connectionAction()
    {
    	try {
    		$id = $this->params('id');
    		$user_api = $this->getUserApi($id);
    		if (empty($user_api))
    			throw new \Exception('not_found');
    		
    		if ($this->request->isPost()) {
    			$id_conexao = $this->request->getPost('conexao');
    			$conexao = $this->getRepository('Application\Model\Conexao')
    				->findOneBy(array('id' => $id_conexao, 'usuario' => $this->getUser()->id));
    			
    			$user_api->api->conexao = $conexao;
    			$this->getEntityManager()->persist($user_api->api->conexao);
    			$this->getEntityManager()->flush();
    			if ($this->request->isXmlHttpRequest())
    				return $this->sendJson(true, 'save_ok', array('next' => $this->url()->fromRoute('api-resources-reload', array('id' => $user_api->api->id))));
    		}
    		
    		$formApiConexao = new Form\ApiConexao();
    		$formApiConexao->get('conexao')->setValueOptions($this->getOptionsConexao());
    		$formApiConexao->get('conexao')->setValue($user_api->api->conexao);
    		
    		$formAddConexao = new Form\Conexao();
    		
    	} catch (\Exception $e) {
    		$msg = $e->getMessage();
    		if ($this->request->isXmlHttpRequest())
    			return $this->sendJson(false, $msg);
    		
    		$this->flashMessenger()->addErrorMessage($msg);
    		return $this->redirect()->toRoute('apis');
    	}
    	
        $contentView = new ViewModel(
        	array('userApi' => $user_api, 'formApiConexao' => $formApiConexao, 'formAddConexao' => $formAddConexao)
        );
        $contentView->setTemplate('admin/api/connection');
        return $this->getViewWizard(array('item' => 3), $contentView);
    }
    
    
    private function getOptionsConexao() {
    	$new_conexoes = array();
    	$qb = $this->getRepository('Application\Model\Conexao')
	    	->createQueryBuilder('c')
	    	->where('c.usuario = :usuario')->setParameters(array('usuario' => $this->getUser()->id));
    	$conexoes = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    	foreach ($conexoes as $conexao)
    		$new_conexoes[$conexao['id']] = $conexao['nome'];
    	return $new_conexoes;
    }
    
    
    
}
