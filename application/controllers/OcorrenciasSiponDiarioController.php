<?php

class OcorrenciasSiponDiarioController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Ocorrências Diárias do Sipon";
        $objMapper = new Application_Model_OcorrenciasSiponDiarioMapper();
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

        $objOcorSiponMapper = new Application_Model_OcorrenciasSiponDiarioMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $objOcorSiponMapper->getUltimoDiaMes($dtSelecionada);

        $arrRetorno['dtldia'] = $dtLDia;
        $arrRetorno['dtsel'] = ($dtSelecionada);

        $arrRetorno['dadosEmpregados'] = $objOcorSiponMapper->empregadosMes($dtSelecionada, $dtLDia);

        echo json_encode($arrRetorno);

        die();
    }
}
