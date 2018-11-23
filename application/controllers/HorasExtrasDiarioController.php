<?php

class HorasExtrasDiarioController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Horas Extras - Dia a Dia";
        $objMapper = new Application_Model_HoraExtraDiarioMapper();
        $this->view->datasDisponiveis = $this->evalDatas($objMapper);
    }

    private function evalDatas($objMapper)
    {
        $datas = $objMapper->getDatasDisponiveis();
        array_pop($datas);
        return $datas;
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objMapper = new Application_Model_HoraExtraDiarioMapper();
        $MapperPositivo = new Application_Model_SaldoPositivoMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $objMapper->getUltimoDiaMes($dtSelecionada);
        $dtPDia = $objMapper->getPrimeiroDiaMes($dtSelecionada);
        $yesterday = $objMapper->nearData($dtSelecionada);

        $relPositivo = $MapperPositivo->dataRelPositivo($dtSelecionada, $dtLDia);

        $arrRetorno['dadosDtSelecionada'] = $objMapper->getHEDiarioEmpregados($dtSelecionada, $dtPDia[0], $dtLDia, $relPositivo);
        $arrRetorno['dadosDtAnterior'] = $objMapper->getHEDiarioEmpregados($yesterday, $dtPDia[0], $dtLDia, $relPositivo);
        $arrRetorno['ontem'] = $yesterday;
        $arrRetorno['hoje'] = $dtSelecionada;
        $arrRetorno['positivo'] = $relPositivo;
        echo json_encode($arrRetorno);
        die();
    }
}
