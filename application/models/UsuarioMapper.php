<?php

class Application_Model_UsuarioMapper
{

    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_UsuarioSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_Usuario();
            $entry->setNuUsuario($row->nu_usuario);
            $entry->setNoMatrFunc($row->no_matr_func);
            $entry->setSenha($row->senha);
            $entry->setEmail($row->email);
            $entry->setNuFuncionario($row->nu_funcionario);
            $entry->setIcAdministrador($row->ic_administrador);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchByUserId($userId)
    {
        $resultSet = $this->getDbTable()->fetchAll("no_matr_func = '{$userId}'");

        $entry = new Application_Model_Usuario();

        foreach ($resultSet as $row) {

            $entry = new Application_Model_Usuario();
            $entry->setNuUsuario($row->nu_usuario);
            $entry->setNuFuncionario($row->nu_funcionario);
        }

        return $entry;
    }

    public function save(Application_Model_Usuario $objUsuario)
    {
        $data = array(
            'nu_usuario' => $objUsuario->getNuUsuario(),
            'no_matr_func' => strtoupper($objUsuario->getNoMatrFunc()),
            'senha' => $objUsuario->getSenha(),
            'email' => $objUsuario->getEmail(),
            'ic_administrador' => $objUsuario->getIcAdministrador(),
            'nu_funcionario' => $objUsuario->getNuFuncionario()
        );

        foreach ($data as $key => $value) {
            if ($value == '') {
                unset($data[$key]);
            }
        }

        try {

            $this->getDbTable()->insert($data);
        } catch (Zend_Db_Exception $e) {

            throw new Exception($e->getMessage());
        }
    }

    public function update(Application_Model_Usuario $objUsuario)
    {
        $data = array(
            'nu_usuario' => $objUsuario->getNuUsuario(),
            'no_matr_func' => $objUsuario->getNoMatrFunc(),
            'senha' => $objUsuario->getSenha(),
            'email' => $objUsuario->getEmail(),
            'ic_administrador' => $objUsuario->getIcAdministrador(),
            'nu_funcionario' => $objUsuario->getNuFuncionario()
        );

        foreach ($data as $key => $value) {
            if ($value == '') {
                unset($data[$key]);
            }
        }

        try {

            $this->getDbTable()->update($data, "nu_usuario = '{$data['nu_usuario']}'");
        } catch (Zend_Db_Exception $e) {

            throw new Exception($e->getMessage());
        }
    }

    public function login(Application_Model_Usuario $objUsuario)
    {
        $resultSet = $this->getDbTable()->fetchAll("no_matr_func = '{$objUsuario->getNoMatrFunc()}' AND senha = '{$objUsuario->getSenha()}'");

        return $resultSet->toArray();
    }

    public function getListaCadastrados($icAdministrador = null)
    {

        // $db = Zend_Db::factory('Pdo_pgsql', array(
        // 'host' => 'localhost',
        // 'username' => 'postgres',
        // 'dbname' => 'PTIDB001',
        // 'password' => 'admin',
        // #'password' => '1gisele',
        // 'port' => 5432,
        // ));
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        if ($icAdministrador) {
            $where = "WHERE usu.ic_administrador = 1";
        } else {
            $where = "";
        }

        $select = "SELECT usu.nu_usuario, usu.no_matr_func, fun.no_funcionario, are.no_sigla_area
                    FROM painel.usuario usu
                    JOIN painel.funcionario_sipti fun ON (usu.nu_funcionario = fun.nu_funcionario)
                    JOIN painel.area_sipti are ON (fun.nu_area = are.nu_area)
                    {$where}
                    ORDER BY fun.no_funcionario";

        $rs = $db->fetchAll($select);

        return $rs;
    }
}

class Application_Model_UsuarioSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "usuario";

    protected $_primary = "nu_usuario";
}
