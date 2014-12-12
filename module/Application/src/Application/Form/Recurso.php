<?php
namespace Application\Form;

use Zend\Form\Form;

class Recurso extends Form
{
    public function __construct()
    {
        parent::__construct('form_recurso');
        $this->setAttribute('id', 'form_recurso');
        $this->setAttribute('method', 'post');
        
        /*$this->add(array( 
            'name' => 'db_table', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'URL',
            	'add-on-append' => '.minhaapi.com',
            ), 
        ));*/
 
        $this->add(array( 
            'name' => 'resource', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'URI do Recurso', 
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
            	'help-block' => 'Escreva uma breve descrição sobre o recurso, aparecerá na documentação da API'
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'disponivel', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => 'disponivel_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('disponivel')->setCheckedValue('SIM')->setUncheckedValue('NAO');
        
        $this->add(array( 
            'name' => '_get', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_get_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_get')->setCheckedValue('ATIVO')->setUncheckedValue('INATIVO');

        $this->add(array( 
            'name' => '_post', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_post_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_post')->setCheckedValue('ATIVO')->setUncheckedValue('INATIVO');

        $this->add(array( 
            'name' => '_put', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_put_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_put')->setCheckedValue('ATIVO')->setUncheckedValue('INATIVO');

        $this->add(array( 
            'name' => '_delete', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_delete_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_delete')->setCheckedValue('ATIVO')->setUncheckedValue('INATIVO');
        
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