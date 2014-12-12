<?php
namespace Application\Form\Validator;

use Zend\Form\Form;

class Date extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_date';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        
        $this->add(array(
        	'name' => 'format',
        	'type' => 'Text',
        	'attributes' => array(
        		'required' => 'required',
        		'value' => '',
        		'placeHolder' => 'Y-m-d H:i:s'
        	),
        	'options' => array(
        		'label' => 'Format',
        		'help-block' => 'This option accepts format as specified in the standard PHP function http://php.net/manual/en/function.date.php'
        	),
        ));
        
    }
}