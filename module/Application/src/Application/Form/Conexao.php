<?php
namespace Application\Form;

use Zend\Form\Form;

class Conexao extends Form
{
    public function __construct()
    {
        parent::__construct('form_conexao');
        $this->setAttribute('id', 'form_conexao');
        $this->setAttribute('method', 'post');
        //$this->setAttribute('action', '/admin/index/save');
        
        $this->add(array(
			'name' => 'nome',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
        		'required' => 'required',
			),
        	'options' => array(
        		'label' => 'Nome',
        	),
        ));
        
        $this->add(array(
        	'name' => 'db',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array (
				'required' => 'required' 
			),
			'options' => array (
				'label' => 'DB',
				'value_options' => array (
					'MySQL' => 'MySQL',
					'Oracle' => 'Oracle',
					'SQLServer' => 'SQLServer',
					'PostgreSQL' => 'PostgreSQL' 
				) 
			),
        ));
        
        $this->add ( array (
			'name' => 'host',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array (
				'required' => 'required' 
			),
			'options' => array (
				'label' => 'Host' 
			) 
		) );
        
        $this->add ( array (
			'name' => 'username',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array (
				'required' => 'required' 
			),
			'options' => array (
				'label' => 'Username' 
			) 
		) );
		
		$this->add ( array (
			'name' => 'password',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array (
				'required' => 'required' 
			),
			'options' => array (
				'label' => 'Password' 
			) 
		) );
		
		$this->add ( array (
			'name' => 'data_base',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array (
				'required' => 'required' 
			),
			'options' => array (
				'label' => 'Database' 
			) 
		) );
		
		$this->add ( array (
			'name' => 'port',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array (
				'type' => 'number' 
			),
			'options' => array (
				'label' => 'Port'
			) 
		) );
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Adicionar',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}