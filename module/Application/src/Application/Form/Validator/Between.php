<?php
namespace Application\Form\Validator;

use Zend\Form\Form;

class Between extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_between';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
 
        
        $this->add(array(
        	'name' => 'inclusive',
        	'type' => 'Radio',
        	'attributes' => array(
      			'required' => 'required',
        		'value' => '0',
        	),
        	'options' => array(
        		'label' => 'Inclusive',
        		'value_options' => array(
        			0 => 'Não',
        			1 => 'Sim',
        		),
        		'help-block' => ' Defines if the validation is inclusive the minimum and maximum border values or exclusive.'
        	),
        ));
        
        $this->add(array(
        	'name' => 'min',
        	'type' => 'Number',
        	'attributes' => array(
        		'required' => 'required',
       			'step' => '1',
        		'min' => '0'
        	),
        	'options' => array(
        		'label' => 'Min',
        		'help-block' => 'Sets the minimum border for the validation.'
        	),
        ));

        $this->add(array(
        	'name' => 'max',
        	'type' => 'Number',
        	'attributes' => array(
        		'required' => 'required',
       			'step' => '1',
        		'min' => '0'
        	),
        	'options' => array(
        		'label' => 'Max',
        		'help-block' => 'Sets the maximum border for the validation.'
        	),
        ));
    }
}