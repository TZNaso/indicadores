<?php

class Application_Model_LigacoesMapper
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
            $this->setDbTable('Application_Model_LigacoesSchema');
        }
        return $this->_dbTable;
    }

    public function deleteByDtReferencia($dtReferencia, $numeroArquivo)
    {
        $this->getDbTable()->delete("nr_arquivo = {$numeroArquivo} AND mes_referencia = '{$dtReferencia}'");
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Ligacoes();
            $entry->setCgc($row->cgc)
                ->setUnidade($row->unidade)
                ->setDdd($row->ddd)
                ->setNumero($row->numero)
                ->setNome_usuario($row->nome_usuario)
                ->setDescricao($row->descricao)
                ->setNumero_chamado($row->numero_chamado)
                ->setHora_ocorrencia($row->hora_ocorrencia)
                ->setDuracao($row->duracao)
                ->setDuracao_minutos($row->duracao_minutos)
                ->setValor($row->valor)
                ->setNome($row->nome)
                ->setMes_referencia($row->mes_referencia)
                ->setNr_arquivo($row->nr_arquivo);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_Ligacoes $objLigacoes)
    {
        $data = array(
            'cgc' => $objLigacoes->getCgc(),
            'unidade' => $objLigacoes->getUnidade(),
            'ddd' => $objLigacoes->getDdd(),
            'numero' => $objLigacoes->getNumero(),
            'nome_usuario' => $objLigacoes->getNome_usuario(),
            'descricao' => $objLigacoes->getDescricao(),
            'numero_chamado' => $objLigacoes->getNumero_chamado(),
            'hora_ocorrencia' => $objLigacoes->getHora_ocorrencia(),
            'duracao' => $objLigacoes->getDuracao(),
            'duracao_minutos' => $objLigacoes->getDuracao_minutos(),
            'valor' => $objLigacoes->getValor(),
            'nome' => $objLigacoes->getNome(),
            'mes_referencia' => $objLigacoes->getMes_referencia(),
            'nr_arquivo' => $objLigacoes->getNr_arquivo()
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
        $sql = "SELECT DISTINCT
                  mes_referencia as date,
                  nr_arquivo,
                  nome,
                  SUM(valor)
                FROM
                  painel.ligacoes
                GROUP BY
                  mes_referencia,
                  nr_arquivo,
                  nome
                ORDER BY
                  mes_referencia DESC;
                ";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getUltimoMesDisponivel()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT to_char(max(mes_referencia),'MM/YYYY') as dt_referencia from painel.ligacoes";
        $rs = $db->fetchAll($select);
        if ($rs) {
            return $rs[0]['dt_referencia'];
        } else {
            return null;
        }
    }

    public function getMesesDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(mes_referencia,'MM/YYYY')) dt_referencia, mes_referencia dt_ref
        FROM painel.ligacoes
        ORDER BY dt_ref DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAnosDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(mes_referencia,'YYYY')) ano
        FROM painel.ligacoes
        ORDER BY ano DESC";
        $rs = $db->fetchAll($select);
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
                    CASE WHEN (area_table.nu_area = 259 OR area_table.nu_area_vinculada = 259)
                      THEN area_table.no_sigla_area
                      ELSE coordenacao_table.no_sigla_area
                    END as coord,
                    area_table.no_sigla_area as equipe,
                    funcionario_sipti.no_matr_func as matricula,
                    ligacoes.nome_usuario as funcionario,
                    ligacoes.numero,
                    ligacoes.numero_chamado,
                    ligacoes.hora_ocorrencia,
                    ligacoes.duracao,
                    ligacoes.valor,
                    ligacoes.a_servico
                  FROM
                    painel.ligacoes,
                    painel.funcionario_sipti,
                    painel.hstro_funcionario_area_sipti,
                    painel.hist_ramal_sitel,
                    painel.ramal_sitel,
                    painel.area_sipti as area_table,
                    painel.area_sipti as coordenacao_table
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                    ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                    ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                    hist_ramal_sitel.data_inicio = (
                      SELECT MAX(hist.data_inicio)
                      FROM painel.hist_ramal_sitel AS hist, painel.funcionario_sipti AS func
                      WHERE hist.matricula = func.no_matr_func
                      AND hist.ramal_sitel_id = ramal_sitel.id
                      AND hist.data_inicio <= '{$dtLDia}'
                    ) AND
                    hist_ramal_sitel.matricula = funcionario_sipti.no_matr_func AND
                    funcionario_sipti.nu_funcionario = hstro_funcionario_area_sipti.nu_funcionario AND
                    hstro_funcionario_area_sipti.nu_area = area_table.nu_area AND
                    area_table.nu_area_vinculada = coordenacao_table.nu_area AND
                    hstro_funcionario_area_sipti.dt_inicio = (
                      SELECT MAX(hist.dt_inicio)
                      FROM painel.hstro_funcionario_area_sipti AS hist
                      WHERE hist.nu_funcionario = funcionario_sipti.nu_funcionario
                      AND hist.dt_inicio <= '{$dtLDia}'
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
                    CASE WHEN (area_table.nu_area = 259 OR area_table.nu_area_vinculada = 259)
                      THEN area_table.no_sigla_area
                      ELSE coordenacao_table.no_sigla_area
                    END as coord,
                    {$selectColmuns}
                    SUM(ligacoes.duracao) as duracao,
                    SUM(ligacoes.duracao_minutos) as duracao_minutos,
                    SUM(ligacoes.valor) as valor
                  FROM
                    painel.ligacoes,
                    painel.funcionario_sipti,
                    painel.hstro_funcionario_area_sipti,
                    painel.hist_ramal_sitel,
                    painel.ramal_sitel,
                    painel.area_sipti as area_table,
                    painel.area_sipti as coordenacao_table
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                    ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                    ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                    hist_ramal_sitel.data_inicio = (
                      SELECT MAX(hist.data_inicio)
                      FROM painel.hist_ramal_sitel AS hist, painel.funcionario_sipti AS func
                      WHERE hist.matricula = func.no_matr_func
                      AND hist.ramal_sitel_id = ramal_sitel.id
                      AND hist.data_inicio <= '{$dtLDia}'
                    ) AND
                    hist_ramal_sitel.matricula = funcionario_sipti.no_matr_func AND
                    funcionario_sipti.nu_funcionario = hstro_funcionario_area_sipti.nu_funcionario AND
                    hstro_funcionario_area_sipti.nu_area = area_table.nu_area AND
                    area_table.nu_area_vinculada = coordenacao_table.nu_area AND
                    hstro_funcionario_area_sipti.dt_inicio = (
                      SELECT MAX(hist.dt_inicio)
                      FROM painel.hstro_funcionario_area_sipti AS hist
                      WHERE hist.nu_funcionario = funcionario_sipti.nu_funcionario
                      AND hist.dt_inicio <= '{$dtLDia}'
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
                    $colmuns = "area_table.no_sigla_area as equipe,";
                }
                break;
            case Zend_Registry::get('Funcionario'):
                if ($query == Zend_Registry::get('Group')) {
                    $colmuns = ", equipe, funcionario_sipti.no_matr_func, funcionario_sipti.no_funcionario, ligacoes.numero";
                } else if ($query == Zend_Registry::get('Select')) {
                    $colmuns = "area_table.no_sigla_area as equipe,
                                funcionario_sipti.no_matr_func as matricula,
                                funcionario_sipti.no_funcionario as funcionario,
                                ligacoes.numero,";
                }
                break;
            default:
                error_log("Tipo de agregação não existente! LigaçõesMapper::selectColumnsByDataType");
        }

        return $colmuns;
    }

    public function getRamaisNotFound($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT
                    matricula,
                    coord,
                    equipe,
                    numero,
                    nome_usuario,
                    dt_referencia
                  FROM
                    painel.ramal_sitel
                    INNER JOIN
                    painel.ligacoes_outros ON ramal_id = ramal_sitel.id
                    RIGHT JOIN
                  (SELECT DISTINCT
                    numero,
                    nome_usuario
                  FROM
                    painel.ligacoes
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                    CONCAT(nome_usuario, hora_ocorrencia::text) not in
                    (
                      SELECT
                        CONCAT(nome_usuario, hora_ocorrencia::text)
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
                          SELECT
                            MAX(hist.data_inicio)
                          FROM
                            painel.hist_ramal_sitel AS hist
                          WHERE
                            hist.ramal_sitel_id = ramal_sitel.id AND
                            hist.matricula = prestador.matricula_formatada AND
                            hist.data_inicio <= '{$dtLDia}'
                      ) AND
                      hist_ramal_sitel.matricula = prestador.matricula_formatada AND
                      prestador.data_referencia = (
                        SELECT
                          MAX(hist.data_referencia)
                        FROM
                          painel.prestador AS hist
                        WHERE
                          hist.matricula = prestador.matricula AND
                          hist.data_referencia <= '{$dtLDia}'
                      )

                      UNION

                      SELECT
                        CONCAT(nome_usuario, hora_ocorrencia::text)
                      FROM
                        painel.ligacoes,
                        painel.funcionario_sipti,
                        painel.hstro_funcionario_area_sipti,
                        painel.hist_ramal_sitel,
                        painel.ramal_sitel,
                        painel.area_sipti as area_table,
                        painel.area_sipti as coordenacao_table
                      WHERE
                        ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                        ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                        ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                        hist_ramal_sitel.data_inicio = (
                          SELECT
                            MAX(hist.data_inicio)
                          FROM
                            painel.hist_ramal_sitel AS hist
                          WHERE
                            hist.ramal_sitel_id = ramal_sitel.id AND
                            hist.matricula = funcionario_sipti.no_matr_func AND
                            hist.data_inicio <= '{$dtLDia}'
                        ) AND
                      hist_ramal_sitel.matricula = funcionario_sipti.no_matr_func AND
                      funcionario_sipti.nu_funcionario = hstro_funcionario_area_sipti.nu_funcionario AND
                      hstro_funcionario_area_sipti.nu_area = area_table.nu_area AND
                      area_table.nu_area_vinculada = coordenacao_table.nu_area AND
                      hstro_funcionario_area_sipti.dt_inicio = (
                        SELECT
                          MAX(hist.dt_inicio)
                        FROM
                          painel.hstro_funcionario_area_sipti AS hist
                        WHERE
                          hist.nu_funcionario = funcionario_sipti.nu_funcionario AND
                          hist.dt_inicio <= '{$dtLDia}'
                      )

                      UNION

                        SELECT
                          CONCAT(nome_usuario, hora_ocorrencia::text)

                        FROM
                          painel.ligacoes,
                          painel.ramal_sitel,
                          painel.ligacoes_outros
                        WHERE
                          ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                          ligacoes_outros.dt_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}' AND
                          ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                          ramal_sitel.id = ligacoes_outros.ramal_id AND
                          ligacoes_outros.dt_referencia = (
                            SELECT MAX(hist.dt_referencia)
                            FROM painel.ligacoes_outros AS hist
                            WHERE hist.ramal_id = ramal_sitel.id
                            AND hist.dt_referencia <= '{$dtLDia}'
                          )
                    )
                  ) u
                  ON CONCAT(nome_usuario, numero) = CONCAT(ligacoes_outros.nome, SUBSTRING(ramal_sitel.ramal, 6, length(ramal_sitel.ramal))) AND ligacoes_outros.dt_referencia = (
                          SELECT
                            MAX(hist.dt_referencia)
                          FROM
                            painel.ligacoes_outros AS hist,
                      	    painel.ramal_sitel
                      	  WHERE
                      	    ramal_sitel.id = hist.ramal_id AND
                      	    numero::text = SUBSTRING(ramal_sitel.ramal, 6, length(ramal_sitel.ramal)) AND
                            hist.dt_referencia <= '{$dtLDia}'
                        )
                  ORDER BY matricula DESC
                ";

        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getFileNumber($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT
                      CASE
                        WHEN MAX(arquivo_nr) IS NULL THEN 0
                        ELSE MAX(arquivo_nr)
                      END AS arquivo_nr
                    FROM
                      painel.ligacoes
                    WHERE
                      mes_referencia = '{$dtReferencia}'
                  ";

        $rs = $db->fetchAll($select);

        return $rs['arquivo_nr'];
    }

    public function exportReport($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT
                    *
                  FROM
                    painel.ligacoes
                  WHERE
                    ligacoes.mes_referencia BETWEEN '{$dtReferencia}' AND '{$dtLDia}'
                ";

        $rs = $db->fetchAll($select);

        return $rs;
    }
}

class Application_Model_LigacoesSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "ligacoes";

    protected $_primary = "id";
}
