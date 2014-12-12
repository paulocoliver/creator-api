<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade ColunaValidador
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="`column_validator`")
 */
class ColunaValidador extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Coluna", inversedBy="ColunaValidadorCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_column", referencedColumnName="id")
     * @var Recurso
     */
    protected $coluna;
    
    /**
     * @ORM\ManyToOne(targetEntity="Validador", inversedBy="ColunaValidadorCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_validator", referencedColumnName="id")
     * @var Recurso
     */
    protected $validador;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $params;

	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getColuna() {
		return $this->coluna;
	}

	public function setColuna($coluna) {
		$this->coluna = $coluna;
	}

	public function getValidador() {
		return $this->validador;
	}

	public function setValidador($validador) {
		$this->validador = $validador;
	}

	public function getParams() {
		return $this->params;
	}

	public function setParams($params) {
		$this->params = $params;
	}

	/**
     * Configura os filtros dos campos da entidade
     *
     * @return Zend\InputFilter\InputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}