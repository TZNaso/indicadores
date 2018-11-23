<?php

class Application_Model_FuncaoSipti
{

    protected $_nuFuncao;

    protected $_deFuncao;

    protected $_nuTipoFuncao;

    protected $_icFuncaoAtiva;

    protected $_stcoFncoCxa;

    protected $_cargaHoraria;

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
            throw new Exception('Propriedade do FuncaoSipti inválida');
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

    function getNuFuncao()
    {
        return $this->_nuFuncao;
    }

    function getDeFuncao()
    {
        return $this->_deFuncao;
    }

    function getNuTipoFuncao()
    {
        return $this->_nuTipoFuncao;
    }

    function getIcFuncaoAtiva()
    {
        return $this->_icFuncaoAtiva;
    }

    function getStcoFncoCxa()
    {
        return $this->_stcoFncoCxa;
    }

    function getCargaHoraria()
    {
        return $this->_cargaHoraria;
    }

    function setNuFuncao($nuFuncao)
    {
        $this->_nuFuncao = $nuFuncao;
    }

    function setDeFuncao($deFuncao)
    {
        $this->_deFuncao = $deFuncao;
    }

    function setNuTipoFuncao($nuTipoFuncao)
    {
        $this->_nuTipoFuncao = $nuTipoFuncao;
    }

    function setIcFuncaoAtiva($icFuncaoAtiva)
    {
        $this->_icFuncaoAtiva = $icFuncaoAtiva;
    }

    function setStcoFncoCxa($stcoFncoCxa)
    {
        $this->_stcoFncoCxa = $stcoFncoCxa;
    }

    function setCargaHoraria($cargaHoraria)
    {
        $this->_cargaHoraria = $cargaHoraria;
    }
}
