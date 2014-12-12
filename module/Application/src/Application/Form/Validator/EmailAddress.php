<?php
namespace Application\Form\Validator;

use Zend\Form\Form;
use Zend\Form\Element\Radio;

class EmailAddress extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_emailAddress';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        
        $this->add(array( 
            'name' => 'deep', 
            'type' => 'radio', 
            'attributes' => array( 
            	'value' => 0
            ), 
            'options' => array( 
            	'label' => 'Deep',
            	'help-block' => 'Defines if the servers MX records should be verified by a deep check. When this option is set to TRUE then additionally to MX records also the A, A6 and AAAA records are used to verify if the server accepts emails. This option defaults to FALSE.',
            	'inline' => false,
            	'value_options' => array(
            		0 => 'FALSE',
            		1 => 'TRUE'
            	)
            ), 
        ));
        
        $this->add(array( 
            'name' => 'domain', 
            'type' => 'radio', 
            'attributes' => array( 
            	'value' => 0
            ),
            'options' => array( 
            	'label' => 'Domain',
            	'help-block' => 'Defines if the domain part should be checked. When this option is set to FALSE, then only the local part of the email address will be checked. In this case the hostname validator will not be called. This option defaults to TRUE.',
            	'inline' => false,
            	'value_options' => array(
            		0 => 'FALSE',
            		1 => 'TRUE'
            	)
            ), 
        ))->setValue(0);
        
    }
}