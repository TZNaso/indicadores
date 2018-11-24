<?php

class Application_Model_OcorrenciasSipon
{

    protected $_nuEmpregado;

    protected $_dtReferencia;

    protected $_nuCodigoFg;

    protected $_noFg;

    protected $_qt56;

    protected $_qt57;

    protected $_qt58;

    protected $_qt70;

    protected $_qt195;

    protected $_qt19;

    protected $_qt20;

    protected $_qtBloqueio;

    protected $_qtTotalOcorrencias;

    protected $_qtTotalPontosUtilizados;

    protected $_qtLimite;

    protected $_qt53;

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
            throw new Exception('Propriedade do OcorrenciasSipon inválida');
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

    function getNuEmpregado()
    {
        return $this->_nuEmpregado;
    }

    function getDtReferencia()
    {
        return $this->_dtReferencia;
    }

    function getNuCodigoFg()
    {
        return $this->_nuCodigoFg;
    }

    function getNoFg()
    {
        return $this->_noFg;
    }

    function getQt56()
    {
        return $this->_qt56;
    }

    function getQt57()
    {
        return $this->_qt57;
    }

    function getQt58()
    {
        return $this->_qt58;
    }

    function getQt70()
    {
        return $this->_qt70;
    }

    function getQt195()
    {
        return $this->_qt195;
    }

    function getQt19()
    {
        return $this->_qt19;
    }

    function getQt20()
    {
        return $this->_qt20;
    }

    function getQtBloqueio()
    {
        return $this->_qtBloqueio;
    }

    function getQtTotalOcorrencias()
    {
        return $this->_qtTotalOcorrencias;
    }

    function getQtTotalPontosUtilizados()
    {
        return $this->_qtTotalPontosUtilizados;
    }

    function getQtLimite()
    {
        return $this->_qtLimite;
    }

    function getQt53()
    {
        return $this->_qt53;
    }

    function setNuEmpregado($nuEmpregado)
    {
        $this->_nuEmpregado = $nuEmpregado;
    }

    function setDtReferencia($dtReferencia)
    {
        $this->_dtReferencia = $dtReferencia;
    }

    function setNuCodigoFg($nuCodigoFg)
    {
        $this->_nuCodigoFg = $nuCodigoFg;
    }

    function setNoFg($noFg)
    {
        $this->_noFg = $noFg;
    }

    function setQt56($qt56)
    {
        $this->_qt56 = $qt56;
    }

    function setQt57($qt57)
    {
        $this->_qt57 = $qt57;
    }

    function setQt58($qt58)
    {
        $this->_qt58 = $qt58;
    }

    function setQt70($qt70)
    {
        $this->_qt70 = $qt70;
    }

    function setQt195($qt195)
    {
        $this->_qt195 = $qt195;
    }

    function setQt19($qt19)
    {
        $this->_qt19 = $qt19;
    }

    function setQt20($qt20)
    {
        $this->_qt20 = $qt20;
    }

    function setQtBloqueio($qtBloqueio)
    {
        $this->_qtBloqueio = $qtBloqueio;
    }

    function setQtTotalOcorrencias($qtTotalOcorrencias)
    {
        $this->_qtTotalOcorrencias = $qtTotalOcorrencias;
    }

    function setQtTotalPontosUtilizados($qtTotalPontosUtilizados)
    {
        $this->_qtTotalPontosUtilizados = $qtTotalPontosUtilizados;
    }

    function setQtLimite($qtLimite)
    {
        $this->_qtLimite = $qtLimite;
    }

    function setQt53($qt53)
    {
        $this->_qt53 = $qt53;
    }
}
