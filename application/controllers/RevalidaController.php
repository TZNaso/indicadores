<?php

class RevalidaController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Revalida";
        $objHeMapper = new Application_Model_HoraExtraMapper();
        $this->view->mesesDisponiveis = $objHeMapper->getMesesDisponiveis();
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objHeMapper = new Application_Model_HoraExtraMapper();
        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $this->getultimoDia("01/" . $dtSelecionada);

        $arrRetorno['Revalidados'] = $objHeMapper->revalida("01/" . $dtSelecionada, $dtLDia);
        $arrRetorno['dt1'] = "01/" . $dtSelecionada;
        $arrRetorno['dt2'] = $dtLDia;
        echo json_encode($arrRetorno);
        die();
    }

    private function getultimoDia($datatual)
    {
        $objHeMapper = new Application_Model_HoraExtraMapper();
        $dt = $objHeMapper->getUltimoDiaMes($datatual);
        return $dt[0]['date'];
    }
}

