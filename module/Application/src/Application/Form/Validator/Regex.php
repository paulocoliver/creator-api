<?php
namespace Application\Form\Validator;

use Zend\Form\Form;

class Regex extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_regex';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'pattern', 
            'type' => 'text', 
            'attributes' => array(
            	'required' => 'required',
            	'value' => '',
            	'placeHolder' => ' /^Test$/ '
            ), 
            'options' => array( 
            	'label' => 'Pattern',
            ) 
        ));
    }
    
    
}