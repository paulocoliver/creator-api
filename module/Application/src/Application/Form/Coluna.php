<?php
namespace Application\Form;

use Zend\Form\Form;

class Coluna extends Form
{
    public function __construct()
    {
        parent::__construct('form_coluna');
        $this->setAttribute('id', 'form_coluna');
        $this->setAttribute('method', 'post');
        
        $this->add(array( 
            'name' => '_select', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_select_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_select')->setCheckedValue('SIM')->setUncheckedValue('NAO');

        $this->add(array( 
            'name' => '_insert', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            	'id' => '_insert_switch',
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            ), 
        ));
        $this->get('_insert')->setCheckedValue('SIM')->setUncheckedValue('NAO');
        
        
    }
}