<?php

class Application_Model_Usuario
{

    protected $_nuUsuario;

    protected $_noMatrFunc;

    protected $_senha;

    protected $_email;

    protected $_nuFuncionario;

    protected $_icAdministrador;

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
            throw new Exception('Propriedade do Usuario inválida');
        }
        return $this->$method();
    }

    function getNuUsuario()
    {
        return $this->_nuUsuario;
    }

    function getNoMatrFunc()
    {
        return $this->_noMatrFunc;
    }

    function getSenha()
    {
        return $this->_senha;
    }

    function getEmail()
    {
        return $this->_email;
    }

    function getNuFuncionario()
    {
        return $this->_nuFuncionario;
    }

    function getIcAdministrador()
    {
        return $this->_icAdministrador;
    }

    function setNuUsuario($nuUsuario)
    {
        $this->_nuUsuario = $nuUsuario;
    }

    function setNoMatrFunc($noMatrFunc)
    {
        $this->_noMatrFunc = $noMatrFunc;
    }

    function setSenha($senha)
    {
        $this->_senha = $senha;
    }

    function setEmail($email)
    {
        $this->_email = $email;
    }

    function setNuFuncionario($nuFuncionario)
    {
        $this->_nuFuncionario = $nuFuncionario;
    }

    function setIcAdministrador($icAdministrador)
    {
        $this->_icAdministrador = $icAdministrador;
    }
}
