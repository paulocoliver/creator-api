<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade Coluna
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="`column`")
 */
class Coluna extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Recurso", inversedBy="colunaCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_resource", referencedColumnName="id")
     * @var Recurso
     */
    protected $recurso;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $nome;

    /**
     * @ORM\Column(type="string")
     */
    protected $_select;

    /**
     * @ORM\Column(type="string")
     */
    protected $_insert;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $tipo;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;
    
    /**
     * @ORM\OneToMany(targetEntity="ColunaConstraint", mappedBy="column", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $colunaConstraintCollection;
    
    /**
     * @ORM\OneToMany(targetEntity="ColunaConstraint", mappedBy="column_reference", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $colunaConstraintReferenceCollection;
    
    /**
     * @ORM\OneToMany(targetEntity="ColunaValidador", mappedBy="coluna", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $colunaValidadorCollection;
    
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getRecurso() {
		return $this->recurso;
	}

	public function setRecurso($recurso) {
		$this->recurso = $recurso;
	}

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function getSelect() {
		return $this->_select;
	}

	public function setSelect($select) {
		$this->_select = $select;
	}

	public function getInsert() {
		return $this->_insert;
	}

	public function setInsert($insert) {
		$this->_insert = $insert;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	public function getColunaConstraintCollection() {
		return $this->colunaConstraintCollection;
	}

	public function setColunaConstraintCollection($colunaConstraintCollection) {
		$this->colunaConstraintCollection = $colunaConstraintCollection;
	}

	public function getColunaConstraintReferenceCollection() {
		return $this->colunaConstraintReferenceCollection;
	}

	public function setColunaConstraintReferenceCollection($colunaConstraintReferenceCollection) {
		$this->colunaConstraintReferenceCollection = $colunaConstraintReferenceCollection;
	}

	public function getColunaValidadorCollection() {
		return $this->colunaValidadorCollection;
	}

	public function setColunaValidadorCollection($colunaValidadorCollection) {
		$this->colunaValidadorCollection = $colunaValidadorCollection;
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