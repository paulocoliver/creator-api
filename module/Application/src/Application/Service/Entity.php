<?php
namespace Application\Service;

use Doctrine\ORM\EntityManager;

class Entity extends AbstractService 
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $repository;
	
	private static $_repositorys = array(
		'Api' 			=> 'Application\Model\Api',
		'Usuario' 		=> 'Application\Model\Usuario',
		'UsuarioApi' 	=> 'Application\Model\UsuarioApi',
		'Conexao' 		=> 'Application\Model\Conexao',
		'Recurso' 		=> 'Application\Model\Recurso',
		'Coluna' 		=> 'Application\Model\Coluna',
		'Constraint'	=> 'Application\Model\Constraint',
		'Validador'		=> 'Application\Model\Validador',
		'ColunaValidador' => 'Application\Model\ColunaValidador',
	);
	
	public function setEntityManager(EntityManager $em)
	{
		$this->em = $em;
	}
	
	public function getEntityManager()
	{
		if (null === $this->em) {
			$this->em = $this->getService('Doctrine\ORM\EntityManager');
		}
		return $this->em;
	}
	
	public static function getRepositorys()
	{
		return self::$_repositorys;
	}
	
	public function getRepository($repository=null)
	{
		if (empty($repository))
			$repository =  $this->repository;
			
		if (key_exists($repository, self::$_repositorys))
			$repository = self::$_repositorys[$repository];
	
		return $this->getEntityManager()->getRepository($repository);
	}

	public function persist($entity) 
	{
		$this->getEntityManager()->persist($entity);
	    $this->getEntityManager()->flush();
	}

	public function remove($entity) 
	{
		$this->getEntityManager()->remove($entity);
		$this->getEntityManager()->flush();
	}
	
}