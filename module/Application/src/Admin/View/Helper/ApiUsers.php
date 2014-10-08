<?php
namespace Admin\View\Helper;
 
use Application\View\Helper\AbstractHelper;
 
class ApiUsers extends AbstractHelper
{
    public function __invoke($id_api, $params=array())
    {
        $sql = "SELECT usuario_acesso_api.*, usuario.nome AS nome, usuario.email AS email, usuario.username AS username
        		FROM usuario_acesso_api
        		INNER JOIN usuario ON usuario.id = usuario_acesso_api.id_usuario
        		WHERE id_api = :id_api";
        return $this->getService('Doctrine\ORM\EntityManager')->getConnection()->fetchAll($sql, array('id_api' => $id_api));
    }
}