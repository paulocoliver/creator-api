<?php
namespace Application\Service;

use Zend\Db\Sql\Select;

class RecursoEntity extends Entity
{
	public function __construct() {
		$this->repository = 'Recurso';
	}
	
	public function processRecursos() {
		
		$dbAdapter = $this->getServiceManager()->get('DbAdapter');
		
		echo '<pre>';
		print_r($dbAdapter);
		echo '</pre>';
		exit;
		
	}
	
	
	public function getAdapterByConexao($id_conexao) {
		
	
		$conexaoTable = new Model\ConexaoTable();
		$conexao = $conexaoTable->select(array('id' => $id_conexao))->current();
		if (!empty($conexao)) {
	
			$driver = $this->getDriver($conexao->db);
			$this->adapter_externo = new \Zend\Db\Adapter\Adapter(array(
					'driver'   => $driver,
					'hostname' => $conexao->host,
					'database' => $conexao->database,
					'username' => $conexao->username,
					'password' => $conexao->password,
					'options' => array(
							'buffer_results' => true,
					),
			));
		}
	
	}
	
	/**
	 * Retorna um array com todos os comentários de um post
	 *
	 * @param int $post_id  Id do post
	 * @return array
	 */
	/*public function getComments($post_id)
	{
		$posts = $this->getTable('Skel\Model\Post')->get($post_id)->toArray();
		//verifica se existem comments
		$posts['comments'] = $this->getTable('Skel\Model\Comment')
		->fetchAll(null, "post_id = $post_id")
		->toArray();
		return $posts;
	}*/
}