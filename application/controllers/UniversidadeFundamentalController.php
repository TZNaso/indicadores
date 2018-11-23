<?php

class UniversidadeFundamentalController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Trilha Fundamental Caixa";

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();

        $ultimaDataDisponivel = $UnivTrilhaFundamentalMapper->getUltimaDataDisponivel();
        $this->view->ultimaDataDisponivel = $ultimaDataDisponivel;
        $this->view->DatasDisponiveis = $UnivTrilhaFundamentalMapper->getDatasDisponiveis();
        $this->view->areasDosponiveis = $UnivTrilhaFundamentalMapper->getAreasPorData($ultimaDataDisponivel);
        $this->view->anosDisponiveis = $UnivTrilhaFundamentalMapper->getAnosDisponiveis();
        $this->view->totalizacaoEmpregado = $UnivTrilhaFundamentalMapper->getTotalizacaoEmpregados($ultimaDataDisponivel);
        $this->view->totalizacaoEquipe = $this->createPorEquipe($UnivTrilhaFundamentalMapper, $ultimaDataDisponivel);
        $this->view->totalizacaoCoordenacao = $this->createPorCoordenacao($UnivTrilhaFundamentalMapper, $ultimaDataDisponivel);
    }

    public function createPorEquipe($UnivTrilhaFundamentalMapper, $dtSelecionada)
    {
        $dtLDia = $UnivTrilhaFundamentalMapper->getUltimoDiaMes($dtSelecionada);
        $totalEquipe = $UnivTrilhaFundamentalMapper->getTotalizacaoEquipe($dtSelecionada, $dtLDia);
        foreach ($totalEquipe as $key => $value) {
            $arrRetorno['dadosEquipe'][$key]['area'] = $value['area'];
            $arrRetorno['dadosEquipe'][$key]['total_trilhado'] = number_format($this->convertPorcentagem($value['porcentagem_soma'], $value['total_funcionarios']), 2);
            $arrRetorno['dadosEquipe'][$key]['meta'] = "100%";
        }
        return $arrRetorno['dadosEquipe'];
    }

    public function createPorCoordenacao($UnivTrilhaFundamentalMapper, $dtSelecionada)
    {
        $dtLDia = $UnivTrilhaFundamentalMapper->getUltimoDiaMes($dtSelecionada);
        $totalCoordenacao = $UnivTrilhaFundamentalMapper->getTotalizacaoCoordenacao($dtSelecionada, $dtLDia);
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

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');

        $arrRetorno['dadosEmpregados'] = $UnivTrilhaFundamentalMapper->getTotalizacaoEmpregados($dtSelecionada);
        $arrRetorno['dadosEquipe'] = $this->createPorEquipe($UnivTrilhaFundamentalMapper, $dtSelecionada);
        $arrRetorno['dadosCoordenacao'] = $this->createPorCoordenacao($UnivTrilhaFundamentalMapper, $dtSelecionada);
        $arrRetorno['areasDisponiveis'] = $UnivTrilhaFundamentalMapper->getAreasPorData($dtSelecionada);
        $arrRetorno['dt_selecionada'] = $dtSelecionada;

        echo json_encode($arrRetorno);

        die();
    }
}


