<?php

class Application_Model_HoraExtraAcumulado
{

    protected $_nuEmpregado;

    protected $_dtReferencia;

    protected $_TotalHe;

    protected $_ValorHe;

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

    function getNuEmpregado()
    {
        return $this->_nuEmpregado;
    }

    function getDtReferencia()
    {
        return $this->_dtReferencia;
    }

    function getTotalHe()
    {
        return $this->_TotalHe;
    }

    function getValorHe()
    {
        return $this->_ValorHe;
    }

    function setNuEmpregado($nuEmpregado)
    {
        $this->_nuEmpregado = $nuEmpregado;
    }

    function setDtReferencia($dtReferencia)
    {
        $this->_dtReferencia = $dtReferencia;
    }

    function setTotalHe($nuTotalHe)
    {
        $this->_TotalHe = $nuTotalHe;
    }

    function setValorHe($nuValorTotalHe)
    {
        $this->_ValorHe = $nuValorTotalHe;
    }
}
