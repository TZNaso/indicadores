<?php

class Application_Model_FuncaoSiptiMapper
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
            $this->setDbTable('Application_Model_FuncaoSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function fetchByNuFuncao($nuFuncao)
    {
        $resultSet = $this->getDbTable()->fetchAll("nu_funcao = '{$nuFuncao}'");

        $entry = new Application_Model_FuncaoSipti();

        foreach ($resultSet as $row) {

            $entry = new Application_Model_FuncaoSipti();
            $entry->setNuFuncao($row->nu_funcao);
            $entry->setDeFuncao($row->de_funcao);
            $entry->setNuTipoFuncao($row->nu_tipo_funcao);
        }

        return $entry;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_FuncaoSipti();
            $entry->setNuFuncao($row->nu_funcao);
            $entry->setDeFuncao($row->de_funcao);
            $entry->setNuTipoFuncao($row->nu_tipo_funcao);
            $entry->setIcFuncaoAtiva($row->ic_funcao_ativa);
            $entry->setStcoFncoCxa($row->stco_fnco_cxa);
            $entry->setCargaHoraria($row->carga_horaria);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb009_funcao";

        return $db->fetchAll($select);
    }

    public function save(Application_Model_FuncaoSipti $objFuncao)
    {
        $data = array(
            'nu_funcao' => $objFuncao->getNuFuncao(),
            'de_funcao' => $objFuncao->getDeFuncao(),
            'nu_tipo_funcao' => $objFuncao->getNuTipoFuncao(),
            'stco_fnco_cxa' => $objFuncao->getStcoFncoCxa(),
            'carga_horaria' => $objFuncao->getCargaHoraria()
        );

        if ($objFuncao->getIcFuncaoAtiva()) {
            $data['ic_funcao_ativa'] = 1;
        } else {
            $data['ic_funcao_ativa'] = 0;
        }

        $this->getDbTable()->insert($data);
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.funcao_sipti";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.funcao_sipti
            (SELECT * FROM ptism001.gprtb009_funcao);";

        try {

            $query = $this->getDbTable()
                ->getAdapter()
                ->query($sql);
        } catch (Zend_Db_Exception $e) {
            echo "<pre>";
            print_r($e->getMessage());
            echo "<br/>";
            die(__FILE__ . " - " . __LINE__);
        }
    }
}

class Application_Model_FuncaoSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "funcao_sipti";

    protected $_primary = "nu_funcao";
}
