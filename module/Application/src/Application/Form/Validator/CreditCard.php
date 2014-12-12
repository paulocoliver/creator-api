<?php
namespace Application\Form\Validator;

use Zend\Form\Form;
use Zend\Validator\CreditCard as ValidatorCreditCard;

class CreditCard extends Form
{
    public function __construct()
    {
    	$name = 'form_validator_creditCard';
        parent::__construct($name);
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        
        $this->add(array(
        	'name' => 'Type',
        	'type' => 'MultiCheckbox',
        	'attributes' => array(
      			'required' => 'false',
        		'value' => '0',
        	),
        	'options' => array(
        		'label' => 'Type',
        		'value_options' => array(
        			array(
        				'label' => 'AMERICAN EXPRESS', 'value' => ValidatorCreditCard::AMERICAN_EXPRESS
        			),
        			array(
        				'label' => 'UNIONPAY', 'value' => ValidatorCreditCard::UNIONPAY
        			),
        			array(
        				'label' => 'DINERS CLUB', 'value' => ValidatorCreditCard::DINERS_CLUB
        			),
        			array(
        				'label' => 'DINERS CLUB US', 'value' => ValidatorCreditCard::DINERS_CLUB_US
        			),
        			array(
        				'label' => 'DISCOVER', 'value' => ValidatorCreditCard::DISCOVER
        			),
        			array(
        				'label' => 'JCB', 'value' => ValidatorCreditCard::JCB
        			),
        			array(
        				'label' => 'LASER', 'value' => ValidatorCreditCard::LASER
        			),
        			array(
        				'label' => 'MAESTRO', 'value' => ValidatorCreditCard::MAESTRO
        			),
        			array(
        				'label' => 'MASTERCARD', 'value' => ValidatorCreditCard::MASTERCARD
        			),
        			array(
        				'label' => 'SOLO', 'value' => ValidatorCreditCard::SOLO
        			),
        			array(
        				'label' => 'VISA', 'value' => ValidatorCreditCard::VISA
        			),
        		),
        		'inline' => false,
        		'help-block' => 'The type of credit card which will be validated'
        	),
        ));
        
    }
}