<?php
namespace Application\Form;

use Zend\Form\Form;

class Usuario extends Form
{
    public function __construct()
    {
        parent::__construct('form-user-info');
        $this->setAttribute('id', 'form-user-info');
        $this->setAttribute('method', 'post');
        //$this->setAttribute('action', '/admin/index/save');
        
        $this->add(array( 
            'name' => 'nome', 
            'type' => 'Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Nome',
            ), 
        )); 
 
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Email', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'E-mail', 
            ), 
        )); 

        $this->add(array( 
            'name' => 'username', 
            'type' => 'Text', 
            'attributes' => array( 
                'required' => 'required', 
            ), 
            'options' => array( 
                'label' => 'Username', 
            ), 
        )); 

        $this->add(array( 
            'name' => 'senha', 
            'type' => 'Password', 
            'attributes' => array( 
            ), 
            'options' => array( 
                'label' => 'Password',
            	'help-block' => '*Deixe em branco se não quiser alterar a senha'
            ), 
        )); 

 
    }
}