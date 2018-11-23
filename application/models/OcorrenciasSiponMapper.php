<?php

class Application_Model_OcorrenciasSiponMapper
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
            $this->setDbTable('Application_Model_OcorrenciasSiponSchema');
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
            $entry = new Application_Model_OcorrenciasSipon();
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

    public function save(Application_Model_OcorrenciasSipon $objOcorrenciasSipon)
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

    public function getUltimoMesDisponivel()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT to_char(max(dt_referencia),'MM/YYYY') as dt_referencia from painel.ocorrencias_sipon";
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
        $select = "SELECT DISTINCT(to_char(dt_referencia,'MM/YYYY')) dt_referencia, dt_referencia dt_ref
        FROM painel.ocorrencias_sipon
        ORDER BY dt_ref DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAnosDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'YYYY')) ano
        FROM painel.ocorrencias_sipon
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
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.no_sigla_area
            ELSE coo.no_sigla_area
        END as coord
        , are.no_sigla_area as equipe
        , his.nu_area
        , fun.no_funcionario
        , qt_53, qt_56
        , qt_57, qt_58
        , qt_70, qt_195
        , qt_19, qt_20
        , qt_bloqueio, qt_total_ocorrencias
        , qt_total_pontos_utilizados, qt_limite
        FROM painel.ocorrencias_sipon oco
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
        // file_put_contents('php://stderr', print_r($select, TRUE));
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function ocorXFunc($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "
        SELECT
        t1.coord,
        total as funcionarios,
                    ocorrencias
                FROM
                    (
                SELECT
                coord
                , SUM(soma) as ocorrencias
                from(
                    SELECT
                        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                            THEN are.no_sigla_area
                            ELSE coo.no_sigla_area
                        END as coord
                        , are.no_sigla_area as equipe
                        , his.nu_area
                        , fun.no_funcionario
                        , (qt_53 + qt_56 + qt_57 + qt_58 + qt_70 + qt_195 + qt_19 + qt_20 + qt_bloqueio) as soma
                        FROM painel.ocorrencias_sipon oco
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
                        ORDER BY coord

                )dados GROUP BY coord


                )t1

                LEFT JOIN(

                SELECT
                coord
                , count(no_funcionario) as total
                from(
                    SELECT
                        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                            THEN are.no_sigla_area
                            ELSE coo.no_sigla_area
                        END as coord
                        , are.no_sigla_area as equipe
                        , his.nu_area
                        , fun.no_funcionario
                        FROM painel.funcionario_sipti fun
                        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
                        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
                        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
                        WHERE fun.ic_ativo_redea = TRUE
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
                        ORDER BY coord

                )dados GROUP BY coord



                )t2 ON t1.coord = t2.coord
        ";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getConsultaGeral($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT fun.no_funcionario,
        fun.no_matricula_caixa,
        fun.no_matr_func,
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.no_sigla_area
            ELSE coo.no_sigla_area
        END as no_sigla_coord,
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.de_area
            ELSE coo.de_area
        END as de_coord,
        are.no_sigla_area, are.de_area,
                oco.qt_56, oco.qt_57, oco.qt_58, oco.qt_70, oco.qt_195, oco.qt_53, oco.qt_19, oco.qt_20, oco.qt_bloqueio, oco.qt_total_ocorrencias, oco.qt_total_pontos_utilizados, oco.qt_limite
        FROM painel.ocorrencias_sipon oco
        JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE oco.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
        AND(
            /* estava na equipe no inicio E não saiu até o fim do mês*/
            ( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe no inicio E não saiu até o fim do mês*/
            OR( his.dt_inicio >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND(his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe E saiu até o fim do mês*/
            OR( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND his.dt_fim >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
            )
        )
        /* data de inicio em alguma equipe mais proxima do fim do mês */
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
            AND hist.nu_area in (
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
                /* as proprias coordenações */
                UNION
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area_vinculada = 259
            )
        )
        ORDER BY are.no_sigla_area;";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getConsultaCoordenacaoEquipe($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT   CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.no_sigla_area
            ELSE coo.no_sigla_area
        END as no_sigla_coord,
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.de_area
            ELSE coo.de_area
        END as de_coord,
        are.no_sigla_area, are.de_area,
        SUM(oco.qt_56) qt_56, SUM(oco.qt_57) qt_57, SUM(oco.qt_58) qt_58, SUM(oco.qt_70) qt_70, SUM(oco.qt_195)
        qt_195, SUM(oco.qt_53) qt_53, SUM(oco.qt_19) qt_19, SUM(oco.qt_20) qt_20, SUM(oco.qt_bloqueio) qt_bloqueio, SUM(oco.qt_total_ocorrencias) qt_total_ocorrencias, SUM(oco.qt_total_pontos_utilizados) qt_total_pontos_utilizados, SUM(oco.qt_limite) qt_limite
        FROM painel.ocorrencias_sipon oco
        JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE oco.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
        AND(
            /* estava na equipe no inicio E não saiu até o fim do mês*/
            ( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe no inicio E não saiu até o fim do mês*/
            OR( his.dt_inicio >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND(his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe E saiu até o fim do mês*/
            OR( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND his.dt_fim >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
            )
        )
        /* data de inicio em alguma equipe mais proxima do fim do mês */
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
            AND hist.nu_area in (
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
                /* as proprias coordenações */
                UNION
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area_vinculada = 259
            )
        )
        GROUP BY are.nu_area, coo.nu_area
        ORDER BY are.no_sigla_area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getConsultaCoordenacao($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT dados.no_sigla_coord,
        dados.de_coord,
        SUM(dados.qt_56) qt_56,
        SUM(dados.qt_57) qt_57,
        SUM(dados.qt_58) qt_58,
        SUM(dados.qt_70) qt_70,
        SUM(dados.qt_195) qt_195,
        SUM(dados.qt_53) qt_53,
        SUM(dados.qt_19) oco.qt_19,
        SUM(dados.qt_20) qt_20,
        SUM(dados.qt_bloqueio) qt_bloqueio,
        SUM(dados.qt_total_ocorrencias) qt_total_ocorrencias,
        SUM(dados.qt_total_pontos_utilizados) qt_total_pontos_utilizados,
        SUM(dados.qt_limite) qt_limite
        FROM (
            SELECT CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.no_sigla_area
                ELSE coo.no_sigla_area
            END as no_sigla_coord,
            CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.de_area
                ELSE coo.de_area
            END as de_coord,
            SUM(oco.qt_56) qt_56,
            SUM(oco.qt_57) qt_57,
            SUM(oco.qt_58) qt_58,
            SUM(oco.qt_70) qt_70,
            SUM(oco.qt_195) qt_195,
            SUM(oco.qt_53) qt_53,
            SUM(oco.qt_19) oco.qt_19,
            SUM(oco.qt_20) qt_20,
            SUM(oco.qt_bloqueio) qt_bloqueio,
            SUM(oco.qt_total_ocorrencias) qt_total_ocorrencias,
            SUM(oco.qt_total_pontos_utilizados) qt_total_pontos_utilizados,
            SUM(oco.qt_limite) qt_limite
            FROM painel.ocorrencias_sipon oco
            JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
            JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
            JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
            JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
            WHERE oco.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
            AND(
                /* estava na equipe no inicio E não saiu até o fim do mês*/
                ( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe no inicio E não saiu até o fim do mês*/
                OR( his.dt_inicio >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                    AND(his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe E saiu até o fim do mês*/
                OR( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                    AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                    AND his.dt_fim >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                )
            )
            /* data de inicio em alguma equipe mais proxima do fim do mês */
            AND his.dt_inicio = (
                SELECT MAX(hist.dt_inicio)
                FROM painel.hstro_funcionario_area_sipti AS hist
                WHERE hist.nu_funcionario = fun.nu_funcionario
                AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
                AND hist.nu_area in (
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
                    /* as proprias coordenações */
                    UNION
                    SELECT nu_area
                    FROM painel.area_sipti
                    WHERE nu_area_vinculada = 259
                )
            )
            GROUP BY are.nu_area, coo.nu_area, no_sigla_coord, de_coord
            ORDER BY coo.no_sigla_area
        ) dados
        GROUP BY dados.no_sigla_coord, dados.de_coord
        ORDER BY dados.no_sigla_coord";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getExportacao($arrParametros)
    {
        $dtLDia = $this->getUltimoDiaMes('01/' . $arrParametros['dt_referencia']);
        $dtReferencia = '01/' . $arrParametros['dt_referencia'];
        if (isset($arrParametros['empregado'])) {
            // Se tiver empregado, pegamos a consulta geral
            $rs = $this->getConsultaGeral($dtReferencia, $dtLDia);
        } else {
            if (isset($arrParametros['coordenacao']) && ! isset($arrParametros['equipe'])) {
                $rs = $this->getConsultaCoordenacao($dtReferencia, $dtLDia);
            } else {
                $rs = $this->getConsultaCoordenacaoEquipe($dtReferencia, $dtLDia);
            }
        }
        return $rs;
    }

    public function getExportacaoAnual($ano)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT  to_char(oco.dt_referencia, 'MM/YYYY') dt_referencia,
        fun.no_funcionario, fun.no_matricula_caixa, fun.no_matr_func,
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.no_sigla_area
                ELSE coo.no_sigla_area
        END as no_sigla_coord,
        CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.de_area
                ELSE coo.de_area
        END as de_coord,
        are.no_sigla_area, are.de_area,
        oco.qt_56, oco.qt_57, oco.qt_58, oco.qt_70, oco.qt_195, oco.qt_53, oco.qt_19, oco.qt_20, oco.qt_bloqueio, oco.qt_total_ocorrencias, oco.qt_total_pontos_utilizados, oco.qt_limite
        FROM painel.ocorrencias_sipon oco
        JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE to_char(oco.dt_referencia, 'YYYY') = '{$ano}'
        AND oco.dt_referencia >= his.dt_inicio
        AND (his.dt_fim is null OR oco.dt_referencia <= his.dt_fim)
        ORDER BY oco.dt_referencia, fun.no_funcionario;";

        $rs = $db->fetchAll($sql);

        return $rs;
    }
}

class Application_Model_OcorrenciasSiponSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "ocorrencias_sipon";

    protected $_primary = array(
        "nu_empregado",
        "dt_referencia"
    );


}