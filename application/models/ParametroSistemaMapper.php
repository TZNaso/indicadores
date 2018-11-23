<?php

class Application_Model_ParametroSistemaMapper
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
            $this->setDbTable('Application_Model_ParametroSistemaSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_ParametroSistema();
            $entry->setNuParametroSistema($row->nu_parametro_sistema);
            $entry->setNoChaveParametro($row->no_chave_parametro);
            $entry->setNoValorParametro($row->no_valor_parametro);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_ParametroSistema $objParametroSistema)
    {
        $data = array(
            'nu_parametro_sistema' => $objParametroSistema->getNuParametroSistema(),
            'no_chave_parametro' => $objParametroSistema->getNoChaveParametro(),
            'no_valor_parametro' => $objParametroSistema->getNoValorParametro()
        );

        if ($data['nu_parametro_sistema']) {
            $this->getDbTable()->update($data, 'nu_parametro_sistema = ' . $data['nu_parametro_sistema']);
        } else {
            $this->getDbTable()->insert($data);
        }
    }
}

class Application_Model_ParametroSistemaSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "parametro_sistema";

    protected $_primary = "nu_parametro_sistema";
}
