<?php
namespace Application\Form\Validator;

use Zend\Form\Form;
use Zend\Form\Element\Radio;

class InArray extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_inArray';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        
        $this->add(array( 
            'name' => 'haystack[]', 
            'type' => 'text', 
            'attributes' => array( 
            	'value' => ''
            ), 
            'options' => array( 
            	'label' => 'Deep',
            ), 
        ));
        $this->add(array( 
            'name' => 'haystack[]', 
            'type' => 'text', 
            'attributes' => array( 
            	'value' => ''
            ), 
            'options' => array( 
            ), 
        ));
        
        
    }
}