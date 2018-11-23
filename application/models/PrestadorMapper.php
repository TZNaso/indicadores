<?php

class Application_Model_PrestadorMapper
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
            $this->setDbTable('Application_Model_PrestadorSchema');
        }
        return $this->_dbTable;
    }

    public function deleteByDtReferencia($dtReferencia)
    {
        $this->getDbTable()->delete("data_referencia = '{$dtReferencia}'");
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Prestador();
            $entry->setCpf($row->cpf)
                ->setStatus($row->status)
                ->setMatricula($row->matricula)
                ->setRestricao($row->restricao)
                ->setLotacao($row->lotacao)
                ->setLotacao_fisica($row->lotacao_fisica)
                ->setEmpresa($row->empresa)
                ->setTipo($row->tipo)
                ->setNome($row->nome)
                ->setSexo($row->sexo)
                ->setData_nascimento($row->data_nascimento)
                ->setIdentidade($row->identidade)
                ->setOrgao_emissor($row->orgao_emissor)
                ->setNome_mae($row->nome_mae)
                ->setData_referencia($row->data_referencia);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_Prestador $objLigacoes)
    {
        $data = array(
            'cpf' => $objLigacoes->getCpf(),
            'status' => $objLigacoes->getStatus(),
            'matricula' => $objLigacoes->getMatricula(),
            'restricao' => $objLigacoes->getRestricao(),
            'lotacao' => $objLigacoes->getLotacao(),
            'lotacao_fisica' => $objLigacoes->getLotacao_fisica(),
            'empresa' => $objLigacoes->getEmpresa(),
            'tipo' => $objLigacoes->getTipo(),
            'nome' => $objLigacoes->getNome(),
            'sexo' => $objLigacoes->getSexo(),
            'data_nascimento' => $objLigacoes->getData_nascimento(),
            'identidade' => $objLigacoes->getIdentidade(),
            'orgao_emissor' => $objLigacoes->getOrgao_emissor(),
            'nome_mae' => $objLigacoes->getNome_mae(),
            'data_referencia' => $objLigacoes->getData_referencia()
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

    public function allUploads()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT DISTINCT data_referencia as date FROM painel.prestador ORDER BY data_referencia DESC;";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getUltimoDiaMes($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT (date_trunc('MONTH',to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')) + INTERVAL '1 MONTH - 1 day')::date";
        $rs = $db->fetchAll($sql);
        return $rs[0]['date'];
    }

    public function empregadosMes($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT
                    prestador.empresa as coord,
                    prestador.empresa as equipe,
                    prestador.matricula_formatada as matricula,
                    ligacoes.nome_usuario as funcionario,
                    ligacoes.numero,
                    ligacoes.numero_chamado,
                    ligacoes.hora_ocorrencia,
                    ligacoes.duracao,
                    ligacoes.valor,
                    ligacoes.a_servico
                  FROM
                    painel.ligacoes,
                    painel.ramal_sitel,
                    painel.hist_ramal_sitel,
                    painel.prestador
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                    ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                    ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                    hist_ramal_sitel.data_inicio = (
                      SELECT MAX(hist.data_inicio)
                      FROM painel.hist_ramal_sitel AS hist
                      WHERE hist.matricula = prestador.matricula_formatada
                      AND hist.data_inicio <= '{$dtLDia}'
                    ) AND
                    hist_ramal_sitel.matricula = prestador.matricula_formatada AND
                    prestador.data_referencia = (
                      SELECT MAX(hist.data_referencia)
                      FROM painel.prestador AS hist
                      WHERE hist.matricula = prestador.matricula
                      AND hist.data_referencia <= '{$dtLDia}'
                    );";

        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getSumBy($type, $dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $selectColmuns = self::selectColumnsByDataType($type, Zend_Registry::get('Select'));
        $groupByColmuns = self::selectColumnsByDataType($type, Zend_Registry::get('Group'));

        $select = "SELECT
                    prestador.empresa as coord,
                    {$selectColmuns}
                    SUM(ligacoes.duracao) as duracao,
                    SUM(ligacoes.duracao_minutos) as duracao_minutos,
                    SUM(ligacoes.valor) as valor
                  FROM
                    painel.ligacoes,
                    painel.ramal_sitel,
                    painel.hist_ramal_sitel,
                    painel.prestador
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                    ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                    ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                    hist_ramal_sitel.data_inicio = (
                      SELECT MAX(hist.data_inicio)
                      FROM painel.hist_ramal_sitel AS hist
                      WHERE hist.matricula = prestador.matricula_formatada
                      AND hist.data_inicio <= '{$dtLDia}'
                    ) AND
                    hist_ramal_sitel.matricula = prestador.matricula_formatada AND
                    prestador.data_referencia = (
                      SELECT MAX(hist.data_referencia)
                      FROM painel.prestador AS hist
                      WHERE hist.matricula = prestador.matricula
                      AND hist.data_referencia <= '{$dtLDia}'
                    )
                  GROUP BY
                    coord
                    {$groupByColmuns}
                  ORDER BY
                    coord ASC;
                ";

        $rs = $db->fetchAll($select);

        return $rs;
    }

    function selectColumnsByDataType($type, $query)
    {
        $colmuns = "";
        switch ($type) {
            case Zend_Registry::get('Coord'):
                break;
            case Zend_Registry::get('Equipe'):
                if ($query == Zend_Registry::get('Group')) {
                    $colmuns = ", equipe";
                } else if ($query == Zend_Registry::get('Select')) {
                    $colmuns = "prestador.empresa as equipe,";
                }
                break;
            case Zend_Registry::get('Funcionario'):
                if ($query == Zend_Registry::get('Group')) {
                    $colmuns = ", equipe, prestador.matricula_formatada, funcionario, ligacoes.numero";
                } else if ($query == Zend_Registry::get('Select')) {
                    $colmuns = "prestador.empresa as equipe,
                                prestador.matricula_formatada as matricula,
                                ligacoes.nome_usuario as funcionario,
                                ligacoes.numero,";
                }
                break;
            default:
                error_log("Tipo de agregação não existente! LigaçõesMapper::selectColumnsByDataType");
        }

        return $colmuns;
    }
}

class Application_Model_PrestadorSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "prestador";

    protected $_primary = "id";
}
