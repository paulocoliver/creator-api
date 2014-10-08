<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function getAuth() {
    	return $this->getServiceLocator()->get('Auth\Admin');
    }
    
    public function indexAction()
    {
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
        		return $this->redirect()->toRoute("admin", array());
        
        	$this->flashMessenger()->addErrorMessage('Error Login.');
        	return $this->redirect()->toRoute('login');
        }
         
         
        $viewModel = new ViewModel();
         
        return $viewModel;
    }

    public function registerAction()
    {
    }
}
