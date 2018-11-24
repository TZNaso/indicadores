<?php

class Application_Model_UnivTrilhaFundamentalMapper
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
            $this->setDbTable('Application_Model_UnivTrilhaFundamentalSchema');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_UnivTrilhaFundamental $objUnivTrilhaFundamental)
    {
        $data = array(
            'nu_empregado' => $objUnivTrilhaFundamental->getNuEmpregado(),
            'no_matr_func' => $objUnivTrilhaFundamental->getNoMatrFunc(),
            'dt_referencia' => $objUnivTrilhaFundamental->getDtReferencia(),
            'no_funcionario' => $objUnivTrilhaFundamental->getNoFuncionario(),
            'passos_trilhados' => $objUnivTrilhaFundamental->getPassosTrilhados(),
            'passos_total' => $objUnivTrilhaFundamental->getPassosTotal(),
            'passos_porcentagem' => $objUnivTrilhaFundamental->getPassosPorcentagem()
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
        $sql = "SELECT DISTINCT dt_referencia as date FROM painel.univ_trilha_fundamental ORDER BY dt_referencia DESC;";
        $rs = $db->fetchAll($sql);
        return $rs;
    }

    public function getUltimaDataDisponivel()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT to_char(max(dt_referencia),'DD/MM/YYYY')
        as dt_referencia from painel.univ_trilha_fundamental";
        $rs = $db->fetchAll($select);
        if ($rs) {
            return $rs[0]['dt_referencia'];
        } else {
            return null;
        }
    }

    public function getDatasDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'DD/MM/YYYY'))
        dt_referencia, dt_referencia dt_ref
        FROM painel.univ_trilha_fundamental
        ORDER BY dt_ref DESC";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAnosDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'YYYY')) ano
                    FROM painel.univ_trilha_fundamental
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

    public function getTotalizacaoEmpregados($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
            area.nu_area as nu_area,
            fund.no_funcionario as nome_func ,
            area.no_sigla_area as sigla_area ,
            area.de_area as de_area,
            area.nu_area_vinculada as vinculada,
            fund.passos_porcentagem as porcentagem,
            fun.nu_area as num
            from painel.univ_trilha_fundamental as fund,
            painel.area_sipti as area,
            painel.funcionario_sipti as fun
            JOIN painel.hstro_funcionario_area_sipti his
            ON (fun.nu_funcionario = his.nu_funcionario)
            where  to_char(fund.dt_referencia,'DD/MM/YYYY')  = '{$dtReferencia}'
            AND (to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') >= his.dt_inicio
            AND (his.dt_fim is null OR to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') <= his.dt_fim))
            AND fund.nu_empregado = fun.nu_funcionario and
            fun.nu_area = area.nu_area
            order by nome_func";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getAreasPorData($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(are.no_sigla_area || ' - ' || are.de_area) area, are.nu_area
        FROM painel.area_sipti are
        JOIN painel.hstro_funcionario_area_sipti his ON (are.nu_area = his.nu_area)
        JOIN painel.funcionario_sipti fun on (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.univ_trilha_fundamental fund on (fund.nu_empregado = fun.nu_funcionario)
        WHERE fund.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
        AND (to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') >= his.dt_inicio AND (his.dt_fim is null OR to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') <= his.dt_fim)) ORDER BY area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getTotalizacaoCoordenacao($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT coordenacao,
        sum(passos_porcentagem) porcentagem_soma ,
        sum(total_funcionarios) total_funcionarios
        FROM (
            SELECT  CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.nu_area
                ELSE coo.nu_area
            END as nu_area,
            CASE WHEN (are.nu_area = 259 OR are.nu_area_vinculada = 259)
                THEN are.no_sigla_area || ' - ' || are.de_area
                ELSE coo.no_sigla_area || ' - ' || coo.de_area
            END as coordenacao,
            SUM(passos_porcentagem) passos_porcentagem,
            COUNT(nu_empregado) total_funcionarios
            FROM painel.univ_trilha_fundamental oco
            JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
            JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
            JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
            JOIN painel.area_sipti coo ON (are.nu_area_vinculada = coo.nu_area)
            WHERE oco.dt_referencia = to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
            AND(
                /* estava na equipe no inicio E não saiu até o fim do mês */
                ( his.dt_inicio <= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe no inicio E não saiu até o fim do mês */
                OR( his.dt_inicio >= to_timestamp('{$dtReferencia}', 'DD/MM/YYYY')
                    AND(his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                    AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                )
                /* não estava na equipe E saiu até o fim do mês */
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
            ORDER BY are.no_sigla_area
        ) dados
        GROUP BY dados.nu_area, dados.coordenacao
        ORDER BY dados.coordenacao";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getTotalizacaoEquipe($dtReferencia, $dtLDia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT are.no_sigla_area || ' - ' || are.de_area area,
        SUM(passos_porcentagem) porcentagem_soma,
        COUNT(*) as total_funcionarios
        FROM painel.univ_trilha_fundamental  oco
        JOIN painel.funcionario_sipti fun ON (oco.nu_empregado = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti are ON (his.nu_area = are.nu_area)
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
        /* data de inicio em alguma equipe mais proxima do fim do mês*/
        AND his.dt_inicio = (
            SELECT MAX(hist.dt_inicio)
            FROM painel.hstro_funcionario_area_sipti AS hist
            WHERE hist.nu_funcionario = fun.nu_funcionario
            AND hist.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD')
            AND hist.nu_area in (
                -- todas as equipes
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area_vinculada IN(
                    -- de todas as coordenações
                    SELECT nu_area
                    FROM painel.area_sipti
                    WHERE nu_area_vinculada = 259
                )
                -- a propria cedesbr
                UNION
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area = 259
                -- as proprias coordenações
                UNION
                SELECT nu_area
                FROM painel.area_sipti
                WHERE nu_area_vinculada = 259
            )
        )
        GROUP BY are.nu_area
        ORDER BY are.no_sigla_area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getNumCoordenacao($coordenacao)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT nu_area from painel.area_sipti where no_sigla_area = '{$coordenacao}' and ic_area_ativa = true";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getTotalizacaoEquipePorCoordenacao($dtReferencia, $coord)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT  dados.area as area,
            dados.nu_area_vinculada,
            dados.porcentagem as porcentagem_soma,
            totais.total as total_funcionarios
            from (
                SELECT are.no_sigla_area as sigla,
                are.no_sigla_area || ' - ' || are.de_area area,
                are.nu_area,
                are.nu_area_vinculada,
                SUM(passos_porcentagem) porcentagem
                FROM painel.univ_trilha_fundamental he
                JOIN painel.funcionario_sipti fun ON (he.nu_empregado = fun.nu_funcionario)
                JOIN painel.area_sipti are ON (fun.nu_area = are.nu_area)
                JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
                WHERE he.dt_referencia = '{$dtReferencia}'
                AND (to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') >= his.dt_inicio
                AND (his.dt_fim is null OR to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') <= his.dt_fim))
                GROUP BY are.nu_area
                ORDER BY are.no_sigla_area
            )dados join  (
            SELECT COUNT(*) as total, no_sigla_area FROM (
            select fun.no_funcionario , area.no_sigla_area
            from painel.univ_trilha_fundamental  as fund
            join painel.funcionario_sipti fun  on ( fun.nu_funcionario = fund.nu_empregado)
            join  painel.area_sipti  as area on(  fun.nu_area = area.nu_area)
            JOIN painel.hstro_funcionario_area_sipti his ON (fun.nu_funcionario = his.nu_funcionario)
            WHERE fund.dt_referencia = '{$dtReferencia}'
            AND (to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') >= his.dt_inicio
            AND (his.dt_fim is null OR to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') <= his.dt_fim))
            order by area.de_area, fun.no_funcionario
            )dados
            group by dados.no_sigla_area
            order by dados.no_sigla_area
           )totais
        on dados.sigla = totais.no_sigla_area where
        dados.nu_area_vinculada = '$coord' ";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getTotalizacaoEmpregadosPorEquipe($dtReferencia, $equipe)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
            his.nu_area as nu_area ,
            fund.no_funcionario as nome_func ,
            his.nu_area as sigla_area ,
            fun.no_matr_func as matricula,
            fund.passos_porcentagem as porcentagem
            from painel.univ_trilha_fundamental as fund,
            painel.area_sipti as area,
            painel.funcionario_sipti as fun
            JOIN painel.hstro_funcionario_area_sipti his
            ON (fun.nu_funcionario = his.nu_funcionario)
            where  to_char(fund.dt_referencia,'DD/MM/YYYY')  = '{$dtReferencia}'
            AND (to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') >= his.dt_inicio
            AND (his.dt_fim is null OR to_timestamp('{$dtReferencia}', 'DD/MM/YYYY') <= his.dt_fim))
            AND fund.nu_empregado = fun.nu_funcionario
          AND fun.nu_area = area.nu_area
          AND his.nu_area = '$equipe'
      order by nome_func";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getEVolucaoEmpregado($matricula)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = " SELECT * FROM painel.univ_trilha_fundamental WHERE no_matr_func = '{$matricula}' ORDER BY dt_referencia";
        $rs = $db->fetchAll($select);
        return $rs;
    }
}

class Application_Model_UnivTrilhaFundamentalSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "univ_trilha_fundamental";

    protected $_primary = array(
        "nu_empregado"
    );
}
