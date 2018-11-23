<?php

class Application_Model_HoraExtraDiario
{

    protected $_nuFuncionario;

    protected $_dtReferencia;

    protected $_heHomPg;

    protected $_heHomBco;

    protected $_heNHhom;

    protected $_heTotal;

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
            throw new Exception('Propriedade do HoraExtraDiario inválida');
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

    function getNuFuncionario()
    {
        return $this->_nuFuncionario;
    }

    function getDtReferencia()
    {
        return $this->_dtReferencia;
    }

    function getHeHomPg()
    {
        return $this->_heHomPg;
    }

    function getHeHomBco()
    {
        return $this->_heHomBco;
    }

    function getHeNHhom()
    {
        return $this->_heNHhom;
    }

    function getHeTotal()
    {
        return $this->_heTotal;
    }

    function setNuFuncionario($_nuFuncionario)
    {
        $this->_nuFuncionario = $_nuFuncionario;
    }

    function setDtReferencia($_dtReferencia)
    {
        $this->_dtReferencia = $_dtReferencia;
    }

    function setHeHomogPag($HeHomPg)
    {
        $this->_heHomPg = $HeHomPg;
    }

    function setHeHomogBco($HeHomBco)
    {
        $this->_heHomBco = $HeHomBco;
    }

    function setHeNaoHomog($_heNHhom)
    {
        $this->_heNHhom = $_heNHhom;
    }

    function setTotalReal($HeTotal)
    {
        $this->_heTotal = $HeTotal;
    }
}
