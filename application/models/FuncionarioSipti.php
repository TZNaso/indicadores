<?php

class Application_Model_FuncionarioSipti
{

    protected $_nuFuncionario;

    protected $_nuArea;

    protected $_nuFuncao;

    protected $_nuCargo;

    protected $_noMatrFunc;

    protected $_noMatriculaCaixa;

    protected $_noFuncionario;

    protected $_noApelido;

    protected $_icAtivoRedea;

    protected $_icSexo;

    protected $_dtAniversario;

    protected $_noArquivoFoto;

    protected $_idUsuarioOutlook;

    protected $_noEndereco;

    protected $_noBairro;

    protected $_noUf;

    protected $_nuCep;

    protected $_dtAdmissao;

    protected $_deLocalizacaoInterna;

    protected $_noLogicoMicro;

    protected $_dtNascimento;

    protected $_deResponsavel;

    protected $_dtAtualizacao;

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
            throw new Exception('Propriedade do FuncionarioSipti inválida');
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

    function getNuFuncionario()
    {
        return $this->_nuFuncionario;
    }

    function getNuArea()
    {
        return $this->_nuArea;
    }

    function getNuFuncao()
    {
        return $this->_nuFuncao;
    }

    function getNuCargo()
    {
        return $this->_nuCargo;
    }

    function getNoMatrFunc()
    {
        return $this->_noMatrFunc;
    }

    function getNoMatriculaCaixa()
    {
        return $this->_noMatriculaCaixa;
    }

    function getNoFuncionario()
    {
        return $this->_noFuncionario;
    }

    function getNoApelido()
    {
        return $this->_noApelido;
    }

    function getIcAtivoRedea()
    {
        return $this->_icAtivoRedea;
    }

    function getIcSexo()
    {
        return $this->_icSexo;
    }

    function getDtAniversario()
    {
        return $this->_dtAniversario;
    }

    function getNoArquivoFoto()
    {
        return $this->_noArquivoFoto;
    }

    function getIdUsuarioOutlook()
    {
        return $this->_idUsuarioOutlook;
    }

    function getNoEndereco()
    {
        return $this->_noEndereco;
    }

    function getNoBairro()
    {
        return $this->_noBairro;
    }

    function getNoUf()
    {
        return $this->_noUf;
    }

    function getNuCep()
    {
        return $this->_nuCep;
    }

    function getDtAdmissao()
    {
        return $this->_dtAdmissao;
    }

    function getDeLocalizacaoInterna()
    {
        return $this->_deLocalizacaoInterna;
    }

    function getNoLogicoMicro()
    {
        return $this->_noLogicoMicro;
    }

    function getDtNascimento()
    {
        return $this->_dtNascimento;
    }

    function getDeResponsavel()
    {
        return $this->_deResponsavel;
    }

    function getDtAtualizacao()
    {
        return $this->_dtAtualizacao;
    }

    function setNuFuncionario($nuFuncionario)
    {
        $this->_nuFuncionario = $nuFuncionario;
    }

    function setNuArea($nuArea)
    {
        $this->_nuArea = $nuArea;
    }

    function setNuFuncao($nuFuncao)
    {
        $this->_nuFuncao = $nuFuncao;
    }

    function setNuCargo($nuCargo)
    {
        $this->_nuCargo = $nuCargo;
    }

    function setNoMatrFunc($noMatrFunc)
    {
        $this->_noMatrFunc = $noMatrFunc;
    }

    function setNoMatriculaCaixa($noMatriculaCaixa)
    {
        $this->_noMatriculaCaixa = $noMatriculaCaixa;
    }

    function setNoFuncionario($noFuncionario)
    {
        $this->_noFuncionario = $noFuncionario;
    }

    function setNoApelido($noApelido)
    {
        $this->_noApelido = $noApelido;
    }

    function setIcAtivoRedea($icAtivoRedea)
    {
        $this->_icAtivoRedea = $icAtivoRedea;
    }

    function setIcSexo($icSexo)
    {
        $this->_icSexo = $icSexo;
    }

    function setDtAniversario($dtAniversario)
    {
        $this->_dtAniversario = $dtAniversario;
    }

    function setNoArquivoFoto($noArquivoFoto)
    {
        $this->_noArquivoFoto = $noArquivoFoto;
    }

    function setIdUsuarioOutlook($idUsuarioOutlook)
    {
        $this->_idUsuarioOutlook = $idUsuarioOutlook;
    }

    function setNoEndereco($noEndereco)
    {
        $this->_noEndereco = $noEndereco;
    }

    function setNoBairro($noBairro)
    {
        $this->_noBairro = $noBairro;
    }

    function setNoUf($noUf)
    {
        $this->_noUf = $noUf;
    }

    function setNuCep($nuCep)
    {
        $this->_nuCep = $nuCep;
    }

    function setDtAdmissao($dtAdmissao)
    {
        $this->_dtAdmissao = $dtAdmissao;
    }

    function setDeLocalizacaoInterna($deLocalizacaoInterna)
    {
        $this->_deLocalizacaoInterna = $deLocalizacaoInterna;
    }

    function setNoLogicoMicro($noLogicoMicro)
    {
        $this->_noLogicoMicro = $noLogicoMicro;
    }

    function setDtNascimento($dtNascimento)
    {
        $this->_dtNascimento = $dtNascimento;
    }

    function setDeResponsavel($deResponsavel)
    {
        $this->_deResponsavel = $deResponsavel;
    }

    function setDtAtualizacao($dtAtualizacao)
    {
        $this->_dtAtualizacao = $dtAtualizacao;
    }
}
