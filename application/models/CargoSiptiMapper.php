<?php

class Application_Model_CargoSiptiMapper
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
            $this->setDbTable('Application_Model_CargoSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();

        $entries = array();
        foreach ($resultSet as $row) {

            $entry = new Application_Model_CargoSipti();
            $entry->setNuCargo($row->nu_cargo);
            $entry->setNuTipoCargo($row->nu_tipo_cargo);
            $entry->setNoCargo($row->no_cargo);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb010_cargo";

        return $db->fetchAll($select);
    }

    public function save(Application_Model_CargoSipti $objCargo)
    {
        $data = array(
            'nu_cargo' => $objCargo->getNuCargo(),
            'nu_tipo_cargo' => $objCargo->getNuTipoCargo(),
            'no_cargo' => $objCargo->getNoCargo()
        );

        $this->getDbTable()->insert($data);
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.cargo_sipti";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.cargo_sipti
            (SELECT * FROM ptism001.gprtb010_cargo);";

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

class Application_Model_CargoSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "cargo_sipti";

    protected $_primary = "nu_cargo";
}
