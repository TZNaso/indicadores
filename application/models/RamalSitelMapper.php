<?php

class Application_Model_RamalSitelMapper
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
            $this->setDbTable('Application_Model_RamalSitelSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Ligacoes();
            $entry->setRamal($row->ramal);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_RamalSitel $objLigacoes)
    {
        $data = array(
            'ramal' => $objLigacoes->getRamal()
        );
        // Limpando registros em branco
        foreach ($data as $key => $value) {
            if ($value == "") {
                unset($data[$key]);
            }
        }
        try {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $select = "INSERT INTO painel.ramal_sitel
                        VALUES (DEFAULT, '{$data['ramal']}')
                        ;";
            $db->fetchAll($select);
        } catch (Zend_Db_Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getIDByRamal($ramal, $ddd = true)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = '';
        if ($ddd) {
            $select = "SELECT id
                FROM painel.ramal_sitel
                WHERE ramal = '{$ramal}';
              ";
        } else {
            $select = "SELECT id
                        FROM painel.ramal_sitel
                        WHERE SUBSTRING(ramal, 6, length(ramal)) = '{$ramal}';
                      ";
        }
        $rs = $db->fetchAll($select);
        return $rs[0]['id'];
    }
    
    public function getRamalByID($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT ramal
                    FROM painel.ramal_sitel
                    WHERE id = $id;
                  ";
        $rs = $db->fetchAll($select);
        return $rs['ramal'];
    }
}

class Application_Model_RamalSitelSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "ramal_sitel";

    protected $_primary = 'id';
}
