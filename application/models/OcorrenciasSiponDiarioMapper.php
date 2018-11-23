<?php

class Application_Model_OcorrenciasSiponDiarioMapper
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
            $this->setDbTable('Application_Model_OcorrenciasSiponDiarioSchema');
        }
        return $this->_dbTable;
    }

    public function deleteByDtReferencia($dtReferencia)
    {
        $this->getDbTable()->delete("dt_referencia = '{$dtReferencia}'");
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_OcorrenciasSiponDiario();
            $entry->setNuEmpregado($row->nu_empregado)
                ->setDtReferencia($row->dt_referencia)
                ->setNuCodigoFg($row->nu_codigo_fg)
                ->setNoFg($row->no_fg)
                ->setQt56($row->qt_56)
                ->setQt57($row->qt_57)
                ->setQt58($row->qt_58)
                ->setQt70($row->qt_70)
                ->setQt195($row->qt_195)
                ->setQt19($row->qt_19)
                ->setQt20($row->qt_20)
                ->setQtBloqueio($row->qt_bloqueio)
                ->setQtTotalOcorrencias($row->qt_total_ocorrencias)
                ->setQtTotalPontosUtilizados($row->qt_total_pontos_utilizados)
                ->setQtLimite($row->qt_limite)
                ->setQt53($row->qt_53);

            $entries[] = $entry;
        }
        return $entries;
    }

    public function save(Application_Model_OcorrenciasSiponDiario $objOcorrenciasSipon)
    {
        $data = array(
            'nu_empregado' => $objOcorrenciasSipon->getNuEmpregado(),
            'dt_referencia' => $objOcorrenciasSipon->getDtReferencia(),
            'nu_codigo_fg' => $objOcorrenciasSipon->getNuCodigoFg(),
            'no_fg' => $objOcorrenciasSipon->getNoFg(),
            'qt_56' => $objOcorrenciasSipon->getQt56(),
            'qt_57' => $objOcorrenciasSipon->getQt57(),
            'qt_58' => $objOcorrenciasSipon->getQt58(),
            'qt_70' => $objOcorrenciasSipon->getQt70(),
            'qt_195' => $objOcorrenciasSipon->getQt195(),
            'qt_19' => $objOcorrenciasSipon->getQt19(),
            'qt_20' => $objOcorrenciasSipon->getQt20(),
            'qt_bloqueio' => $objOcorrenciasSipon->getQtBloqueio(),
            'qt_total_ocorrencias' => $objOcorrenciasSipon->getQtTotalOcorrencias(),
            'qt_total_pontos_utilizados' => $objOcorrenciasSipon->getQtTotalPontosUtilizados(),
            'qt_limite' => $objOcorrenciasSipon->getQtLimite(),
            'qt_53' => $objOcorrenciasSipon->getQt53()
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

    public function getDatasDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'DD/MM/YYYY'))
        dt_referencia, dt_referencia dt_ref
        FROM painel.ocorrencias_sipon_diario
        ORDER BY dt_ref desc";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function allUploads()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT DISTINCT dt_referencia as date FROM painel.ocorrencias_sipon_diario ORDER BY dt_referencia DESC;";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function empregadosMes($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.no_sigla_area
            ELSE coo.no_sigla_area
        END as coord
        , are.no_sigla_area as equipe
        , fun.no_funcionario
        , fun.no_matricula_caixa
        , qt_53, qt_56
        , qt_57, qt_58
        , qt_70, qt_195
        , qt_19, qt_20
        , qt_bloqueio, qt_total_ocorrencias
        , qt_total_pontos_utilizados, qt_limite
        FROM painel.ocorrencias_sipon_diario oco
        JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE oco.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
        AND(
            /* estava na equipe no inicio E não saiu até o fim do mês*/
            ( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') AND
            ( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe no inicio E não saiu até o fim do mês*/
            OR(
                his.dt_inicio >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')  AND
                ( his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')) AND
                ( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe E saiu até o fim do mês*/
            OR(
                his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') AND
                ( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')) AND
                his.dt_fim >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
            )
        )
        /* data de inicio em alguma equipe mais proxima do fim do mês */
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
        )
        AND his.nu_area in (
            /* todas as equipes */
            SELECT nu_area
            FROM painel.area_sipti
            WHERE nu_area_vinculada IN(
                /* de todas as coordenações */
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area_vinculada = 259
            )
            /* a propria cedesbr */
            UNION
            SELECT nu_area
            FROM painel.area_sipti
            WHERE nu_area = 259
            OR nu_area_vinculada = 259
        )
        ORDER BY fun.no_funcionario";
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
}

class Application_Model_OcorrenciasSiponDiarioSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "ocorrencias_sipon_diario";

    protected $_primary = array(
        "id"
    );
}
