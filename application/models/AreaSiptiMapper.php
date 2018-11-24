<?php

class Application_Model_AreaSiptiMapper
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
            $this->setDbTable('Application_Model_AreaSiptiSchema');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_AreaSipti $objAreaSipti)
    {
        $data = array(
            'nu_area' => $objAreaSipti->getNuArea(),
            'nu_tipo_area' => $objAreaSipti->getNuTipoArea(),
            'nu_area_vinculada' => $objAreaSipti->getNuAreaVinculada(),
            'nu_func_titular' => $objAreaSipti->getNuFuncTitular(),
            'nu_func_eventual' => $objAreaSipti->getNuFuncEventual(),
            'de_area' => $objAreaSipti->getDeArea(),
            'no_sigla_area' => $objAreaSipti->getNoSiglaArea(),
            'co_tipo_atuacao' => $objAreaSipti->getCoTipoAtuacao(),
            'nu_centro_custo' => $objAreaSipti->getNuCentroCusto(),
            'no_cx_postal' => $objAreaSipti->getNoCxPostal(),
            'de_area_negocio' => $objAreaSipti->getDeAreaNegocio(),
            'nu_area_absorvedora' => $objAreaSipti->getNuAreaAbsorvedora(),
            'nu_categoria_area' => $objAreaSipti->getNuCategoriaArea()
        );

        if ($objAreaSipti->getIcAreaAtiva()) {
            $data['ic_area_ativa'] = 1;
        } else {
            $data['ic_area_ativa'] = 0;
        }

        $this->getDbTable()->insert($data);
    }

    public function deleteAll()
    {
        $sql = "TRUNCATE TABLE painel.area_sipti CASCADE";

        $query = $this->getDbTable()
            ->getAdapter()
            ->query($sql);
        $query->execute();
    }

    public function fetchAllFromSipti()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = "SELECT * from ptism001.gprtb006_area";

        return $db->fetchAll($select);
    }

    public function cargaSipti()
    {
        $sql = "INSERT INTO painel.area_sipti
            (SELECT * FROM ptism001.gprtb006_area);";

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


    public function getCoordEquipe($inicioMes, $fimMes)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = "SELECT
                      CASE WHEN (equipe.nu_area = 259 OR equipe.nu_area_vinculada = 259)
                        THEN equipe.no_sigla_area
                        ELSE coord.no_sigla_area
                      END as coord,
                      equipe.no_sigla_area AS equipe
                    FROM
                      painel.area_sipti AS coord,
                      painel.area_sipti AS equipe
                    WHERE
                      equipe.nu_area_vinculada = coord.nu_area AND
                      (equipe.nu_area_vinculada = 259 OR coord.nu_area_vinculada = 259) AND
                      equipe.no_sigla_area IN
                  (
                  SELECT DISTINCT

                      area_table.no_sigla_area
                    FROM
                      painel.ligacoes,
                      painel.funcionario_sipti,
                      painel.hstro_funcionario_area_sipti,
                      painel.hist_ramal_sitel,
                      painel.ramal_sitel,
                      painel.area_sipti as area_table,
                      painel.area_sipti as coordenacao_table
                    WHERE
                        ligacoes.mes_referencia BETWEEN '{$inicioMes}' AND '{$fimMes}' AND
                        ligacoes.numero::text = REPLACE(ramal_sitel.ramal, '(61) ', '')  AND
                        ramal_sitel.id = hist_ramal_sitel.ramal_sitel_id AND
                        hist_ramal_sitel.data_inicio = (
                  	SELECT MAX(hist.data_inicio)
                  	FROM painel.hist_ramal_sitel AS hist, painel.funcionario_sipti AS func
                  	WHERE hist.matricula = func.no_matr_func
                  	AND hist.ramal_sitel_id = ramal_sitel.id
                  	AND hist.data_inicio <= '{$fimMes}'
                        ) AND
                        hist_ramal_sitel.matricula = funcionario_sipti.no_matr_func AND
                        funcionario_sipti.nu_funcionario = hstro_funcionario_area_sipti.nu_funcionario AND
                        hstro_funcionario_area_sipti.nu_area = area_table.nu_area AND
                        area_table.nu_area_vinculada = coordenacao_table.nu_area AND
                        hstro_funcionario_area_sipti.dt_inicio = (
                  	SELECT MAX(hist.dt_inicio)
                  	FROM painel.hstro_funcionario_area_sipti AS hist
                  	WHERE hist.nu_funcionario = funcionario_sipti.nu_funcionario
                  	AND hist.dt_inicio <= '{$fimMes}'
                        )
                      ORDER BY no_sigla_area
                  )
                    ORDER BY
                      coord, equipe ASC;
                  ";

        $rs = $db->fetchAll($select);

        $coord = [];
        for ($i = 0; $i < sizeof($rs); $i++){
            if (isset($coord[$rs[$i]['coord']])) {
                array_push($coord[$rs[$i]['coord']], $rs[$i]['equipe']);
            } else {
                $coord[$rs[$i]['coord']] = [$rs[$i]['equipe']];
            }
        }

        return $coord;
    }

    public function fetchById($nuArea)
    {
        $resultSet = $this->getDbTable()->fetchAll("nu_area = '{$nuArea}'");

        $entry = new Application_Model_AreaSipti();

        foreach ($resultSet as $row) {

            $entry = new Application_Model_AreaSipti();
            $entry->setNuArea($row->nu_area);
            $entry->setNoSiglaArea($row->no_sigla_area);
        }

        return $entry;
    }
}

class Application_Model_AreaSiptiSchema extends Zend_Db_Table_Abstract
{

    protected $_schema = "painel";

    protected $_name = "area_sipti";

    protected $_primary = "nu_area";
}
