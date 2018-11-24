<?php

class Application_Model_HstroFuncionarioAreaSipti
{

    protected $_nuFuncionario;

    protected $_nuArea;

    protected $_dtInicio;

    protected $_dtFim;

    protected $_deResponsavel;

    protected $_dtAtualizacao;

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
            throw new Exception('Propriedade do HstroFuncionarioAreaSipti inválida');
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

    function getNuArea()
    {
        return $this->_nuArea;
    }

    function getDtInicio()
    {
        return $this->_dtInicio;
    }

    function getDtFim()
    {
        return $this->_dtFim;
    }

    function getDeResponsavel()
    {
        return $this->_deResponsavel;
    }

    function getDtAtualizacao()
    {
        return $this->_dtAtualizacao;
    }

    function setNuFuncionario($nuFuncionario)
    {
        $this->_nuFuncionario = $nuFuncionario;
    }

    function setNuArea($nuArea)
    {
        $this->_nuArea = $nuArea;
    }

    function setDtInicio($dtInicio)
    {
        $this->_dtInicio = $dtInicio;
    }

    function setDtFim($dtFim)
    {
        $this->_dtFim = $dtFim;
    }

    function setDeResponsavel($deResponsavel)
    {
        $this->_deResponsavel = $deResponsavel;
    }

    function setDtAtualizacao($dtAtualizacao)
    {
        $this->_dtAtualizacao = $dtAtualizacao;
    }
}
