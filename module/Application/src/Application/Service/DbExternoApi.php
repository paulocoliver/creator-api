<?php
namespace Application\Service;

use Application\Model;

class DbExternoApi extends Entity
{
	/**
	 * @var Conexao
	 */
	private $_conexao;

	private $_adapter;
	
	private $_drivers = array(
		'MySQL'         => 'Mysqli',
		'Oracle'        => 'Pdo_Pgsql',
		'SQLServer'     => 'Sqlsrv',
		'PostgreSQL'    => 'Pgsql',
		'SQLite'        => 'Pdo_Sqlite'
	);
	
	public function setConexao(Model\Conexao $conexao) {
		$this->_conexao = $conexao;
	}
	
	public function getConexao() {
		return $this->_conexao;
	}
	
	/**
	 * @throws \Exception
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public function getAdapter() {
		
		if (empty($this->_adapter)) {
			if (empty($this->_conexao))
				throw new \Exception('Conexao não informada');
			
			$db = $this->_conexao->getDb();
			if (!key_exists($db, $this->_drivers))	
				throw new \Exception('Driver invalido');
		
			$adapter = new \Zend\Db\Adapter\Adapter(array(
				'driver'   => $this->_drivers[$db],
				'hostname' => $this->_conexao->getHost(),
				'database' => $this->_conexao->getData_base(),
				'username' => $this->_conexao->getUsername(),
				'password' => $this->_conexao->getPassword(),
				'options' => array(
					'buffer_results' => true,
				),
				'charset' => 'UTF8',
				 'driver_options' => array(
			    		1002 => 'SET NAMES \'UTF8\''
			    ),
			));
			
			try {
				if (!@$adapter->getDriver()->getConnection()->connect()->isConnected())
					throw new \Exception();
			} catch (\Exception $e) {
				throw new \Exception('error_connecting');
			}
			$this->_adapter = $adapter;
		}
		
		return $this->_adapter;
	}
	
	/**
	 * @throws \Exception
	 * @return \Zend\Db\Metadata\Metadata
	 */
	public function getMetadata() {
		return new \Zend\Db\Metadata\Metadata($this->getAdapter());
	}
	
	public function sincronizarRecursos(Model\Api $api) {
		if (empty($api->getId()))
			throw new \Exception('ID Api invalido');
		
		$conn = $this->getEntityManager()->getConnection();
		$conn->beginTransaction();
		try {
			$repositoryRecurso = $this->getRepository('Recurso');
			$repositoryColuna  = $this->getRepository('Coluna');
			
			
			$tables_info = array();
			$tables_views = array_merge($this->getMetadata()->getTables(), $this->getMetadata()->getViews());
			
			foreach ($tables_views as $table_view) {
				//$table_view = new \Zend\Db\Metadata\Object\TableObject();
				$tableName = $table_view->getName();

				$tipo =  $table_view instanceof \Zend\Db\Metadata\Object\ViewObject ? 'VIEW' : 'TABLE';
				
				$row_recurso = $this->getRepository('Recurso')->findOneBy(array('db_table' => $tableName, 'api' => $api));
				if (empty($row_recurso)) {
					$resource = $tableName;
					$existeRecurso = $this->getRepository('Recurso')->findOneBy(array('resource' => $tableName, 'api' => $api));
					if (!empty($existeRecurso))
						$resource .= '_'.rand(1000, 99999);
					
					$row_recurso = new Model\Recurso();
					$row_recurso->setApi($api);
					$row_recurso->setDb_table($tableName);
					$row_recurso->setResource($resource);
					$row_recurso->setGet('ATIVO');
					$action_status = $tipo == 'VIEW' ? 'INATIVO' : 'ATIVO';
					$row_recurso->setPost($action_status);
					$row_recurso->setPut($action_status);
					$row_recurso->setDelete($action_status);
					$row_recurso->setDisponivel('SIM');
				}
				$row_recurso->setTipo($tipo);
				$row_recurso->setStatus('VALIDO');
				$this->persist($row_recurso);
				$tables_info[$tableName]['id'] = $row_recurso->getId();
				$recursos_validos[] = $row_recurso->getId();
				
				$conn->update('`column`', array('status' => 'INVALIDO'), array('id_resource' => $row_recurso->getId()));
				foreach ($table_view->getColumns() as $columnName) {
					$row_coluna = $repositoryColuna->findOneBy(array('nome' => $columnName->getName(), 'recurso' => $row_recurso));
					if (empty($row_coluna)) {
						$row_coluna = new Model\Coluna();
						$row_coluna->setRecurso($row_recurso);
						$row_coluna->setNome($columnName->getName());
						$row_coluna->setInsert($tipo == 'VIEW' ? 'NAO' : 'SIM');
						$row_coluna->setSelect('SIM');
						$row_coluna->setTipo($columnName->getDataType());
					}
					$row_coluna->setStatus('VALIDO');
					$this->persist($row_coluna);
					$tables_info[$tableName]['column'][$columnName->getName()] = $row_coluna->getId();
				}
	
				$tables_info[$tableName]['constraint'] = $table_view->getConstraints();
				
			}
			
			$qb = $repositoryRecurso->createQueryBuilder('r');
			$qb->update()
				->set('r.status', $qb->expr()->literal('INVALIDO'))
				->where('r.api = :api')
				->andWhere('r.id NOT IN (:ids)')
				->setParameters(array('ids' => $recursos_validos, 'api' => $api))
				->getQuery()->execute();
			
			$repositoryConstraint  = $this->getRepository('Constraint');
			if (!empty($tables_info)) {
				
				$conn->delete('`constraint`', array('id_api' => $api->getId()));
				
				foreach ($tables_info as $table => $info) {
					
					foreach ($info['constraint'] as $constraint) {
						
						//$constraint = new \Zend\Db\Metadata\Object\ConstraintObject();
						if (!$constraint->isPrimaryKey() && !$constraint->isForeignKey())
							continue;
						
						$row_constraint = new Model\Constraint();
						$row_constraint->setApi($api);
						$row_constraint->setNome($constraint->getName());
						$row_constraint->setTipo($constraint->isPrimaryKey() ? 'PRIMARY_KEY' : 'FOREIGN_KEY');
						$this->persist($row_constraint);
						
						$key_columns 	 = $constraint->getColumns();
						$key_ref_columns = $constraint->getReferencedColumns();
						$key_ref_table   = $constraint->getReferencedTableName();
						
						foreach ($key_columns as $key => $column) {
							$id_column_reference = null;
							if ($constraint->isForeignKey())
								$id_column_reference = $tables_info[$key_ref_table]['column'][$key_ref_columns[$key]];
							$conn->insert(
								'`column_constraint`',
								array(
									'id_constraint' 		=> $row_constraint->getId(),
									'id_column' 			=> $info['column'][$column],
									'id_column_reference' 	=> $id_column_reference
								)
							);
						}
						
					}
				}
			}
			
			$conn->commit();
		
		} catch (\Exception $e) {
			$conn->rollBack();
			
			throw new \Exception($e->getMessage());
		}
	}
	
}