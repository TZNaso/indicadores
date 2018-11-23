<?php

class Application_Model_HoraExtraMapper
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
            $this->setDbTable('Application_Model_HoraExtraSchema');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_HoraExtra $objHoraExtra)
    {
        $data = array(
            'nu_empregado' => $objHoraExtra->getNuEmpregado(),
            'dt_referencia' => $objHoraExtra->getDtReferencia(),
            'nu_codigo_fg' => $objHoraExtra->getNuCodigoFg(),
            'no_fg' => $objHoraExtra->getNoFg(),
            'nu_he_pg_285' => $objHoraExtra->getNuHePg285(),
            'nu_he_pg_296' => $objHoraExtra->getNuHePg296(),
            'nu_he_pg_302' => $objHoraExtra->getNuHePg302(),
            'nu_he_pg_demais_proj' => $objHoraExtra->getNuHePgDemaisProj(),
            'nu_valor_he_pg_285' => $objHoraExtra->getNuValorHePg285(),
            'nu_valor_he_pg_296' => $objHoraExtra->getNuValorHePg296(),
            'nu_valor_he_pg_302' => $objHoraExtra->getNuValorHePg302(),
            'nu_valor_he_pg_demais_proj' => $objHoraExtra->getNuValorHePgDemaisProj(),
            'nu_he_comp_284' => $objHoraExtra->getNuHeComp284(),
            'nu_valor_he_comp_284' => $objHoraExtra->getNuValorHeComp284(),
            'nu_total_he' => $objHoraExtra->getNuTotalHe(),
            'nu_valor_total_he' => $objHoraExtra->getNuValorTotalHe()
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

    public function allUploads()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT DISTINCT dt_referencia as date FROM painel.hora_extra ORDER BY dt_referencia DESC;";
        $rs = $db->fetchAll($sql);
        return $rs;
    }


    public function getUltimoMesDisponivel()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT to_char(max(dt_referencia),'MM/YYYY')
            as dt_referencia from painel.hora_extra";
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
        $select = "SELECT DISTINCT(to_char(dt_referencia,'MM/YYYY')) dt_referencia,
        dt_referencia dt_ref
        FROM painel.hora_extra
        ORDER BY dt_ref DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getMesesDisponiveisPorAno()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'MM/YYYY')) dt_referencia,
        dt_referencia dt_ref
        FROM painel.hora_extra
        WHERE  dt_referencia > to_timestamp('31/12/2014', 'DD/MM/YYYY')
        ORDER BY dt_ref DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAnosDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'YYYY')) ano
        FROM painel.hora_extra
        ORDER BY ano DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getUltimoDiaMes($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT (date_trunc('MONTH',to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')) + INTERVAL '1 MONTH - 1 day')::date";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getTotalizacaoEmpregados($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT fun.no_funcionario, his.nu_area,
        nu_he_pg_285, he.nu_he_pg_296, he.nu_he_pg_302,
        he.nu_he_pg_demais_proj, nu_valor_he_pg_285, he.nu_valor_he_pg_296,
        he.nu_valor_he_pg_302, he.nu_valor_he_pg_demais_proj, he.nu_he_comp_284,
        he.nu_valor_he_comp_284, he.nu_valor_he_comp_284, he.nu_total_he,
        he.nu_valor_total_he
        FROM painel.hora_extra he
        JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        WHERE he.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
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
        ORDER BY fun.no_funcionario";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAreasPorData($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(are.no_sigla_area || ' - ' || are.de_area) area, are.nu_area
        FROM painel.area_sipti are
        JOIN painel.hstro_funcionario_area_sipti his ON (are.nu_area = his.nu_area)
        JOIN painel.funcionario_sipti fun on (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.hora_extra he on (he.nu_empregado = fun.nu_funcionario)
        WHERE he.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
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
        ORDER BY area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getConsultaCoordenacao($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT no_sigla_coord, de_coord,
        sum(nu_he_pg_285) nu_he_pg_285,
        sum(nu_he_pg_296) nu_he_pg_296,
        sum(nu_he_pg_302) nu_he_pg_302,
        sum(nu_he_pg_demais_proj) nu_he_pg_demais_proj,
        sum(nu_valor_he_pg_285) nu_valor_he_pg_285,
        sum(nu_valor_he_pg_296) nu_valor_he_pg_296,
        sum(nu_valor_he_pg_302) nu_valor_he_pg_302,
        sum(nu_valor_he_pg_demais_proj) nu_valor_he_pg_demais_proj,
        sum(nu_he_comp_284) nu_he_comp_284,
        sum(nu_valor_he_comp_284) nu_valor_he_comp_284,
        sum(nu_total_he) nu_total_he,
        sum(nu_valor_total_he) nu_valor_total_he
        FROM (
            SELECT CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.no_sigla_area
                ELSE coo.no_sigla_area
            END as no_sigla_coord,
            CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.de_area
                ELSE coo.de_area
            END as de_coord,
            CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.nu_area
                ELSE coo.nu_area
            END as nu_area,
            sum(nu_he_pg_285) nu_he_pg_285,
            sum(nu_he_pg_296) nu_he_pg_296,
            sum(nu_he_pg_302) nu_he_pg_302,
            sum(nu_he_pg_demais_proj) nu_he_pg_demais_proj,
            sum(nu_valor_he_pg_285) nu_valor_he_pg_285,
            sum(nu_valor_he_pg_296) nu_valor_he_pg_296,
            sum(nu_valor_he_pg_302) nu_valor_he_pg_302,
            sum(nu_valor_he_pg_demais_proj) nu_valor_he_pg_demais_proj,
            sum(nu_he_comp_284) nu_he_comp_284,
            sum(nu_valor_he_comp_284) nu_valor_he_comp_284,
            sum(nu_total_he) nu_total_he,
            sum(nu_valor_total_he) nu_valor_total_he
            FROM painel.hora_extra he
            JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
            JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
            JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
            JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
            WHERE he.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
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
            GROUP BY no_sigla_coord, de_coord, are.nu_area, coo.nu_area
            ORDER BY coo.no_sigla_area
        ) dados
        GROUP BY dados.nu_area, dados.no_sigla_coord, dados.de_coord
        ORDER BY dados.no_sigla_coord";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getExportacao($arrParametros)
    {
        $dtLDia = $this->getUltimoDiaMes('01/' . $arrParametros['dt_referencia']);
        if (isset($arrParametros['empregado'])) {
            // Se tiver empregado, pegamos a consulta geral
            $rs = $this->getConsultaGeral('01/' . $arrParametros['dt_referencia'], $dtLDia[0]['date']);
        } else {
            if (isset($arrParametros['coordenacao']) && ! isset($arrParametros['equipe'])) {
                $rs = $this->getConsultaCoordenacao('01/' . $arrParametros['dt_referencia'], $dtLDia);
            } else {
                $rs = $this->getConsultaCoordenacaoEquipe('01/' . $arrParametros['dt_referencia'], $dtLDia);
            }
        }
        return $rs;
    }

    private function getConsultaGeral($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT fun.no_funcionario,
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
        his.nu_area, nu_he_pg_285,
        he.nu_he_pg_296, he.nu_he_pg_302,
        he.nu_he_pg_demais_proj, nu_valor_he_pg_285,
        he.nu_valor_he_pg_296, he.nu_valor_he_pg_302,
        he.nu_valor_he_pg_demais_proj, he.nu_he_comp_284,
        he.nu_valor_he_comp_284, he.nu_valor_he_comp_284,
        he.nu_total_he, he.nu_valor_total_he
        FROM painel.hora_extra he
        JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE he.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
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
        /* data de inicio em alguma equipe mais proxima do fim do mês*/
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
        )
        ORDER BY fun.no_funcionario";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getExportacaoAnual($ano)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT  to_char(he.dt_referencia, 'MM/YYYY') dt_referencia,
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
            his.nu_area, nu_he_pg_285, he.nu_he_pg_296, he.nu_he_pg_302,
            he.nu_he_pg_demais_proj, nu_valor_he_pg_285, he.nu_valor_he_pg_296,
            he.nu_valor_he_pg_302, he.nu_valor_he_pg_demais_proj, he.nu_he_comp_284,
            he.nu_valor_he_comp_284, he.nu_valor_he_comp_284, he.nu_total_he,
            he.nu_valor_total_he
            FROM painel.hora_extra he
            JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
            JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
            JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
            JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
            WHERE to_char(he.dt_referencia, 'YYYY') = '{$ano}'
            AND he.dt_referencia >= his.dt_inicio
            AND (his.dt_fim is null OR he.dt_referencia <= his.dt_fim)
            ORDER BY he.dt_referencia, fun.no_funcionario";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getCoordTotais($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT  data.dt_referencia  as dt,
        sum(nu_total_he) horas,
        sum(nu_valor_total_he) valor,
        meta_hora_extra.valor meta
        from(
            SELECT  no_sigla_coord,
            de_coord,
            dt_referencia,
            sum(nu_total_he) nu_total_he,
            sum(nu_valor_total_he) nu_valor_total_he
            FROM (
                SELECT CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                    THEN are.no_sigla_area
                    ELSE coo.no_sigla_area
                END as no_sigla_coord,
                CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                    THEN are.de_area
                    ELSE coo.de_area
                END as de_coord,
                CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                    THEN are.nu_area
                    ELSE coo.nu_area
                END as nu_area,
                sum(nu_total_he) nu_total_he,
                sum(nu_valor_total_he) nu_valor_total_he,
                he.dt_referencia
                FROM painel.hora_extra he
                JOIN painel.funcionario_sipti fun
                ON (he.nu_empregado = fun.nu_funcionario)
                JOIN painel.hstro_funcionario_area_sipti his
                ON (fun.nu_funcionario = his.nu_funcionario)
                JOIN painel.area_sipti are
                ON (his.nu_area = are.nu_area)
                JOIN painel.area_sipti coo
                ON (are.nu_area_vinculada = coo.nu_area)
                WHERE he.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
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
                GROUP BY no_sigla_coord,
                    de_coord,
                    are.nu_area,
                    coo.nu_area,
                    he.dt_referencia
                ORDER BY coo.no_sigla_area
            ) dados
            GROUP BY dados.nu_area,
            dados.no_sigla_coord,
            dados.de_coord,
            dados.dt_referencia
            ORDER BY dados.no_sigla_coord
        )data
        JOIN painel.meta_hora_extra ON (data.dt_referencia = meta_hora_extra.dt_referencia)
        GROUP BY data.dt_referencia , meta_hora_extra.valor";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function EmpregadosPorMes($primeiroDia, $ultimoDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT
        fun.no_funcionario
        , are.no_sigla_area as area
        , his.nu_area
        , CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.no_sigla_area
            ELSE coo.no_sigla_area
        END as no_sigla_coord
        , CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.de_area
            ELSE coo.de_area
        END as de_coord
        , CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
            THEN are.nu_area
            ELSE coo.nu_area
        END as nu_coord
        , nu_he_pg_285
        , he.nu_he_pg_296
        , he.nu_he_pg_302
        , he.nu_he_pg_demais_proj
        , nu_valor_he_pg_285
        , he.nu_valor_he_pg_296
        , he.nu_valor_he_pg_302
        , he.nu_valor_he_pg_demais_proj
        , he.nu_he_comp_284
        , he.nu_valor_he_comp_284
        , he.nu_valor_he_comp_284
        , he.nu_total_he
        , he.nu_valor_total_he
        FROM painel.hora_extra he
        JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
        JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
        WHERE he.dt_referencia = to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
        AND(
            /* estava na equipe no inicio E não saiu até o fim do mês*/
            ( his.dt_inicio <= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe no inicio E não saiu até o fim do mês*/
            OR( his.dt_inicio >= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                AND(his.dt_inicio <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe E saiu até o fim do mês*/
            OR( his.dt_inicio <= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                AND his.dt_fim >= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
            )
        )
        /* data de inicio em alguma equipe mais proxima do fim do mês */
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD')
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
        ORDER BY fun.no_funcionario";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function testeGeral($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT
            SUM(nu_valor_total_he),
            dt_referencia
        FROM
            painel.hora_extra
        WHERE dt_referencia > '{$dtReferencia}'::DATE
        GROUP BY
            dt_referencia
        ORDER BY dt_referencia";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function Revalida($primeiroDia, $ultimoDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT * FROM(
        SELECT
            fun.no_matricula_caixa,
            fun.no_funcionario,
            CASE WHEN( ara.nu_area = 259 OR ara.nu_area_vinculada = 259 )THEN
                ara.no_sigla_area
            ELSE
                coo.no_sigla_area
            END AS coord,
            TO_CHAR(( he.nu_he_comp_284 || ' minutes')::interval, 'HH24:MI') as horas_284,
            TO_CHAR(( he.nu_he_pg_285 || ' minutes')::interval, 'HH24:MI') as horas_285,
            he.nu_valor_he_comp_284 as valor_284,
            he.nu_valor_he_pg_285 as valor_285,
            TO_CHAR(( he.nu_total_he || ' minutes')::interval, 'HH24:MI') as total_horas,
            he.nu_valor_total_he as total_valor
        FROM
        painel.hora_extra he
        JOIN painel.funcionario_sipti fun ON he.nu_empregado = fun.nu_funcionario
        JOIN painel.area_sipti ara ON fun.nu_area = ara.nu_area
        JOIN painel.area_sipti coo ON ara.nu_area_vinculada = coo.nu_area
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        WHERE he.dt_referencia = to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
            AND he.nu_total_he >=10
            AND(
                /* estava na equipe no inicio E não saiu até o fim do mês*/
                ( his.dt_inicio <= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe no inicio E não saiu até o fim do mês*/
                OR( his.dt_inicio >= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                    AND(his.dt_inicio <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe E saiu até o fim do mês*/
                OR( his.dt_inicio <= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                    AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD'))
                    AND his.dt_fim >= to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
                )
            )
            /* data de inicio em alguma equipe mais proxima do fim do mês */
            AND his.dt_inicio = (
                SELECT MAX(hist.dt_inicio)
                FROM painel.hstro_funcionario_area_sipti AS hist
                WHERE hist.nu_funcionario = fun.nu_funcionario
                AND hist.dt_inicio <= to_timestamp('{$ultimoDia}', 'YYYY-MM-DD')
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
            ORDER BY fun.no_funcionario
        ) dados WHERE coord not in
        (
            SELECT met.coord
            FROM painel.meta_hora_extra_coord AS met
            WHERE met.dt_referencia = to_timestamp('{$primeiroDia}', 'DD/MM/YYYY')
            AND met.meta > 0
            ORDER BY coord
        )
        AND coord != 'CEDESBR701'
        AND coord != 'CEDESBR800'";
        $rs = $db->fetchAll($sql);
        return $rs;
    }
}

class Application_Model_HoraExtraSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "hora_extra";

    protected $_primary = "id";
}
