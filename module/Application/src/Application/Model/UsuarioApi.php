<?php
namespace Application\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
//use Core\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entidade UsuarioApi
 * 
 * @category Application
 * @package Model
 *
 * @ORM\Entity
 * @ORM\Table(name="usuario_acesso_api")
 */
class UsuarioApi  extends EntityAbstract
{
    /**
     * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="apiCollection", cascade={"persist", "merge", "refresh"})
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id")
	 * 
	 * @var Usuario
	 */
	protected $usuario;

	/**
     * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Api", inversedBy="usuarioCollection", cascade={"persist", "merge", "refresh"})
	 * @ORM\JoinColumn(name="id_api", referencedColumnName="id")
	 * 
	 * @var Api
	 */
	protected $api;
    
	/**
	 * @ORM\Column(type="string")
	 */
	protected $permissao;
	
	
	
    public function getUsuario() {
		return $this->usuario;
	}

	public function setUsuario($usuario) {
		$this->usuario = $usuario;
	}

	public function getApi() {
		return $this->api;
	}

	public function setApi($api) {
		$this->api = $api;
	}

	public function getPermissao() {
		return $this->permissao;
	}

	public function setPermissao($permissao) {
		$this->permissao = $permissao;
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