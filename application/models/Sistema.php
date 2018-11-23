<?php

class Application_Model_Sistema
{

    protected $_nu_sistema;

    protected $_de_sistema;

    protected $_no_sigla_sistema;

    protected $_de_sigla_gercia_exectva;

    protected $_de_sigla_und_resp;

    protected $_de_sigla_coord_ti;

    protected $_de_sigla_coord_proj_ti;

    protected $_de_sigla_proj_especial;

    protected $_de_carteira;

    protected $_de_alias;

    protected $_de_tipo;

    protected $_ic_estado;

    protected $_de_objetivo;

    protected $_de_beneficios_esperados;

    protected $_de_cronograma_vigente;

    protected $_de_situacao_documentacao;

    protected $_dt_ultima_atualizacao;

    protected $_de_pronto_atendimento;

    protected $_de_sit_cod_fonte;

    protected $_de_processo;

    protected $_de_cenario;

    protected $_de_fase;

    protected $_de_situacao;

    protected $_dt_modificacao;

    protected $_dt_criacao;

    protected $_dt_inicio;

    protected $_dt_conclusao;

    protected $_de_abrangencia_uso;

    protected $_de_criticidade_tecnica;

    protected $_de_criticidade_negocio;

    protected $_nu_criticidade_neg_abrang;

    protected $_nu_grau_internalizacao;

    protected $_nu_meta_independencia;

    protected $_de_ling_programacao;

    protected $_de_banco_de_dados;

    protected $_de_forma_acesso;

    protected $_de_sistema_operacional;

    protected $_de_suporte_desenv;

    protected $_de_servidor_aplicacao;

    protected $_de_servidor_web;

    protected $_de_rede;

    protected $_de_plataforma;

    protected $_de_tecnologia_suporte;

    protected $_de_componentes;

    protected $_de_integracoes;

    protected $_de_interfaces_disponiveis;

    protected $_de_autenticacao_dominio;

    protected $_de_autorizacao_dominio;

    protected $_de_certificado_digital;

    protected $_de_padroes_desenv;

    protected $_de_ambiente_prod;

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
            throw new Exception('Propriedade do Sistema inválida');
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
}
