<?php

class Application_Model_HistRamalSitelMapper
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
            $this->setDbTable('Application_Model_HistRamalSitelSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Ligacoes();
            $entry->setMatricula($row->matricula)
                ->setNome($row->nome)
                ->setData_inicio($row->data_inicio)
                ->setData_fim($row->data_fim)
                ->setRamal_sitel_id($row->ramal_sitel_id);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_HistRamalSitel $objLigacoes)
    {
        $data = array(
            'matricula' => $objLigacoes->getMatricula(),
            'nome' => $objLigacoes->getNome(),
            'data_inicio' => $objLigacoes->getData_inicio(),
            'data_fim' => $objLigacoes->getData_fim(),
            'ramal_sitel_id' => $objLigacoes->getRamal_sitel_id()
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
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}

class Application_Model_HistRamalSitelSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "hist_ramal_sitel";

    protected $_primary = 'id';
}
