<?php

class Application_Model_ParametroSistema
{

    protected $_nuParametroSistema;

    protected $_noChaveParametro;

    protected $_noValorParametro;

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || ! method_exists($this, $method)) {
            throw new Exception('Propriedade do ParametroSistema inválida');
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

    function getNuParametroSistema()
    {
        return $this->_nuParametroSistema;
    }

    function getNoChaveParametro()
    {
        return $this->_noChaveParametro;
    }

    function getNoValorParametro()
    {
        return $this->_noValorParametro;
    }

    function setNuParametroSistema($_nuParametroSistema)
    {
        $this->_nuParametroSistema = $_nuParametroSistema;
    }

    function setNoChaveParametro($_noChaveParametro)
    {
        $this->_noChaveParametro = $_noChaveParametro;
    }

    function setNoValorParametro($_noValorParametro)
    {
        $this->_noValorParametro = $_noValorParametro;
    }
}
