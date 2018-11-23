<?php

class Application_Model_LigacoesOutros
{

    protected $_ramal_id;

    protected $_nome;

    protected $_matricula;

    protected $_coord;

    protected $_equipe;
    
    protected $_dt_referencia;

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
            throw new Exception('Propriedade de Ligacoes Outros inválida');
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
    
    function getRamal_id()
    {
        return $this->_ramal_id;
    }

    function getNome()
    {
        return $this->_nome;
    }

    function getMatricula()
    {
        return $this->_matricula;
    }

    function getCoord()
    {
        return $this->_coordenacao;
    }

    function getEquipe()
    {
        return $this->_equipe;
    }
    
    function getDtReferencia()
    {
        return $this->_dt_referencia;
    }

    function setRamal_id($ramal_id)
    {
        $this->_ramal_id = $ramal_id;
    }

    function setNome($nome)
    {
        $this->_nome = $nome;
    }

    function setMatricula($matricula)
    {
        $this->_matricula = $matricula;
    }

    function setCoord($coordenacao)
    {
        $this->_coordenacao = $coordenacao;
    }

    function setEquipe($equipe)
    {
        $this->_equipe = $equipe;
    }
    
    function setDtReferencia($dt_referencia)
    {
        $this->_dt_referencia = $dt_referencia;
    }
}
