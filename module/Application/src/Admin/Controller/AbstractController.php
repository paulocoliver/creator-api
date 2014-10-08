<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\AbstractTable;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Doctrine\ORM\EntityManager;

abstract class AbstractController extends AbstractActionController
{
	/**
	 * @var \Zend\Http\PhpEnvironment\Request
	 */
	protected $request;

	/**
	 * @var \Zend\I18n\Translator\Translator
	 */
	protected $translator;
	
	protected $title='';
	
	protected $model = null;
	
	protected $model_obj = null;

	protected $repository = null;
	
	private $_user;
	
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;
	
	public function __construct() {
		$this->request = parent::getRequest();
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
	
	
	public function translate($message, $textDomain = 'default', $locale = null) {
	    return $this->getService('Translator')->translate($message, $textDomain = 'default', $locale = null);
	}
	
	
	/**
	 * @return \Application\Model\Usuario
	 */
	protected function getUser() {
		if (empty($this->_user)) {
			$auth = $this->getService('Auth\Admin');
			$data = $auth->getIdentity();
			$this->_user = $this->getRepository('Usuario')->find($data->id);
		}
		return $this->_user;
	}
	
	/**
	 * @return \Application\Model\UsuarioApi
	 */
	public function getUserApi($api) {
		try {
			$res = $this->getRepository('UsuarioApi')
			->findOneBy(array('usuario' => $this->getUser()->id, 'api' => $api));
			if (empty($res))
				throw new \Exception();
	
			if (!in_array($res->permissao, array('OWNER','EDIT')))
				throw new \Exception();
	
			return $res;
		} catch (\Exception $e) {
			return null;
		}
	}
	
	
	/**
	 * @return AbstractTable
	 */
	protected function getModel($new=false) {
	    if (empty($this->model_obj) || $new) {
            $model = new $this->model();
            if (!$new)
                $this->model_obj = $model;
	    } else {
            $model = $this->model_obj;
	    }
		return $model;
	}

	/**
	 * @return \Zend\View\Model\JsonModel
	 */
	protected function sendJson($success=false, $msg=null, $dados=null) {
	    
	    $json = new \Zend\View\Model\JsonModel();
        $json->setTerminal(true);
        $json->setVariable('success', $success);
        
        if (!empty($msg))
            $json->setVariable('msg', $this->translate($msg));

        if (!empty($dados))
            $json->setVariable('data', $dados);
        
		return $json;
	}
	
	protected function isAjaxJson() {
		$accept = $this->request->getHeaders()->get('Accept');
		$match = $accept->match('application/json');
		if (!$match || $match->getTypeString() == '*/*') {
			// not application/json
			return false;
		}
		return $this->request->isXmlHttpRequest();
	}
	
	
	protected function getViewWizard($variables=null, $child=null) {
	    $view = new ViewModel($variables);
	    $view->setTemplate('admin/api/wizard');
	    if (!empty($child))
	       $view->addChild($child, 'content');
	    return $view;
	}
	
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
    
        $events = $this->getEventManager();
        $events->attach('dispatch', array($this, 'preDispatch'), 100);
        $events->attach('dispatch', array($this, 'postDispatch'), 2);
    } 
    
    public function preDispatch () {
        $this->translator = $this->getServiceLocator()->get('Translator');
    }
    
    public function postDispatch () {
        $this->layout()->setVariable('user', $this->getUser());
    }
	
	public function serverUrl($requestUri = null) {
		$serverUrl = new \Zend\View\Helper\ServerUrl();
		return $serverUrl->__invoke($requestUri);
	}
}