<?php

class Application_Model_CargoSipti
{

    protected $_nuCargo;

    protected $_nuTipoCargo;

    protected $_noCargo;

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
            throw new Exception('Propriedade do CargoSipti inválida');
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

    function getNuCargo()
    {
        return $this->_nuCargo;
    }

    function getNuTipoCargo()
    {
        return $this->_nuTipoCargo;
    }

    function getNoCargo()
    {
        return $this->_noCargo;
    }

    function setNuCargo($nuCargo)
    {
        $this->_nuCargo = $nuCargo;
    }

    function setNuTipoCargo($nuTipoCargo)
    {
        $this->_nuTipoCargo = $nuTipoCargo;
    }

    function setNoCargo($noCargo)
    {
        $this->_noCargo = $noCargo;
    }
}
