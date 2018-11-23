<?php

class Application_Model_LigacoesOutrosMapper
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
            $this->setDbTable('Application_Model_LigacoesOutrosSchema');
        }
        return $this->_dbTable;
    }

    public function deleteByDtReferencia($dtReferencia)
    {
        $this->getDbTable()->delete("mes_referencia = '{$dtReferencia}'");
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_LigacoesOutros();
            $entry->setRamal_id($row->ramal_id)
                ->setNome($row->nome)
                ->setMatricula($row->matricula)
                ->setCoord($row->coordenacao)
                ->setEquipe($row->equipe)
                ->setDtReferencia($row->dt_referencia);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_LigacoesOutros $objLigacoes)
    {
        $data = array(
            'ramal_id' => $objLigacoes->getRamal_id(),
            'nome' => $objLigacoes->getNome(),
            'matricula' => $objLigacoes->getMatricula(),
            'coord' => $objLigacoes->getCoord(),
            'equipe' => $objLigacoes->getEquipe(),
            'dt_referencia' => $objLigacoes->getDtReferencia()
        );

        $ramal_sitel = new Application_Model_RamalSitelMapper();
        $data['ramal_id'] = $ramal_sitel->getIDByRamal($data['ramal_id'], false);

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
        $sql = "SELECT DISTINCT mes_referencia as date FROM painel.ligacoes ORDER BY mes_referencia ASC;";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function empregadoMes($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT
                ligacoes_outros.coord,
                ligacoes_outros.equipe,
                ligacoes_outros.matricula,
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
                );";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function sumNotFound($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT
                    SUM(duracao) as tempo,
                    SUM(valor) as valor
                  FROM
                  painel.ligacoes
                  INNER JOIN
                  (
                  SELECT
                      CONCAT(numero, nome_usuario) as nome_numero
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
                            WHERE hist.nome = ligacoes.nome_usuario
                            AND hist.dt_referencia <= '{$dtLDia}'
                          )
                    )
                    ) u
                    ON CONCAT(u.nome_usuario, u.numero) = CONCAT(ligacoes_outros.nome, SUBSTRING(ramal_sitel.ramal, 6, length(ramal_sitel.ramal))) AND ligacoes_outros.dt_referencia = (
                            SELECT
                              MAX(hist.dt_referencia)
                            FROM
                              painel.ligacoes_outros AS hist
                            WHERE
                              hist.nome = u.nome_usuario AND
                              hist.dt_referencia <= '{$dtLDia}'
                          )
                    ORDER BY matricula DESC
                  ) u_2
                  ON nome_numero = CONCAT(ligacoes.numero, ligacoes.nome_usuario)
	";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getSumBy($type, $dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $selectColmuns = self::selectColumnsByDataType($type, Zend_Registry::get('Select'));
        $groupByColmuns = self::selectColumnsByDataType($type, Zend_Registry::get('Group'));

        $select = "SELECT
                    ligacoes_outros.coord,
                    {$selectColmuns}
                    SUM(ligacoes.duracao) as duracao,
                    SUM(ligacoes.duracao_minutos) as duracao_minutos,
                    SUM(ligacoes.valor) as valor
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
                    $colmuns = ", ligacoes_outros.equipe";
                } else if ($query == Zend_Registry::get('Select')) {
                    $colmuns = "ligacoes_outros.equipe,";
                }
                break;
            case Zend_Registry::get('Funcionario'):
                if ($query == Zend_Registry::get('Group')) {
                    $colmuns = ",ligacoes_outros.equipe,
                                ligacoes_outros.matricula,
                                funcionario,
                                ligacoes.numero";
                } else if ($query == Zend_Registry::get('Select')) {
                    $colmuns = "ligacoes_outros.equipe,
                                ligacoes_outros.matricula,
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

class Application_Model_LigacoesOutrosSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "ligacoes_outros";

    protected $_primary = "id";
}
