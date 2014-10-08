<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Application\Form\Conexao as FormConexao;
use Application\Model\Conexao;

class ConnectionController extends AbstractController
{
	public function __construct() {
		parent::__construct();
		//$this->route_list = 'admin-servico';
		$this->repository = 'Application\Model\Conexao';
	}
	
    public function indexAction()
    {
        $this->title = 'My Apis';
        //$this->layout()->setVariable('title', $this->menu_index);
    }

    public function saveAction()
    {
    	try {
    		
	    	$id = (int) $this->params()->fromRoute('id', 0);
	    	
	    	if ($this->request->isPost()) {
	    		
	    		$conexao = new Conexao();
	    		
	    		$form = new FormConexao();
	    		$form->setInputFilter($conexao->getInputFilter());
	    		$form->setData($this->request->getPost());
	    		if ($form->isValid()) {
					$data = $form->getData();
	    			unset($data['submit']);
	    			if (isset($id) && $id > 0)
	    				$conexao = $this->getRepository()->find($id);
	    			
	    			$conexao->setData($data);
	    			$conexao->usuario = $this->getUser();
	    			$this->getEntityManager()->persist($conexao);
	    			$this->getEntityManager()->flush();
	    			
	    			if ($this->request->isXmlHttpRequest())
	    				return $this->sendJson(true, 'save_ok', array('conexao' => $conexao->toArray()));
				} else
					throw new \Exception('form_error');
	    	}
	    	
    	} catch (\Exception $e) {
    		$msg = $e->getMessage();
    		if ($this->request->isXmlHttpRequest())
    			return $this->sendJson(false, $msg);
    	}
    	
    	
        $contentView = new ViewModel();
        $contentView->setTemplate('admin/connection/add');
        return $this->getViewWizard(array('item' => 1), $contentView);
    }
}
