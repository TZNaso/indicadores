<?php

class Application_Model_Ligacoes
{

    protected $_cgc;

    protected $_unidade;

    protected $_ddd;

    protected $_numero;

    protected $_nome_usuario;

    protected $_descricao;

    protected $_numero_chamado;

    protected $_hora_ocorrencia;

    protected $_duracao;

    protected $_duracao_minutos;

    protected $_valor;

    protected $_nome;

    protected $_mes_referencia;

    protected $_nr_arquivo;

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

    function getCgc()
    {
        return $this->_cgc;
    }

    function getUnidade()
    {
        return $this->_unidade;
    }

    function getDdd()
    {
        return $this->_ddd;
    }

    function getNumero()
    {
        return $this->_numero;
    }

    function getNome_usuario()
    {
        return $this->_nome_usuario;
    }

    function getDescricao()
    {
        return $this->_descricao;
    }

    function getNumero_chamado()
    {
        return $this->_numero_chamado;
    }

    function getHora_ocorrencia()
    {
        return $this->_hora_ocorrencia;
    }

    function getDuracao()
    {
        return $this->_duracao;
    }

    function getDuracao_minutos()
    {
        return $this->_duracao_minutos;
    }

    function getValor()
    {
        return $this->_valor;
    }

    function getNome()
    {
        return $this->_nome;
    }

    function getMes_referencia()
    {
        return $this->_mes_referencia;
    }

    function getNr_arquivo()
    {
        return $this->_nr_arquivo;
    }

    function setCgc($cgc)
    {
        $this->_cgc = $cgc;
    }

    function setUnidade($unidade)
    {
        $this->_unidade = $unidade;
    }

    function setDdd($ddd)
    {
        $this->_ddd = $ddd;
    }

    function setNumero($numero)
    {
        $this->_numero = $numero;
    }

    function setNome_usuario($nome_usuario)
    {
        $this->_nome_usuario = $nome_usuario;
    }

    function setDescricao($descricao)
    {
        $this->_descricao = $descricao;
    }

    function setNumero_chamado($numero_chamado)
    {
        $this->_numero_chamado = $numero_chamado;
    }

    function setHora_ocorrencia($hora_ocorrencia)
    {
        $this->_hora_ocorrencia = $hora_ocorrencia;
    }

    function setDuracao($duracao)
    {
        $this->_duracao = $duracao;
    }

    function setDuracao_minutos($duracao_minutos)
    {
        $this->_duracao_minutos = $duracao_minutos;
    }

    function setValor($valor)
    {
        $this->_valor = $valor;
    }

    function setNome($nome)
    {
        $this->_nome = $nome;
    }

    function setMes_referencia($mes_referencia)
    {
        $this->_mes_referencia = $mes_referencia;
    }

    function setNr_arquivo($_nr_arquivo)
    {
        $this->_nr_arquivo = $_nr_arquivo;
    }
}
