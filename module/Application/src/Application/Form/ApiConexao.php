<?php
namespace Application\Form;

use Zend\Form\Form;

class ApiConexao extends Form
{
    public function __construct()
    {
        parent::__construct('form_api_conexao');
        $this->setAttribute('id', 'form_api_conexao');
        $this->setAttribute('method', 'post');
        //$this->setAttribute('action', '/admin/index/save');
        
        $this->add(array(
				'name' => 'conexao',
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array(
					'required' => 'required'
				),
    			'options' => array (
    			),
    		))
    		->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    				'type'  => 'submit',
    				'value' => 'Escolher e continuar',
    				'class' => 'btn btn-primary',
    			),
    		));
        
    }
}