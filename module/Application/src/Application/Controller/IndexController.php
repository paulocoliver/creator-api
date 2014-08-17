<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Metadata\Object\ConstraintObject as ConstraintObject;

class IndexController extends AbstractActionController
{
    /** @var ConstraintObject */
    private $constraint;
    
    private function getAdapter() {
        return new \Zend\Db\Adapter\Adapter(array(
        		//'driver' => 'Pdo_Sqlite',
        		//'database' => '/Users/paulo/Desktop/db_test.sqlite3'
        		//'database' => '/Users/paulo/Desktop/Sakila.sqlite3'
        		'driver' => 'Mysqli',
        		'hostname' => '187.45.189.26',
        		'database' => 'omelhor_teste_paulo',
        		'username' => 'omelhor_bairro',
        		'password' => 'w23ew23ew23e'
        ));
        
    }
    
    public function indexAction()
    {
    }
}
