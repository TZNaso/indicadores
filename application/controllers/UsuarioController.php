<?php

class UsuarioController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Gestão de Usuários";
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $this->view->arrTodos = $objUsuarioMapper->getListaCadastrados();
        $this->view->arrAdmin = $objUsuarioMapper->getListaCadastrados(true);
    }

    public function ajaxTornaAdminAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $objUsuario = new Application_Model_Usuario();
        $objUsuario->setNuUsuario($this->_request->getParam('nu_usuario'));
        $objUsuario->setIcAdministrador(1);
        try {
            $objUsuarioMapper->update($objUsuario);
            $arrReturn['msg'] = "Operação realizada com sucesso";
            $arrReturn['type'] = 'success';
        } catch (Exception $e) {
            $arrReturn['msg'] = $e->getMessage();
            $arrReturn['type'] = 'error';
        }
        echo Zend_Json_Encoder::encode($arrReturn);
        die();
    }

    public function ajaxExcluiAdminAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $data['nu_usuario'] = $this->_request->getParam('nu_usuario');
        $data['ic_administrador'] = 0;
        try {
            $objUsuarioMapper->getDbTable()->update($data, "nu_usuario = '{$data['nu_usuario']}'");
            $arrReturn['msg'] = "Operação realizada com sucesso";
            $arrReturn['type'] = 'success';
        } catch (Exception $e) {
            $arrReturn['msg'] = $e->getMessage();
            $arrReturn['type'] = 'error';
        }
        echo Zend_Json_Encoder::encode($arrReturn);
        die();
    }

    public function ajaxExcluiUsuarioAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        try {
            $objUsuarioMapper->getDbTable()->delete("nu_usuario = '{$this->_request->getParam('nu_usuario')}'");
            $arrReturn['msg'] = "Operação realizada com sucesso";
            $arrReturn['type'] = 'success';
        } catch (Exception $e) {
            $arrReturn['msg'] = $e->getMessage();
            $arrReturn['type'] = 'error';
        }
        echo Zend_Json_Encoder::encode($arrReturn);
        die();
    }

    public function ajaxCadastrarUsuarioAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $objFuncionarioMapper = new Application_Model_FuncionarioSiptiMapper();
        $userId = strtoupper($this->_request->getParam('usuario'));
        $objFuncionario = $objFuncionarioMapper->fetchByUserId($userId);
        $objUsuarioDuplicado = $objUsuarioMapper->fetchByUserId($userId);
        if ($objFuncionario->getNuFuncionario() == '') {
            // Empregado não encontrado
            $arrReturn['msg'] = "Empregado não encontrado";
            $arrReturn['type'] = 'error';
        } else if ($objUsuarioDuplicado->getNuUsuario() != '') {
            // Usuário já cadastrado
            $arrReturn['msg'] = "Empregado já cadastrado";
            $arrReturn['type'] = 'error';
        } else {
            $objAreaMapper = new Application_Model_AreaSiptiMapper();
            $objArea = $objAreaMapper->fetchById($objFuncionario->getNuArea());
            try {
                $objUsuarioNovo = new Application_Model_Usuario();
                $objUsuarioNovo->setNoMatrFunc(strtoupper($userId));
                $objUsuarioNovo->setNuFuncionario($objFuncionario->getNuFuncionario());
                $objUsuarioNovo->setSenha(md5($this->_request->getParam('senha')));
                $objUsuarioMapper->save($objUsuarioNovo);
                $arrReturn['msg'] = "Operação realizada com sucesso";
                $arrReturn['type'] = 'success';
                $arrReturn['nome'] = $objFuncionario->getNoFuncionario();
                $arrReturn['user_id'] = $userId;
                $arrReturn['area'] = $objArea->getNoSiglaArea();
            } catch (Exception $e) {
                $arrReturn['msg'] = $e->getMessage();
                $arrReturn['type'] = 'error';
            }
        }
        echo Zend_Json_Encoder::encode($arrReturn);
        die();
    }

    public function ajaxAlterarSenhaAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $objUsuario = new Application_Model_Usuario();
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $session = new Zend_Session_Namespace('auth');
        $objUsuario->setNuUsuario($session->usuario['nu_usuario']);
        $objUsuario->setSenha(md5($this->_request->getParam('senha')));
        try {
            $objUsuarioMapper->update($objUsuario);
            $arrReturn['type'] = 'success';
            $arrReturn['msg'] = 'Senha alterada com sucesso';
        } catch (Exception $e) {
            $arrReturn['type'] = 'error';
            $arrReturn['msg'] = $e->getMessage();
        }
        echo Zend_Json_Encoder::encode($arrReturn);
        die();
    }
}
