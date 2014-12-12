<?php
namespace Application\Form\Validator\Db;

use Zend\Form\Form;

class RecordExists extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_db_RecordExists';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        $this->setLabel('Teste');
        
        $this->add(array(
        	'name' => 'table',
        	'type' => 'Text',
        	'attributes' => array(
        		'required' => 'required',
        		'value' => '',
        	),
        	'options' => array(
        		'label' => 'Table',
        		'help-block' => 'The table that will be searched for the record.'
        	),
        ));

        $this->add(array(
        	'name' => 'field',
        	'type' => 'Text',
        	'attributes' => array(
        		'required' => 'required',
        		'value' => '',
        	),
        	'options' => array(
        		'label' => 'Field',
        		'help-block' => 'The database field within this table that will be searched for the record.'
        	),
        ));
        
    }
}