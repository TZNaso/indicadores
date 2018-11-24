<?php

class Application_Model_HoraExtra
{

    protected $_nuEmpregado;

    protected $_dtReferencia;

    protected $_nuCodigoFg;

    protected $_noFg;

    protected $_nuHePg285;

    protected $_nuHePg296;

    protected $_nuHePg302;

    protected $_nuHePgDemaisProj;

    protected $_nuValorHePg285;

    protected $_nuValorHePg296;

    protected $_nuValorHePg302;

    protected $_nuValorHePgDemaisProj;

    protected $_nuHeComp284;

    protected $_nuValorHeComp284;

    protected $_nuTotalHe;

    protected $_nuValorTotalHe;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || ! method_exists($this, $method)) {
            throw new Exception('Propriedade do HoraExtra inválida');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);

        foreach ($options as $key => $value) {

            $arrAux = explode("_", $key);

            if (count($arrAux) == 1) {

                $method = 'set' . ucfirst($arrAux[0]);
            } else {

                $method = 'set';

                foreach ($arrAux as $keyAux => $valueAux) {

                    $method .= ucfirst($arrAux[$keyAux]);
                }
            }

            if (in_array($method, $methods)) {

                $this->$method($value);
            }
        }
        return $this;
    }

    public function getNuEmpregado()
    {
        return $this->_nuEmpregado;
    }

    public function getDtReferencia()
    {
        return $this->_dtReferencia;
    }

    public function getNuCodigoFg()
    {
        return $this->_nuCodigoFg;
    }

    public function getNoFg()
    {
        return $this->_noFg;
    }

    public function getNuHePg285()
    {
        return $this->_nuHePg285;
    }

    public function getNuHePg296()
    {
        return $this->_nuHePg296;
    }

    public function getNuHePg302()
    {
        return $this->_nuHePg302;
    }

    public function getNuHePgDemaisProj()
    {
        return $this->_nuHePgDemaisProj;
    }

    public function getNuValorHePg285()
    {
        return $this->_nuValorHePg285;
    }

    public function getNuValorHePg296()
    {
        return $this->_nuValorHePg296;
    }

    public function getNuValorHePg302()
    {
        return $this->_nuValorHePg302;
    }

    public function getNuValorHePgDemaisProj()
    {
        return $this->_nuValorHePgDemaisProj;
    }

    public function getNuHeComp284()
    {
        return $this->_nuHeComp284;
    }

    public function getNuValorHeComp284()
    {
        return $this->_nuValorHeComp284;
    }

    public function getNuTotalHe()
    {
        return $this->_nuTotalHe;
    }

    public function getNuValorTotalHe()
    {
        return $this->_nuValorTotalHe;
    }

    public function setNuEmpregado($nuEmpregado)
    {
        $this->_nuEmpregado = $nuEmpregado;
    }

    public function setDtReferencia($dtReferencia)
    {
        $this->_dtReferencia = $dtReferencia;
    }

    public function setNuCodigoFg($nuCodigoFg)
    {
        $this->_nuCodigoFg = $nuCodigoFg;
    }

    public function setNoFg($noFg)
    {
        $this->_noFg = $noFg;
    }

    public function setNuHePg285($nuHePg285)
    {
        $this->_nuHePg285 = $nuHePg285;
    }

    public function setNuHePg296($nuHePg296)
    {
        $this->_nuHePg296 = $nuHePg296;
    }

    public function setNuHePg302($nuHePg302)
    {
        $this->_nuHePg302 = $nuHePg302;
    }

    public function setNuHePgDemaisProj($nuHePgDemaisProj)
    {
        $this->_nuHePgDemaisProj = $nuHePgDemaisProj;
    }

    public function setNuValorHePg285($nuValorHePg285)
    {
        $this->_nuValorHePg285 = $nuValorHePg285;
    }

    public function setNuValorHePg296($nuValorHePg296)
    {
        $this->_nuValorHePg296 = $nuValorHePg296;
    }

    public function setNuValorHePg302($nuValorHePg302)
    {
        $this->_nuValorHePg302 = $nuValorHePg302;
    }

    public function setNuValorHePgDemaisProj($nuValorHePgDemaisProj)
    {
        $this->_nuValorHePgDemaisProj = $nuValorHePgDemaisProj;
    }

    public function setNuHeComp284($nuHeComp284)
    {
        $this->_nuHeComp284 = $nuHeComp284;
    }

    public function setNuValorHeComp284($nuValorHeComp284)
    {
        $this->_nuValorHeComp284 = $nuValorHeComp284;
    }

    public function setTotalHe($nuTotalHe)
    {
        $this->_nuTotalHe = $nuTotalHe;
    }

    public function setValorHe($nuValorTotalHe)
    {
        $this->_nuValorTotalHe = $nuValorTotalHe;
    }
}
