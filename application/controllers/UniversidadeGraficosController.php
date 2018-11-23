<?php

class UniversidadeGraficosController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Gráficos de execução da trilha";
        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();
        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();

        $ultimaDataDisponivel = $UnivTrilhaFundamentalMapper->getUltimaDataDisponivel();
        $this->view->ultimaDataDisponivel = $ultimaDataDisponivel;
        $this->view->DatasDisponiveis = $UnivTrilhaFundamentalMapper->getDatasDisponiveis();
        $this->view->totalizacaoFundamentalCoordenacao = $this->createPorCoordenacao($UnivTrilhaFundamentalMapper, $ultimaDataDisponivel);
    }

    public function createPorCoordenacao($UnivMapper, $dtSelecionada)
    {
        $dtLDia = $UnivMapper->getUltimoDiaMes($dtSelecionada);
        $totalCoordenacao = $UnivMapper->getTotalizacaoCoordenacao($dtSelecionada, $dtLDia);
        foreach ($totalCoordenacao as $key => $value) {
            $arrRetorno['dadosCoordenacao'][$key]['coordenacao'] = $value['coordenacao'];
            $arrRetorno['dadosCoordenacao'][$key]['total_trilhado'] = number_format($this->convertPorcentagem($value['porcentagem_soma'], $value['total_funcionarios']), 2);
            $arrRetorno['dadosCoordenacao'][$key]['meta'] = "100%";
        }
        return $arrRetorno['dadosCoordenacao'];
    }

    public function createPorEquipe($UnivMapper, $dtSelecionada, $nu_area)
    {
        $totalEquipe = $UnivMapper->getTotalizacaoEquipePorCoordenacao($dtSelecionada, $nu_area);
        foreach ($totalEquipe as $key => $value) {
            $arrRetorno['dadosEquipe'][$key]['area'] = $value['area'];
            $arrRetorno['dadosEquipe'][$key]['total_trilhado'] = number_format($this->convertPorcentagem($value['porcentagem_soma'], $value['total_funcionarios']), 2);
            $arrRetorno['dadosEquipe'][$key]['meta'] = "100%";
        }
        return $arrRetorno['dadosEquipe'];
    }

    public function createPorEmpregado($UnivMapper, $dtSelecionada, $nu_area)
    {
        $totalEmpregados = $UnivMapper->getTotalizacaoEmpregadosPorEquipe($dtSelecionada, $nu_area);
        foreach ($totalEmpregados as $key => $value) {
            $arrRetorno['dadosEmpregados'][$key]['porcentagem'] = $value['porcentagem'];
            $arrRetorno['dadosEmpregados'][$key]['nome'] = $value['nome_func'];
            $arrRetorno['dadosEmpregados'][$key]['matricula'] = $value['matricula'];
        }
        return $arrRetorno['dadosEmpregados'];
    }

    public function convertPorcentagem($porcentagem_soma, $total_funcionarios)
    {
        $porcentagem_soma = $porcentagem_soma * 100;
        $total_funcionarios = $total_funcionarios * 100;
        return $porcentagem_soma / $total_funcionarios;
    }

    public function ajaxCoordenacoesAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dtSelecionada = $this->_request->getParam('dt_referencia');

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();
        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();

        $dtLDia = $UnivTrilhaFundamentalMapper->getUltimoDiaMes($dtSelecionada);

        $arrRetorno['dadosFundamentalCoordenacao'] = $this->createPorCoordenacao($UnivTrilhaFundamentalMapper, $dtSelecionada);
        $arrRetorno['dadosLiderCoordenacao'] = $this->createPorCoordenacao($UnivTrilhaLiderMapper, $dtSelecionada);
        $arrRetorno['dadosFundamentalGeral'] = $UnivTrilhaFundamentalMapper->getTotalizacaoCoordenacao($dtSelecionada, $dtLDia);
        $arrRetorno['dadosLiderGeral'] = $UnivTrilhaLiderMapper->getTotalizacaoCoordenacao($dtSelecionada, $dtLDia);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEquipesFundamentalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $coordenacaoNome = $this->_request->getParam('coordenacao');

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();
        $coord = $UnivTrilhaFundamentalMapper->getNumCoordenacao($coordenacaoNome);

        $arrRetorno['dadosEquipe'] = $this->createPorEquipe($UnivTrilhaFundamentalMapper, $dtSelecionada, $coord[0]['nu_area']);
        // add func da coord
        $arrRetorno['dadosEmpregadosCoord'] = $this->createPorEmpregado($UnivTrilhaFundamentalMapper, $dtSelecionada, $coord[0]['nu_area']);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEmpregadosFundamentalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $equipeNome = $this->_request->getParam('equipe');

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();
        $equipe = $UnivTrilhaFundamentalMapper->getNumCoordenacao($equipeNome);
        $arrRetorno['dadosEmpregados'] = $this->createPorEmpregado($UnivTrilhaFundamentalMapper, $dtSelecionada, $equipe[0]['nu_area']);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEmpregadosFundamentalTotalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $matricula = $this->_request->getParam('matricula');

        $UnivTrilhaFundamentalMapper = new Application_Model_UnivTrilhaFundamentalMapper();
        $arrRetorno['dadosEmpregado'] = $UnivTrilhaFundamentalMapper->getEVolucaoEmpregado($matricula);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEquipesLiderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $coordenacaoNome = $this->_request->getParam('coordenacao');

        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();
        $coord = $UnivTrilhaLiderMapper->getNumCoordenacao($coordenacaoNome);

        $arrRetorno['dadosEquipe'] = $this->createPorEquipe($UnivTrilhaLiderMapper, $dtSelecionada, $coord[0]['nu_area']);
        // add func da coord
        $arrRetorno['dadosEmpregadosCoord'] = $this->createPorEmpregado($UnivTrilhaLiderMapper, $dtSelecionada, $coord[0]['nu_area']);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEmpregadosLiderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $equipeNome = $this->_request->getParam('equipe');

        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();
        $equipe = $UnivTrilhaLiderMapper->getNumCoordenacao($equipeNome);
        $arrRetorno['dadosEmpregados'] = $this->createPorEmpregado($UnivTrilhaLiderMapper, $dtSelecionada, $equipe[0]['nu_area']);

        echo json_encode($arrRetorno);
        die();
    }

    public function ajaxEmpregadosLiderTotalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $matricula = $this->_request->getParam('matricula');

        $UnivTrilhaLiderMapper = new Application_Model_UnivTrilhaLiderMapper();
        $arrRetorno['dadosEmpregado'] = $UnivTrilhaLiderMapper->getEVolucaoEmpregado($matricula);

        echo json_encode($arrRetorno);
        die();
    }
}
