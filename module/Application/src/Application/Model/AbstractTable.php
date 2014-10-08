<?php
namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Feature;

abstract class AbstractTable extends AbstractTableGateway {

	public function __construct() {
		
		$this->featureSet = new Feature\FeatureSet();
		$this->featureSet->addFeature(new Feature\GlobalAdapterFeature());
		$this->featureSet->addFeature(new Feature\MetadataFeature());
		$this->featureSet->addFeature(new Feature\RowGatewayFeature(null, '\Application\Model\RowGateway'));
		
		
		$this->initialize();
	}
	
	/**
	 * 
	 * @return \Application\Model\RowGateway
	 */
	public function fetchNew() {
	    return $this->getResultSetPrototype()->getArrayObjectPrototype();
	}
	
	
	public function save(array $set) {
		
		if (key_exists('submit', $set))
			unset($set['submit']);
		
		$set = $this->checkDados($this->preparaDadosSet($set));
		
		if (!empty($set['id'])) {
			return $this->update($set, 'id = '.$set['id']);
		} else {
			return $this->insert($set);
		} 
		
	}
	
	private function preparaDadosSet($set) {
		$new_set = array();
		foreach ($set as $key => $value) {
			if (is_array($value))
				$new_set = array_merge($new_set, $this->preparaDadosSet($value));
			else
				$new_set[$key] = $value;
		}
		return $new_set;
	}
	
	public function checkDados($dados) {
		return $dados;
	}

}
