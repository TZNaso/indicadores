<?php

class Application_Model_OrganogramaMapper
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
            $this->setDbTable('Application_Model_DbTable_HoraExtraAcumulado');
        }
        return $this->_dbTable;
    }

    public function getCoords()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        arr.no_sigla_area as label,
        arr.de_area as equipe,
        fun.no_funcionario as nome
        FROM
        painel.area_sipti arr
        JOIN painel.funcionario_sipti fun
        ON arr.nu_func_titular = fun.nu_funcionario
        WHERE
        ic_area_ativa = 't'
        AND nu_area_vinculada = 259
        OR arr.nu_area = 259
        ORDER BY
        no_sigla_area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getSubCoords($value)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        arr.no_sigla_area as label,
        fun.no_arquivo_foto as foto,
        fun.no_funcionario as nome,
        fun.no_matricula_caixa as matricula
        FROM
        painel.area_sipti AS coo
        JOIN painel.area_sipti AS arr ON coo.nu_area = arr.nu_area_vinculada
        JOIN painel.funcionario_sipti AS fun ON fun.nu_funcionario = arr.nu_func_titular
        WHERE
        coo.no_sigla_area = '{$value}'
        AND arr.nu_area_vinculada = coo.nu_area
        AND arr.ic_area_ativa = 't'
        ORDER BY
        arr.no_sigla_area";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getFuncCoord($value)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        fun.no_funcionario as nome,
        fun.no_arquivo_foto as foto,
        cao.de_funcao as label
        FROM
        painel.funcionario_sipti AS fun
        JOIN painel.area_sipti as arr on fun.nu_area = arr.nu_area
        join painel.funcao_sipti as cao on fun.nu_funcao = cao.nu_funcao
        WHERE
        fun.ic_ativo_redea = 't'
        AND  arr.no_sigla_area = '{$value}'
        ORDER BY fun.no_funcionario";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getRoot($value)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        fun.no_funcionario as nome,
        fun.no_arquivo_foto as foto,
        arr.no_sigla_area as label,
        arr.de_area
        FROM
        painel.funcionario_sipti AS fun
        JOIN painel.area_sipti AS arr ON fun.nu_funcionario = arr.nu_func_titular
        WHERE
        arr.no_sigla_area = '{$value}'
        AND fun.ic_ativo_redea = 't'";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getLegend($value)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT count(*) total from (
        SELECT
        fun.no_matr_func,
        fun.no_funcionario,
        func.de_funcao,
        fun.ic_ativo_redea,
        CASE
        WHEN(
        arr.nu_area = 259
        OR arr.nu_area_vinculada = 259
        )THEN
        arr.no_sigla_area
        ELSE
        coo.no_sigla_area
        END AS cti,
        arr.no_sigla_area AS segmento
        FROM
        ptism001.gprtb001_funcionario AS fun
        JOIN ptism001.gprtb009_funcao AS func ON func.nu_funcao = fun.nu_funcao
        JOIN ptism001.gprtb006_area AS arr ON fun.nu_area = arr.nu_area
        JOIN ptism001.gprtb006_area AS coo ON arr.nu_area_vinculada = coo.nu_area
        WHERE
        fun.ic_ativo_redea = 't'
        AND ( arr.nu_area = '{$value}' OR arr.nu_area_vinculada = '{$value}' )
        ORDER BY
        segmento,
        fun.no_funcionario
         ) as empregados";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function getNuarea($value)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT nu_area FROM ptism001.gprtb006_area WHERE no_sigla_area = '{$value}' and ic_area_ativa = 't'";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function countTotal()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT count(*) total from (
        SELECT
        fun.no_matricula_caixa,
        fun.no_matr_func,
        fun.no_funcionario,
        cao.de_funcao,
        arr.no_sigla_area,
        CASE WHEN( arr.nu_area = 259 OR arr.nu_area_vinculada = 259 )THEN
        arr.no_sigla_area
        ELSE
        coo.no_sigla_area
        END AS coord,
        fun.no_arquivo_foto
        FROM
        ptism001.gprtb001_funcionario as fun
        JOIN ptism001.gprtb006_area arr on fun.nu_area = arr.nu_area
        JOIN ptism001.gprtb009_funcao cao on fun.nu_funcao = cao.nu_funcao
        JOIN ptism001.gprtb006_area coo on arr.nu_area_vinculada = coo.nu_area
        WHERE
        fun.ic_ativo_redea = 't'
        AND fun.nu_area in (
        SELECT nu_area
        FROM ptism001.gprtb006_area
        WHERE nu_area_vinculada IN(
        SELECT nu_area
        FROM ptism001.gprtb006_area
        WHERE nu_area_vinculada = 259
        )
        UNION
        SELECT nu_area
        FROM ptism001.gprtb006_area
        WHERE nu_area = 259
        OR nu_area_vinculada = 259
        )
        ORDER BY  fun.no_funcionario
        ) as empregados";
        $rs = $db->fetchAll($select);
        return $rs;
    }

    public function legRoot()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
        arr.de_area,
        fun.no_funcionario
        FROM
        ptism001.gprtb006_area AS arr
        JOIN ptism001.gprtb001_funcionario AS fun ON fun.nu_funcionario = arr.nu_func_titular
        WHERE arr.nu_area = 259";
        $rs = $db->fetchAll($select);
        return $rs;
    }
}
