<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade Constraint
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="`constraint`")
 */
class Constraint extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Api", inversedBy="constraintCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_api", referencedColumnName="id")
     * @var Api
     */
    protected $api;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $nome;

    /**
     * @ORM\Column(type="string")
     */
    protected $tipo;
    
    /**
     * @ORM\OneToMany(targetEntity="ColunaConstraint", mappedBy="constraint", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $constraintCollection;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getApi() {
		return $this->api;
	}

	public function setApi($api) {
		$this->api = $api;
	}

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getConstraintCollection() {
		return $this->constraintCollection;
	}

	public function setConstraintCollection($constraintCollection) {
		$this->constraintCollection = $constraintCollection;
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