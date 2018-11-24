<?php

class Application_Model_TipoAreaSipti
{

    protected $_nuTipoArea;

    protected $_deTipoArea;

    protected $_icAtivo;

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
            throw new Exception('Propriedade do TipoArea inválida');
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

    function getNuTipoArea()
    {
        return $this->_nuTipoArea;
    }

    function getDeTipoArea()
    {
        return $this->_deTipoArea;
    }

    function getIcAtivo()
    {
        return $this->_icAtivo;
    }

    function setNuTipoArea($nuTipoArea)
    {
        $this->_nuTipoArea = $nuTipoArea;
    }

    function setDeTipoArea($deTipoArea)
    {
        $this->_deTipoArea = $deTipoArea;
    }

    function setIcAtivo($icAtivo)
    {
        $this->_icAtivo = $icAtivo;
    }
}
