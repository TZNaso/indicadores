<?php

class Application_Model_UnivTrilhaFundamental
{

    protected $_nuEmpregado;

    protected $_dtReferencia;

    protected $_noMatrFunc;

    protected $_noFuncionario;

    protected $_passosTrilhados;

    protected $_passosTotal;

    protected $_passosPorcentagem;

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
            throw new Exception('Propriedade da trilha fundamental inválida');
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

    function getNoMatrFunc()
    {
        return $this->_noMatrFunc;
    }

    function getNoFuncionario()
    {
        return $this->_noFuncionario;
    }

    function getPassosTrilhados()
    {
        return $this->_passosTrilhados;
    }

    function getPassosTotal()
    {
        return $this->_passosTotal;
    }

    function getPassosPorcentagem()
    {
        return $this->_passosPorcentagem;
    }

    function setNuEmpregado($nuEmpregado)
    {
        $this->_nuEmpregado = $nuEmpregado;
    }

    function setDtReferencia($dtReferencia)
    {
        $this->_dtReferencia = $dtReferencia;
    }

    function setNoMatrFunc($noMatrFunc)
    {
        $this->_noMatrFunc = $noMatrFunc;
    }

    function setNoFuncionario($noFuncionario)
    {
        $this->_noFuncionario = $noFuncionario;
    }

    function setPassosTrilhados($passosTrilhados)
    {
        $this->_passosTrilhados = $passosTrilhados;
    }

    function setPassosTotal($passosTotal)
    {
        $this->_passosTotal = $passosTotal;
    }

    function setPassosPorcentagem($passosPorcentagem)
    {
        $this->_passosPorcentagem = $passosPorcentagem;
    }
}
