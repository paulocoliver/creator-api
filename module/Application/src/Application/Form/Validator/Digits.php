<?php
namespace Application\Form\Validator;

use Zend\Form\Form;

class Digits extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_digits';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
    }
}