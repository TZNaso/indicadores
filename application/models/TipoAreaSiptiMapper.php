<?php

class Application_Model_TipoAreaSiptiMapper
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
            $this->setDbTable('Application_Model_TipoAreaSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_TipoArea();
            $entry->setNuTipoArea($row->nu_tipo_area);
            $entry->setDeTipoArea($row->de_tipo_area);
            $entry->icAtivo($row->ic_ativo);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb008_tipo_area";

        return $db->fetchAll($select);
    }

    public function save(Application_Model_TipoAreaSipti $objTipoArea)
    {
        $data = array(
            'nu_tipo_area' => $objTipoArea->getNuTipoArea(),
            'de_tipo_area' => $objTipoArea->getDeTipoArea()
        );

        if ($objTipoArea->getIcAtivo()) {
            $data['ic_ativo'] = 1;
        } else {
            $data['ic_ativo'] = 0;
        }

        $this->getDbTable()->insert($data);
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.tipo_area";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.tipo_area
            (SELECT * FROM ptism001.gprtb008_tipo_area);";

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

class Application_Model_TipoAreaSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "tipo_area";

    protected $_primary = "nu_tipo_area";
}
