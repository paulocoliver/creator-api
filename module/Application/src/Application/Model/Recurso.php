<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade Recurso
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="resource")
 */
class Recurso extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Api", inversedBy="recursoCollection", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_api", referencedColumnName="id")
     * @var Api
     */
    protected $api;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $db_table;

    /**
     * @ORM\Column(type="string")
     */
    protected $resource;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $descricao;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $disponivel;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $_get;

    /**
     * @ORM\Column(type="string")
     */
    protected $_post;

    /**
     * @ORM\Column(type="string")
     */
    protected $_put;

    /**
     * @ORM\Column(type="string")
     */
    protected $_delete;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cache;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="string")
     */
    protected $tipo;
    
    /**
     * @ORM\OneToMany(targetEntity="Coluna", mappedBy="recurso", fetch="LAZY")
     *
     * @var Doctrine\Common\Collections\Collection
     */
    protected $colunaCollection;
    
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

	public function getDb_table() {
		return $this->db_table;
	}

	public function setDb_table($db_table) {
		$this->db_table = $db_table;
	}

	public function getResource() {
		return $this->resource;
	}

	public function setResource($resource) {
		$this->resource = $resource;
	}

	public function getDescricao() {
		return $this->descricao;
	}

	public function setDescricao($descricao) {
		$this->descricao = $descricao;
	}

	public function getDisponivel() {
		return $this->disponivel;
	}

	public function setDisponivel($disponivel) {
		$this->disponivel = $disponivel;
	}

	public function getGet() {
		return $this->_get;
	}

	public function setGet($_get) {
		$this->_get = $_get;
	}

	public function getPost() {
		return $this->_post;
	}

	public function setPost($_post) {
		$this->_post = $_post;
	}

	public function getPut() {
		return $this->_put;
	}

	public function setPut($_put) {
		$this->_put = $_put;
	}

	public function getDelete() {
		return $this->_delete;
	}

	public function setDelete($_delete) {
		$this->_delete = $_delete;
	}

	public function getCache() {
		return $this->cache;
	}

	public function setCache($cache) {
		$this->cache = $cache;
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getColunaCollection() {
		return $this->colunaCollection;
	}

	public function setColunaCollection($colunaCollection) {
		$this->colunaCollection = $colunaCollection;
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