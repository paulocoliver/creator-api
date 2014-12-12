<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Validator\Db\NoRecordExists as NoRecordExists;

class IndexController extends \Admin\Controller\AbstractController
{
	/**
	 * @return \Zend\Authentication\AuthenticationService
	 */
    public function getAuth() {
    	return $this->getServiceLocator()->get('Auth\Admin');
    }
    
    public function indexAction()
    {
    	return $this->redirect()->toRoute('apis');
    }
    
    public function loginAction()
    {
        $auth = $this->getAuth();
        try {
        	 
        	if ($auth->hasIdentity())
        		throw new \Exception('ja_logado');
        	 
        	if ($this->getRequest()->isPost()) {
        		$data = $this->getRequest()->getPost()->toArray();
        		 
        		$authAdapter = $auth->getAdapter();
        		$authAdapter->setIdentity($data['email'])
        		             ->setCredential($data['senha']);
        		$result = $auth->authenticate($authAdapter);
        		
        		if ($result->isValid()) {
        			$auth->getStorage()->write($authAdapter->getResultRowObject(null, 'senha'));
        			$this->flashMessenger()->addInfoMessage('Welcome');
        			return $this->redirect()->toRoute('apis');
        		}else
        			throw new \Exception('error_login');
        	}
        	 
        } catch (\Exception $e) {
        	$e_msg = $e->getMessage();
        	if ($e_msg == 'ja_logado')
        		return $this->redirect()->toRoute("apis", array());
        
        	$this->flashMessenger()->addErrorMessage('Error Login.');
        	return $this->redirect()->toRoute('login');
        }
         
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function registerAction()
    {
        $auth = $this->getAuth();
        try {
        	 
        	if ($auth->hasIdentity())
        		throw new \Exception('ja_logado');
        	 
        	if ($this->getRequest()->isPost()) {
        		$data = $this->getRequest()->getPost()->toArray();
        		
        		$validator = new NoRecordExists(array(
			        'table'   => 'usuario',
			        'field'   => 'email',
			        'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')
        		));
        		
        		if (!$validator->isValid($data['email']))
        			throw new \Exception('email_existe');

        		$validator->setField('username');
        		if (!$validator->isValid($data['username']))
        			throw new \Exception('username_existe');
        			
        		$user = $this->getUser();
        		$user->setNome($data['nome'])
	        		->setEmail($data['email'])
	        		->setSenha($data['password'])
	        		->setUsername($data['username'])
	        		->setToken($user->createToken());
        		
        		$this->getEntityManager()->persist($user);
        		$this->getEntityManager()->flush();
        		 
        		$authAdapter = $auth->getAdapter();
        		$authAdapter->setIdentity($data['email'])
        		             ->setCredential($data['password']);
        		$result = $auth->authenticate($authAdapter);
        		if ($result->isValid()) {
        			$auth->getStorage()->write($authAdapter->getResultRowObject(null, 'senha'));
        			return $this->sendJson(true, 'Welcome', array('next' => $this->url()->fromRoute('apis')));
        		}else
        			throw new \Exception('error_register');
        	}
        	 
        } catch (\Exception $e) {
        	$e_msg = $e->getMessage();
        	
        	if ($e_msg == 'ja_logado')
        		return $this->redirect()->toRoute("apis", array());
        
        	return $this->sendJson(false, $e_msg);
        }
         
        $viewModel = new ViewModel();
        return $viewModel;
    }
    
    public function logoutAction()
    {
    	$this->getAuth()->clearIdentity();
    	return $this->redirect()->toRoute('login');
    }
}
