<?php

class Application_Model_HstroFuncionarioAreaSiptiMapper
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
            $this->setDbTable('Application_Model_HstroFuncionarioAreaSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_HstroFuncionarioAreaSipti();
            $entry->setNuFuncionario($row->nu_funcionario);
            $entry->setNuArea($row->nu_area);
            $entry->setDtInicio($row->dt_inicio);
            $entry->setDtFim($row->dt_fim);
            $entry->setDeResponsavel($row->de_responsavel);
            $entry->setDtAtualizacao($row->dt_atualizacao);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb007_hstro_funcionario_area";

        return $db->fetchAll($select);
    }

    public function save(Application_Model_HstroFuncionarioAreaSipti $objHstro)
    {
        $data = array(
            'nu_funcionario' => $objHstro->getNuFuncionario(),
            'nu_area' => $objHstro->getNuArea(),
            'dt_inicio' => $objHstro->getDtInicio(),
            'dt_fim' => $objHstro->getDtFim(),
            'dt_atualizacao' => $objHstro->getDtAtualizacao(),
            'de_responsavel' => $objHstro->getDeResponsavel()
        );

        foreach ($data as $key => $value) {
            if ($value == '') {
                unset($data[$key]);
            }
        }

        try {

            $this->getDbTable()->insert($data);
        } catch (Zend_Db_Exception $e) {

            echo "<pre>";
            print_r($e->getMessage());
            echo "<br/>";
            die(__FILE__ . " - " . __LINE__);
        }
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.hstro_funcionario_area_sipti";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.hstro_funcionario_area_sipti
            (SELECT * FROM ptism001.gprtb007_hstro_funcionario_area);";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
    }
}

class Application_Model_HstroFuncionarioAreaSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "hstro_funcionario_area_sipti";

    protected $_primary = array(
        "nu_area",
        "nu_funcionario"
    );
}
