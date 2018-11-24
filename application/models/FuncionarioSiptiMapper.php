<?php

class Application_Model_FuncionarioSiptiMapper
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
            $this->setDbTable('Application_Model_FuncionarioSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_FuncionarioSipti();
            $entry->setNuFuncionario($row->nu_funcionario)
                ->setNuArea($row->nu_area)
                ->setNuFuncao($row->nu_funcao)
                ->setNuCargo($row->nu_cargo)
                ->setNoMatrFunc($row->no_matr_func)
                ->setNoMatriculaCaixa($row->no_matricula_caixa);

            // @TODO Continuar implementação

            $entries[] = $entry;
        }
        return $entries;
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.funcionario_sipti";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb001_funcionario";

        return $db->fetchAll($select);
    }

    public function fetchByUserId($userId)
    {
        $resultSet = $this->getDbTable()->fetchAll("no_matr_func = '{$userId}'");

        $entry = new Application_Model_FuncionarioSipti();

        foreach ($resultSet as $row) {

            $entry = new Application_Model_FuncionarioSipti();
            $entry->setNuFuncionario($row->nu_funcionario);
            $entry->setNuArea($row->nu_area);
            $entry->setNuFuncao($row->nu_funcao);
            $entry->setNuCargo($row->nu_cargo);
            $entry->setNoMatrFunc($row->no_matr_func);
            $entry->setNoMatriculaCaixa($row->no_matricula_caixa);
            $entry->setNoFuncionario($row->no_funcionario);

            // @TODO Continuar implementação
        }

        return $entry;
    }

    public function fetchByUserName($userName)
    {
        $resultSet = $this->getDbTable()->fetchAll("no_funcionario = '{$userName}'");

        $entry = new Application_Model_FuncionarioSipti();

        foreach ($resultSet as $row) {

            $entry = new Application_Model_FuncionarioSipti();
            $entry->setNuFuncionario($row->nu_funcionario);
            $entry->setNuArea($row->nu_area);
            $entry->setNuFuncao($row->nu_funcao);
            $entry->setNuCargo($row->nu_cargo);
            $entry->setNoMatrFunc($row->no_matr_func);
            $entry->setNoMatriculaCaixa($row->no_matricula_caixa);
            $entry->setNoFuncionario($row->no_funcionario);

            // @TODO Continuar implementação
        }

        return $entry;
    }

    public function save(Application_Model_FuncionarioSipti $objFuncionarioSipti)
    {
        $data = array(
            'nu_funcionario' => $objFuncionarioSipti->getNuFuncionario(),
            'nu_area' => $objFuncionarioSipti->getNuArea(),
            'nu_funcao' => $objFuncionarioSipti->getNuFuncao(),
            'nu_cargo' => $objFuncionarioSipti->getNuCargo(),
            'no_matricula_caixa' => $objFuncionarioSipti->getNoMatriculaCaixa(),
            'no_matr_func' => $objFuncionarioSipti->getNoMatrFunc(),
            'no_funcionario' => $objFuncionarioSipti->getNoFuncionario(),
            'no_apelido' => $objFuncionarioSipti->getNoApelido(),
            'ic_sexo' => $objFuncionarioSipti->getIcSexo(),
            'dt_aniversario' => $objFuncionarioSipti->getDtAniversario(),
            'no_arquivo_foto' => $objFuncionarioSipti->getNoArquivoFoto(),
            'id_usuario_outlook' => $objFuncionarioSipti->getIdUsuarioOutlook(),
            'no_endereco' => $objFuncionarioSipti->getNoEndereco(),
            'no_bairro' => $objFuncionarioSipti->getNoBairro(),
            'no_uf' => $objFuncionarioSipti->getNouf(),
            'nu_cep' => $objFuncionarioSipti->getNuCep(),
            'dt_admissao' => $objFuncionarioSipti->getDtAdmissao(),
            'de_localizacao_interna' => $objFuncionarioSipti->getDeLocalizacaoInterna(),
            'no_logico_micro' => $objFuncionarioSipti->getNoLogicoMicro(),
            'dt_nascimento' => $objFuncionarioSipti->getDtNascimento(),
            'de_responsavel' => $objFuncionarioSipti->getDeResponsavel(),
            'dt_atualizacao' => $objFuncionarioSipti->getDtAtualizacao()
        );

        if ($objFuncionarioSipti->getIcAtivoRedea()) {
            $data['ic_ativo_redea'] = 1;
        } else {
            $data['ic_ativo_redea'] = 0;
        }

        $this->getDbTable()->insert($data);
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.funcionario_sipti
            (SELECT * FROM ptism001.gprtb001_funcionario);";

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

class Application_Model_FuncionarioSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "funcionario_sipti";

    protected $_primary = "nu_funcionario";
}
