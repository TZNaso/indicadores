<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _init()
    {}

    protected function _initConstants()
    {
        Zend_Registry::set('Select', 0);
        Zend_Registry::set('Group', 1);
        Zend_Registry::set('Coord', 2);
        Zend_Registry::set('Equipe', 3);
        Zend_Registry::set('Funcionario', 4);
        $systemName = str_ireplace('/public/index.php', '', $_SERVER['PHP_SELF']);
        $rootDir = dirname(dirname(__FILE__));
        define('ROOT_DIR', $rootDir);
        define('SYSTEM_PATH', "http://{$_SERVER['HTTP_HOST']}{$systemName}");
        define('SYSTEM_NAME', 'Painel CEDESBR');
    }

    protected function _initDbs()
    {
        $registry = Zend_Registry::getInstance();
        $configDb = new Zend_Config_Ini(ROOT_DIR . "/application/configs/db.ini", 'painel');
        $db = Zend_Db::factory($configDb->db->adapter, $configDb->db->config->toArray());
        $registry->set('db', $db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }
}
