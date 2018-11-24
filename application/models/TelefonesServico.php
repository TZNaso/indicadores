<?php

class Application_Model_TelefonesServico
{

    protected $_id;

    protected $_ramal;

    protected $_numeroServico;

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
            throw new Exception('Propriedade de Telefone Servico inválida');
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

    function getRamal()
    {
        return $this->_ramal;
    }

    function getNumeroServico()
    {
        return $this->_numeroServico;
    }

    function setRamal($ramal)
    {
        $this->_ramal = $ramal;
    }

    function setNumeroServico($numeroServico)
    {
        $this->_numeroServico = $numeroServico;
    }
}
