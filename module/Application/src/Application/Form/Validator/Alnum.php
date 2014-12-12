<?php
namespace Application\Form\Validator;

use Zend\Form\Form;

class Alnum extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_alnum';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
 
        $this->add(array( 
            'name' => 'allowWhiteSpace', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
            ), 
            'options' => array( 
            	'use_hidden_element' => true,
            	'label' => 'allowWhiteSpace',
            	'help-block' => 'If whitespace characters are allowed.'
            ), 
        ));
        $this->get('allowWhiteSpace')->setCheckedValue('TRUE')->setUncheckedValue('FALSE');
    }
}