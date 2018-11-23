<?php

class Application_Model_AreaSipti
{

    protected $_nuArea;

    protected $_nuTipoArea;

    protected $_nuAreaVinculada;

    protected $_icAreaAtiva;

    protected $_nuFuncTitular;

    protected $_nuFuncEventual;

    protected $_deArea;

    protected $_noSiglaArea;

    protected $_coTipoAtuacao;

    protected $_nuCentroCusto;

    protected $_noCxPostal;

    protected $_deAreaNegocio;

    protected $_nuAreaAbsorvedora;

    protected $_nuCategoriaArea;

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
            throw new Exception('Propriedade do AreaSipti inválida');
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

    function getNuArea()
    {
        return $this->_nuArea;
    }

    function getNuTipoArea()
    {
        return $this->_nuTipoArea;
    }

    function getNuAreaVinculada()
    {
        return $this->_nuAreaVinculada;
    }

    function getIcAreaAtiva()
    {
        return $this->_icAreaAtiva;
    }

    function getNuFuncTitular()
    {
        return $this->_nuFuncTitular;
    }

    function getNuFuncEventual()
    {
        return $this->_nuFuncEventual;
    }

    function getDeArea()
    {
        return $this->_deArea;
    }

    function getNoSiglaArea()
    {
        return $this->_noSiglaArea;
    }

    function getCoTipoAtuacao()
    {
        return $this->_coTipoAtuacao;
    }

    function getNuCentroCusto()
    {
        return $this->_nuCentroCusto;
    }

    function getNoCxPostal()
    {
        return $this->_noCxPostal;
    }

    function getDeAreaNegocio()
    {
        return $this->_deAreaNegocio;
    }

    function getNuAreaAbsorvedora()
    {
        return $this->_nuAreaAbsorvedora;
    }

    function getNuCategoriaArea()
    {
        return $this->_nuCategoriaArea;
    }

    function setNuArea($nuArea)
    {
        $this->_nuArea = $nuArea;
    }

    function setNuTipoArea($nuTipoArea)
    {
        $this->_nuTipoArea = $nuTipoArea;
    }

    function setNuAreaVinculada($nuAreaVinculada)
    {
        $this->_nuAreaVinculada = $nuAreaVinculada;
    }

    function setIcAreaAtiva($icAreaAtiva)
    {
        $this->_icAreaAtiva = $icAreaAtiva;
    }

    function setNuFuncTitular($nuFuncTitular)
    {
        $this->_nuFuncTitular = $nuFuncTitular;
    }

    function setNuFuncEventual($nuFuncEventual)
    {
        $this->_nuFuncEventual = $nuFuncEventual;
    }

    function setDeArea($deArea)
    {
        $this->_deArea = $deArea;
    }

    function setNoSiglaArea($noSiglaArea)
    {
        $this->_noSiglaArea = $noSiglaArea;
    }

    function setCoTipoAtuacao($coTipoAtuacao)
    {
        $this->_coTipoAtuacao = $coTipoAtuacao;
    }

    function setNuCentroCusto($nuCentroCusto)
    {
        $this->_nuCentroCusto = $nuCentroCusto;
    }

    function setNoCxPostal($noCxPostal)
    {
        $this->_noCxPostal = $noCxPostal;
    }

    function setDeAreaNegocio($deAreaNegocio)
    {
        $this->_deAreaNegocio = $deAreaNegocio;
    }

    function setNuAreaAbsorvedora($nuAreaAbsorvedora)
    {
        $this->_nuAreaAbsorvedora = $nuAreaAbsorvedora;
    }

    function setNuCategoriaArea($nuCategoriaArea)
    {
        $this->_nuCategoriaArea = $nuCategoriaArea;
    }
}
