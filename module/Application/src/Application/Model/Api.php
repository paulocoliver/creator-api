<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
//use Core\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade Api
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="api")
 */
class Api extends EntityAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Conexao", inversedBy="api", cascade={"persist", "merge", "refresh"}, fetch="LAZY")
     * @ORM\JoinColumn(name="id_conexao", referencedColumnName="id")
     * @var Conexao
     */
    protected $conexao;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $titulo;
    
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $descricao;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $status;
    
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $acesso;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $cache;
    
    /**
     * @ORM\OneToMany(targetEntity="UsuarioApi", mappedBy="api", cascade={"all"}, orphanRemoval=true, fetch="LAZY")
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $usuarioCollection;

    /**
     * @ORM\OneToMany(targetEntity="Recurso", mappedBy="api", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $recursoCollection;

    /**
     * @ORM\OneToMany(targetEntity="Constraint", mappedBy="api", fetch="LAZY")
     * @var Doctrine\Common\Collections\Collection
     */
    protected $constraintCollection;

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

            /*$inputFilter->add($factory->createInput(array(
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 50,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'valid',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'role',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 20,
                        ),
                    ),
                ),
            )));
			*/
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}