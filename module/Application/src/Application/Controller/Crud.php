<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
	Zend\Paginator\Adapter\DbSelect as PaginatorAdapterDbSelect;

use Application\Model\AbstractTable;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

abstract class CrudController extends AbstractController {

	protected $menu_index;
	protected $model;
	protected $select_default;
	protected $form;
	protected $route;
	protected $titles;
	protected $template_new;
	protected $variables_view=array();
	
	/*public function setEventManager(EventManagerInterface $events)
	{
		parent::setEventManager($events);
	
		$controller = $this;
		$events->attach('dispatch', function (MvcEvent $e) use ($controller) {
			$request = $e->getRequest();
			$method  = $request->getMethod();
			echo $method;
			
			$viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
			$layout  = $viewHelperManager->get('Layout');
			$layout->setVariable('teste', 2121);
			
			
			return;
			
		}, 100); 
	}*/
	
	public function __construct() {
	}

	
	public function indexAction() {
		$this->layout()->setVariable('menu_index', $this->menu_index);
		$page = $this->params()->fromRoute('page');
		
		$select = $this->getSelectDefault();
		$adapter = new PaginatorAdapterDbSelect($select, $this->getModel()->getAdapter());
		$paginator = new Paginator($adapter);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(10);
		
		$this->variables_view = array_merge($this->variables_view, array('paginator' => $paginator, 'page' => $page, 'titles' => $this->titles));

		return new ViewModel($this->variables_view);
	}

	public function newAction() {
		$form = new $this->form();
		$form->setLabel($this->titles['new']);

		if ($this->request->isPost()) {
			$post = $this->request->getPost()->toArray();
			unset($post['actions']);
			$form->setData($post);
			if ($form->isValid()) {
				$this->getModel()->save($post);
				return $this->redirect()->toRoute($this->route, array());
			}
		}
		return new ViewModel(array('form' => $form, 'titles' => $this->titles));
	}

	public function editAction() {
		$form = new $this->form();
		$form->setLabel($this->titles['edit']);
		
		$id = $this->params('id', 0);
		$res = $this->getModel()->select(array('id' => $id))->current();
		if (empty($res)) {
			$this->flashMessenger()->addErrorMessage('Não encontrado');
			return $this->redirect()->toRoute($this->route);
		}
		
		$form->setData($this->preparaDataForm($res->getArrayCopy()));

		if ($this->request->isPost()) {
			$post = $this->request->getPost()->toArray();
			unset($post['actions']);
			$form->setData($post);
			if ($form->isValid()) {
				$post['id'] = $id;
				$this->getModel()->save($post);
				return $this->redirect()->toRoute($this->route);
			}
		}
		$viewModel = new ViewModel(array('form' => $form, 'titles' => $this->titles));
		$viewModel->setTemplate($this->template_new);
		return $viewModel;
	}

	public function deleteAction() {
		$id = $this->params('id', 0);
		
		if (!empty($id) && $this->getModel()->delete(array('id' => $id)))
			$this->flashMessenger()->addSuccessMessage('Success');
		else	
			$this->flashMessenger()->addErrorMessage('Error');
		
		return $this->redirect()->toRoute($this->route);
	}

	
	/**
	 *
	 * @return AbstractTable
	 */
	public function getModel() {
		return new $this->model();
	}

	public function getSelectDefault() {
		if (empty($this->select_default))
			$this->select_default = $this->getModel()->getSql()->select();
		
		return $this->select_default;
	}
	
	protected function preparaDataForm($data) {
		return $data;
	}

}
