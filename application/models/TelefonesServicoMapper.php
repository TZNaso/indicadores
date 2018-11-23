<?php

class Application_Model_TelefonesServicoMapper
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
            $this->setDbTable('Application_Model_TelefonesServicoSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_TelefonesServico();
            $entry->setRamal($row->ramal)->setNumeroServico($row->numero_servico);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_TelefonesServico $objTelefonesServico)
    {
        $data = array(
            'ramal' => $objTelefonesServico->getRamal(),
            'numero_servico' => $objTelefonesServico->getNumeroServico()
        );

        // Limpando registros em branco
        foreach ($data as $key => $value) {
            if ($value == "") {
                unset($data[$key]);
            }
        }
        try {
            $this->getDbTable()->insert($data);
        } catch (Zend_Db_Exception $e) {
            error_log($e->getMessage());
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getNumerosServico($ramal)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT 
                    telefones_servico.numero_servico,
                    telefones_servico.id
                  FROM 
                    painel.telefones_servico
                  WHERE 
                    telefones_servico.ramal = '{$ramal}';
                  ";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function deleteNumeroServico($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "DELETE FROM
                    painel.telefones_servico
                  WHERE
                    id = {$id}
                  ";
        $rs;

        try {
            $rs = $db->fetchAll($select);
        } catch (Zend_Db_Exception $e) {
            error_log($e->getMessage());
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return $rs;
    }
}

class Application_Model_TelefonesServicoSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "telefones_servico";

    protected $_primary = 'id';
}
