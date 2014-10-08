<?php
namespace Application\Model;

class RowGateway extends \Zend\Db\RowGateway\RowGateway
{
    public function __get($name)
    {
    	if (array_key_exists($name, $this->data)) {
    		return $this->data[$name];
    	} else {
    	    return null;
    		//throw new \InvalidArgumentException('Not a valid column in this row: ' . $name);
    	}
    }
    
    public function setData(array $data)
    {
        foreach ($data as $offset => $value)
            $this->offsetSet($offset, $value);
        
        
    	return $this;
    }
}