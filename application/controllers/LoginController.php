<?php

class LoginController extends Zend_Controller_Action
{

    public function loginAction()
    {
        $objUsuario = new Application_Model_Usuario();
        $objUsuario->setNoMatrFunc(strtoupper($this->_request->getParam('usuario')));
        $objUsuario->setSenha(md5($this->_request->getParam('senha')));
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $retornoLogin = $objUsuarioMapper->login($objUsuario);
        if (empty($retornoLogin)) {
            $this->view->title = "Painel de Gestão CedesBR";
            $this->render('erro-login');
        } else {
            $session = new Zend_Session_Namespace('auth');
            error_log($session, 0);
            unset($retornoLogin[0]['senha']);
            $session->usuario = $retornoLogin[0];
            $this->redirect("/ocorrencias-sipon");
        }
    }

    public function logoutAction()
    {
        $session = new Zend_Session_Namespace('auth');
        unset($session->usuario);
        $this->redirect('/');
    }
}
