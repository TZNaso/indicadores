<?php

class UniversidadeLiderController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Trilha Lider Caixa";

        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();

        $ultimaDataDisponivel = $UnivTrilhaLiderMapper->getultimaDataDisponivel();
        $this->view->ultimaDataDisponivel = $ultimaDataDisponivel;
        $this->view->datasDisponiveis = $UnivTrilhaLiderMapper->getDatasDisponiveis();
        $this->view->areasDosponiveis = $UnivTrilhaLiderMapper->getAreasPorData($ultimaDataDisponivel);
        $this->view->anosDisponiveis = $UnivTrilhaLiderMapper->getAnosDisponiveis();
        $this->view->totalizacaoEmpregado = $UnivTrilhaLiderMapper->getTotalizacaoEmpregados($ultimaDataDisponivel);
        $this->view->totalizacaoEquipe = $this->createPorEquipe($UnivTrilhaLiderMapper, $ultimaDataDisponivel);
        $this->view->totalizacaoCoordenacao = $this->createPorCoordenacao($UnivTrilhaLiderMapper, $ultimaDataDisponivel);
    }

    public function createPorEquipe($UnivTrilhaLiderMapper, $dtSelecionada)
    {
        $dtLDia = $UnivTrilhaLiderMapper->getUltimoDiaMes($dtSelecionada);
        $totalEquipe = $UnivTrilhaLiderMapper->getTotalizacaoEquipe($dtSelecionada, $dtLDia);
        foreach ($totalEquipe as $key => $value) {
            $arrRetorno['dadosEquipe'][$key]['area'] = $value['area'];
            $arrRetorno['dadosEquipe'][$key]['total_trilhado'] = number_format($this->convertPorcentagem($value['porcentagem_soma'], $value['total_funcionarios']), 2);
            $arrRetorno['dadosEquipe'][$key]['meta'] = "100%";
        }
        return $arrRetorno['dadosEquipe'];
    }

    public function createPorCoordenacao($UnivTrilhaLiderMapper, $dtSelecionada)
    {
        $dtLDia = $UnivTrilhaLiderMapper->getUltimoDiaMes($dtSelecionada);
        $totalCoordenacao = $UnivTrilhaLiderMapper->getTotalizacaoCoordenacao($dtSelecionada, $dtLDia);
        foreach ($totalCoordenacao as $key => $value) {
            $arrRetorno['dadosCoordenacao'][$key]['coordenacao'] = $value['coordenacao'];
            $arrRetorno['dadosCoordenacao'][$key]['total_trilhado'] = number_format($this->convertPorcentagem($value['porcentagem_soma'], $value['total_funcionarios']), 2);
            $arrRetorno['dadosCoordenacao'][$key]['meta'] = "100%";
        }
        return $arrRetorno['dadosCoordenacao'];
    }

    public function convertPorcentagem($porcentagem_soma, $total_funcionarios)
    {
        $porcentagem_soma = $porcentagem_soma * 100;
        $total_funcionarios = $total_funcionarios * 100;
        return $porcentagem_soma / $total_funcionarios;
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');

        $arrRetorno['dadosCoordenacao'] = $this->createPorCoordenacao($UnivTrilhaLiderMapper, $dtSelecionada);
        $arrRetorno['dadosEmpregados'] = $UnivTrilhaLiderMapper->getTotalizacaoEmpregados($dtSelecionada);
        $arrRetorno['dadosEquipe'] = $this->createPorEquipe($UnivTrilhaLiderMapper, $dtSelecionada);
        $arrRetorno['areasDisponiveis'] = $UnivTrilhaLiderMapper->getAreasPorData($dtSelecionada);
        $arrRetorno['dt_selecionada'] = $dtSelecionada;

        echo json_encode($arrRetorno);

        die();
    }
}


