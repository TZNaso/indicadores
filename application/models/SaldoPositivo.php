<?php

class Application_Model_SaldoPositivo
{

    protected $_id;

    protected $_dtReferencia;

    protected $_nuFuncionario;

    protected $_totalMin;

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
            throw new Exception('Propriedade de saldo positivo inválida');
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

    function getID()
    {
        return $this->_id;
    }

    function getNuFuncionario()
    {
        return $this->_nuFuncionario;
    }

    function getTotalMin()
    {
        return $this->_totalMin;
    }

    function getDtReferencia()
    {
        return $this->_dtReferencia;
    }

    function setNuFuncionario($nuFuncionario)
    {
        $this->_nuFuncionario = $nuFuncionario;
    }

    function setDtReferencia($dtReferencia)
    {
        $this->_dtReferencia = $dtReferencia;
    }

    function setTtotalMin($totalMin)
    {
        $this->_totalMin = $totalMin;
    }
}
