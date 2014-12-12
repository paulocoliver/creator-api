<?php
namespace Application\Form\Validator\Db;

class NoRecordExists extends RecordExists
{
    public function __construct()
    {
        parent::__construct();
    	$name = 'form_validator_db_RecordExists';
        $this->setAttribute('id', $name);
        $this->setAttribute('method', 'post');
        $this->setLabel('Teste2');
        
        
    }
}