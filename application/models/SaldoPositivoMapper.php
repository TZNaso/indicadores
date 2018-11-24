<?php

class Application_Model_SaldoPositivoMapper
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
            $this->setDbTable('Application_Model_SaldoPositivoSchema');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_SaldoPositivo $objSaldoPositivo)
    {
        $data = array(
            'nu_funcionario' => $objSaldoPositivo->getNuFuncionario(),
            'dt_referencia' => $objSaldoPositivo->getDtReferencia(),
            'total_min' => $objSaldoPositivo->getTotalMin()
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

    public function deleteByDtReferencia($dtReferencia)
    {
        $this->getDbTable()->delete("dt_referencia = '{$dtReferencia}'");
    }

    public function dataRelPositivo($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT MIN(dt_referencia) FROM painel.saldo_positivo WHERE dt_referencia <= '{$dtLDia}' :: DATE AND dt_referencia >= '{$dtReferencia}' :: DATE";
        $rs = $db->fetchAll($sql);
        return $rs[0]['min'];
    }
}

class Application_Model_SaldoPositivoSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "saldo_positivo";

    protected $_primary = "id";
}
