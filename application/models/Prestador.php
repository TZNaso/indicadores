<?php

class Application_Model_Prestador
{

    protected $_cpf;

    protected $_status;

    protected $_matricula;

    protected $_restricao;

    protected $_lotacao;

    protected $_lotacao_fisica;

    protected $_empresa;

    protected $_tipo;

    protected $_nome;

    protected $_sexo;

    protected $_data_nascimento;

    protected $_identidade;

    protected $_orgao_emissor;
    
    protected $_nome_mae;
    
    protected $_data_referencia;

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

    function getCpf()
    {
        return $this->_cpf;
    }

    function getStatus()
    {
        return $this->_status;
    }

    function getMatricula()
    {
        return $this->_matricula;
    }

    function getRestricao()
    {
        return $this->_restricao;
    }

    function getLotacao()
    {
        return $this->_lotacao;
    }

    function getLotacao_fisica()
    {
        return $this->_lotacao_fisica;
    }

    function getEmpresa()
    {
        return $this->_empresa;
    }

    function getTipo()
    {
        return $this->_tipo;
    }

    function getNome()
    {
        return $this->_nome;
    }

    function getSexo()
    {
        return $this->_sexo;
    }

    function getData_nascimento()
    {
        return $this->_data_nascimento;
    }

    function getIdentidade()
    {
        return $this->_identidade;
    }

    function getOrgao_emissor()
    {
        return $this->_orgao_emissor;
    }

    function getNome_mae()
    {
        return $this->_nome_mae;
    }

    function getData_referencia()
    {
        return $this->_data_referencia;
    }

    function setCpf($cpf)
    {
        $this->_cpf = $cpf;
    }

    function setStatus($status)
    {
        $this->_status = $status;
    }

    function setMatricula($matricula)
    {
        $this->_matricula = $matricula;
    }

    function setRestricao($restricao)
    {
        $this->_restricao = $restricao;
    }

    function setLotacao($lotacao)
    {
        $this->_lotacao = $lotacao;
    }

    function setLotacao_fisica($lotacao_fisica)
    {
        $this->_lotacao_fisica = $lotacao_fisica;
    }

    function setEmpresa($empresa)
    {
        $this->_empresa = $empresa;
    }

    function setTipo($tipo)
    {
        $this->_tipo = $tipo;
    }

    function setNome($nome)
    {
        $this->_nome = $nome;
    }

    function setSexo($sexo)
    {
        $this->_sexo = $sexo;
    }

    function setData_nascimento($data_nascimento)
    {
        $this->_data_nascimento = $data_nascimento;
    }

    function setIdentidade($identidade)
    {
        $this->_identidade = $identidade;
    }

    function setOrgao_emissor($orgao_emissor)
    {
        $this->_orgao_emissor = $orgao_emissor;
    }

    function setNome_mae($nome_mae)
    {
        $this->_nome_mae = $nome_mae;
    }

    function setData_referencia($data_referencia)
    {
        $this->_data_referencia = $data_referencia;
    }
}
