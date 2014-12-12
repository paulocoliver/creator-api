<?php
namespace Application\Form;

use Zend\Form\Form;

class Api extends Form
{
    public function __construct()
    {
        parent::__construct('form_api');
        $this->setAttribute('id', 'form_api');
        $this->setAttribute('method', 'post');
        //$this->setAttribute('action', '/admin/index/save');
        
        $this->add(array( 
            'name' => 'url', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'URL',
            	'add-on-append' => '.criadorapi.com',
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'titulo', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Titulo', 
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'descricao', 
            'type' => 'Zend\Form\Element\Textarea', 
            'attributes' => array(
            	'rows'=>'4'
            ), 
            'options' => array(
            	'label' => 'Descrição',
            	'help-block' => 'Escreva uma breve descrição sobre a API, aparecerá na documentação da API.'
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'cache', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'type' => 'number',
            	'step' => '3600',
            	'min' => '0'
            ), 
            'options' => array(
            	'label' => 'Cache',
            	'help-block' => 'Tempo em segundos'
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'status', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => 'status_switch',
            	'checked' => 'checked'
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('status')->setCheckedValue('ATIVO')->setUncheckedValue('INATIVO');

        $this->add(array( 
            'name' => 'acesso', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => 'acesso_switch',
            	'checked' => 'checked'
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('acesso')->setCheckedValue('PUBLICO')->setUncheckedValue('PRIVADO');
        
        
        
        /*$this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Adicionar',
                'class' => 'btn btn-primary',
            ),
        ));*/
    }
}