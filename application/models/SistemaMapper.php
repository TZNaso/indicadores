<?php

class Application_Model_SistemaMapper
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
            $this->setDbTable('Application_Model_SistemaSchema');
        }
        return $this->_dbTable;
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.sistema";
        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.sistema
        (SELECT * FROM  ptism001.gprtb041_sistema);";
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

class Application_Model_SistemaSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "sistema";

    protected $_primary = "nu_sistema";
}