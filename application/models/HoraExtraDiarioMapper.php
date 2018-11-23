<?php

class Application_Model_HoraExtraDiarioMapper
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
            $this->setDbTable('Application_Model_HoraExtraDiarioSchema');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_HoraExtraDiario $objHoraExtraDiario)
    {
        $data = array(
            'nu_funcionario' => $objHoraExtraDiario->getNuFuncionario(),
            'dt_referencia' => $objHoraExtraDiario->getDtReferencia(),
            'he_hom_pg' => $this->addZeros($objHoraExtraDiario->getHeHomPg()),
            'he_hom_bco' => $this->addZeros($objHoraExtraDiario->getHeHomBco()),
            'he_n_hom' => $this->addZeros($objHoraExtraDiario->getHeNHhom()),
            'he_total' => $this->addZeros($objHoraExtraDiario->getHeTotal())
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

    public function addZeros($value)
    {
        if (! $value) {
            $value = "0";
        }
        return $value;
    }

    public function deleteByDtReferencia($dtReferencia)
    {
        $this->getDbTable()->delete("dt_referencia = '{$dtReferencia}'");
    }

    public function getDatasDisponiveis()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT DISTINCT(to_char(dt_referencia,'DD/MM/YYYY'))
        dt_referencia, dt_referencia dt_ref
        FROM painel.hora_extra_Diario
        ORDER BY dt_ref desc";
        $rs = $db->fetchAll($select);
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

    public function getUltimoDiaMes($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT (date_trunc('MONTH',to_timestamp('{$dtReferencia}', 'YYYY-MM-DD')) + INTERVAL '1 MONTH - 1 day')::date";
        $rs = $db->fetchAll($sql);
        return $rs[0]['date'];
    }

    public function getPrimeiroDiaMes($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT (date_trunc('MONTH','{$dtReferencia}'::DATE))::date";
        $rs = $db->fetchAll($sql);
        return $rs[0]['date_trunc'];
    }

    public function nearData($dtReferencia)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "SELECT DISTINCT MAX(dt_referencia) FROM painel.hora_extra_Diario WHERE dt_referencia < '{$dtReferencia}'::DATE";
        $rs = $db->fetchAll($sql);
        return $rs[0]['max'];
    }

    public function getHEDiarioEmpregados($dtReferencia, $dtPDia, $dtLDia, $dtPositivo)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        ex.nu_funcionario,
        fun.no_funcionario,
        fun.no_matricula_caixa,
        ex.he_total,
        ex.dt_referencia,
        CASE WHEN(
            ARE .nu_area = 259
            OR ARE .nu_area_vinculada = 259
        )THEN
            ARE .no_sigla_area
        ELSE
            coo.no_sigla_area
        END AS no_sigla_coord
        FROM
            painel.hora_extra_Diario AS ex
        JOIN painel.funcionario_sipti AS fun ON (ex.nu_funcionario = fun.nu_funcionario)
        JOIN painel.hstro_funcionario_area_sipti his ON(fun.nu_funcionario = his.nu_funcionario)
        JOIN painel.area_sipti ARE ON(his.nu_area = ARE .nu_area)
        JOIN painel.area_sipti coo ON(ARE .nu_area_vinculada = coo.nu_area)
        WHERE ex.dt_referencia = to_timestamp('{$dtReferencia}', 'YYYY-MM-DD')
        AND(
            /* estava na equipe no inicio E não saiu até o fim do mês*/
            ( his.dt_inicio <= to_timestamp('{$dtPDia}', 'YYYY-MM-DD')
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe no inicio E não saiu até o fim do mês*/
            OR( his.dt_inicio >= to_timestamp('{$dtPDia}', 'YYYY-MM-DD')
                AND(his.dt_inicio <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND( his.dt_fim IS NULL OR his.dt_fim >= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
            )
            /* não estava na equipe E saiu até o fim do mês*/
            OR( his.dt_inicio <= to_timestamp('{$dtPDia}', 'YYYY-MM-DD')
                AND( his.dt_fim IS NULL OR his.dt_fim <= to_timestamp('{$dtLDia}', 'YYYY-MM-DD'))
                AND his.dt_fim >= to_timestamp('{$dtPDia}', 'YYYY-MM-DD')
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

        AND ex.nu_funcionario IN(
            SELECT
                    nu_funcionario
            FROM
                    painel.saldo_positivo
            WHERE
                    dt_referencia = '{$dtPositivo}' :: DATE
            ORDER BY
                    nu_funcionario

        )
        ORDER BY ex.nu_funcionario";
        $rs = $db->fetchAll($select);
        return $rs;
    }
}

class Application_Model_HoraExtraDiarioSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "hora_extra_diario";

    protected $_primary = array(
        "nu_funcionario",
        "dt_referencia"
    );
}
