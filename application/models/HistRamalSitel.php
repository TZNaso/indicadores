<?php

class Application_Model_HistRamalSitel
{

    protected $_matricula;

    protected $_nome;

    protected $_data_inicio;

    protected $_data_fim;

    protected $_ramal_sitel_id;

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
            throw new Exception('Propriedade de Ligacoes inválida');
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

    function getMatricula() {
        return $this->_matricula;
    }

    function getNome() {
        return $this->_nome;
    }

    function getData_inicio() {
        return $this->_data_inicio;
    }

    function getData_fim() {
        return $this->_data_fim;
    }

    function getRamal_sitel_id() {
        return $this->_ramal_sitel_id;
    }

    function setMatricula($matricula) {
        $this->_matricula = $matricula;
    }

    function setNome($nome) {
        $this->_nome = $nome;
    }

    function setData_inicio($data_inicio) {
        $this->_data_inicio = $data_inicio;
    }

    function setData_fim($data_fim) {
        $this->_data_fim = $data_fim;
    }

    function setRamal_sitel_id($ramal_sitel_id) {
        $this->_ramal_sitel_id = $ramal_sitel_id;
    }
}
