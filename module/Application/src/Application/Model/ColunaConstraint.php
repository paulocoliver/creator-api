<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade ColunaConstraint
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="`column_constraint`")
 */
class ColunaConstraint extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Constraint", inversedBy="colunaConstraintCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_constraint", referencedColumnName="id")
     * @var Constraint
     */
    protected $constraint;
    
    /**
     * @ORM\ManyToOne(targetEntity="Coluna", inversedBy="colunaConstraintCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_column", referencedColumnName="id")
     * @var Coluna
     */
    protected $column;

    /**
     * @ORM\ManyToOne(targetEntity="Coluna", inversedBy="colunaConstraintReferenceCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_column_reference", referencedColumnName="id")
     * @var Coluna
     */
    protected $column_reference;


	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getConstraint() {
		return $this->constraint;
	}

	public function setConstraint($constraint) {
		$this->constraint = $constraint;
	}

	public function getColumn() {
		return $this->column;
	}

	public function setColumn($column) {
		$this->column = $column;
	}

	public function getColumn_reference() {
		return $this->column_reference;
	}

	public function setColumn_reference($column_reference) {
		$this->column_reference = $column_reference;
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