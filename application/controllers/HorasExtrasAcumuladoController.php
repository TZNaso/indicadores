<?php

class HorasExtrasAcumuladoController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Horas Extras - Acumulado do Mês";
        $objMapper = new Application_Model_HoraExtraAcumuladoMapper();
        $this->view->datasDisponiveis = $objMapper->getDatasDisponiveis();
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objMapper = new Application_Model_HoraExtraAcumuladoMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $objMapper->getUltimoDiaMes($dtSelecionada);
        $dtPDia = $objMapper->getPrimeiroDiaMes($dtSelecionada);

        $arrRetorno['dadosEmpregados'] = $objMapper->getHEAcumuladoEmpregados($dtSelecionada, $dtPDia[0], $dtLDia);
        $arrRetorno['dadosMeta'] = $objMapper->getCoordMeta($dtPDia);
        echo json_encode($arrRetorno);
        die();
    }
}
