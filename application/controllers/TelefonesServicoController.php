<?php

class TelefonesServicoController extends Zend_Controller_Action
{

    public function init()
    {}

    public function ajaxSaveTelefoneServicoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objTelefoneServicoMapper = new Application_Model_TelefonesServicoMapper();
        $objTelefoneServicoTable = new Application_Model_TelefonesServico();

        $ramal = $this->getRequest()->getPost('ramal', null);
        $numero_servico = $this->getRequest()->getPost('numeroServico', null);

        $objTelefoneServicoTable->setRamal($ramal);
        $objTelefoneServicoTable->setNumeroServico($numero_servico);

        echo $objTelefoneServicoMapper->save($objTelefoneServicoTable);
        die();
    }

    public function ajaxDeleteTelefoneServicoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objTelefoneServicoMapper = new Application_Model_TelefonesServicoMapper();

        $id = $this->getRequest()->getPost('id', null);

        echo $objTelefoneServicoMapper->deleteNumeroServico($id);
        die();
    }

    public function ajaxListTelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objTelefoneServicoMapper = new Application_Model_TelefonesServicoMapper();
        $ramal = $this->getRequest()->getPost('ramal', null);

        echo json_encode($objTelefoneServicoMapper->getNumerosServico($ramal));
        die();
    }
}
