<?php
namespace Application\Model;
use Exception;

class ApiTable extends AbstractTable { 
    protected $table = "api";
    //protected $table = "api";
    
    /*public function addUser($fields) {
    	try {
	    	$this->insert($fields);
	    	return $this->getLastInsertValue();
    	} catch (Exception $e) {
    		return false;
    	}
    }*/
    
    public function checkDados($dados) {
        $dados['status'] = $dados['status'] == 'ATIVO' ? 'ATIVO' : 'INATIVO';
    	return $dados;
    }
    
}
