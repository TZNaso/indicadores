<?php

class LigacoesController extends Zend_Controller_Action {

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Ligações Telefônicas";
        $objLigacoesMapper = new Application_Model_LigacoesMapper();
        $ultimoMesDisponivel = $objLigacoesMapper->getUltimoMesDisponivel();
        $ultimoDia =  $objLigacoesMapper->getUltimoDiaMes("01/" . $ultimoMesDisponivel);
        $dtReferencia = "01/" . $ultimoMesDisponivel;
        $this->view->ultimoMesDisponivel = $ultimoMesDisponivel;
        $this->view->mesesDisponiveis = $objLigacoesMapper->getMesesDisponiveis();
        $this->view->anosDisponiveis = $objLigacoesMapper->getAnosDisponiveis();

        $objAreaMapper = new Application_Model_AreaSiptiMapper();
        $this->view->coords = $objAreaMapper->getCoordEquipe($dtReferencia, $ultimoDia);
    }

    public function ajaxCoordEquipeAction()
    {
      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();

      $objLigacoesMapper = new Application_Model_LigacoesMapper();

      $dtSelecionada = $this->getRequest()->getPost('dt_referencia', null);
      $dtLDia = $objLigacoesMapper->getUltimoDiaMes("01/" . $dtSelecionada);
      $dtReferencia = "01/" . $dtSelecionada;

      $objAreaMapper = new Application_Model_AreaSiptiMapper();

      $arrRetorno['data'] = $objAreaMapper->getCoordEquipe($dtReferencia, $dtLDia);

      echo json_encode($arrRetorno);

      die();
    }

    public function ajaxRamaisNotFoundAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objLigacoesMapper = new Application_Model_LigacoesMapper();

        $dtSelecionada = $this->getRequest()->getPost('dt_referencia', null);
        $dtLDia = $objLigacoesMapper->getUltimoDiaMes("01/" . $dtSelecionada);
        $dtReferencia = "01/" . $dtSelecionada;

        $arrRetorno['data'] = $objLigacoesMapper->getRamaisNotFound($dtReferencia, $dtLDia);

        echo json_encode($arrRetorno);

        die();
    }

    public function ajaxSaveFuncAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $telefone = $this->getRequest()->getPost('telefone', null);
        $nome = $this->getRequest()->getPost('nome', null);
        $matricula = $this->getRequest()->getPost('matricula', null);
        $coord = $this->getRequest()->getPost('coord', null);
        $equipe = $this->getRequest()->getPost('equipe', null);
        $dt_referencia = $this->getRequest()->getPost('dt_referencia', null);

        $object = new Application_Model_LigacoesOutros();
        $object->setRamal_id($telefone);
        $object->setNome($nome);
        $object->setMatricula($matricula);
        $object->setCoord($coord);
        $object->setEquipe($equipe);
        $object->setDtReferencia($dt_referencia);

        $objectMapper = new Application_Model_LigacoesOutrosMapper();
        $objectMapper->save($object);

        die();
    }

    public function ajaxGetDataAction() {
        $dtSelecionada = $this->getRequest()->getPost('dt_referencia', null);
        $tipo = $this->getRequest()->getPost('tipo', null);

        $objLigacoesMapper = new Application_Model_LigacoesMapper();
        $objPrestadorMapper = new Application_Model_PrestadorMapper();
        $objOutrosMapper = new Application_Model_LigacoesOutrosMapper();
        $dtLDia = $objLigacoesMapper->getUltimoDiaMes("01/" . $dtSelecionada);
        $dtReferencia = "01/" . $dtSelecionada;

        if ($tipo != 'All') {
            $prestadoresarray = $objPrestadorMapper->getSumBy(Zend_Registry::get($tipo), $dtReferencia, $dtLDia);
            $cedesarray = $objLigacoesMapper->getSumBy(Zend_Registry::get($tipo), $dtReferencia, $dtLDia);
            $outrosarray = $objOutrosMapper->getSumBy(Zend_Registry::get($tipo), $dtReferencia, $dtLDia);
            $arrRetorno['data'] = array_merge($cedesarray, $prestadoresarray, $outrosarray);

            if ($tipo == 'Coord') {
                $arraySize = sizeof($arrRetorno['data']);
                for ($i = 0; $i <= $arraySize; $i++) {
                    for ($j = $i + 1; $j <= $arraySize; $j++) {
                        if ($j < $arraySize && isset($arrRetorno['data'][$i]) && isset($arrRetorno['data'][$j]) && $arrRetorno['data'][$j]['coord'] == $arrRetorno['data'][$i]['coord']) {
                            $arrRetorno['data'][$i]['duracao_minutos'] = (string) round($arrRetorno['data'][$i]['duracao_minutos'] + $arrRetorno['data'][$j]['duracao_minutos'], 2);
                            $arrRetorno['data'][$i]['valor'] = (string) round($arrRetorno['data'][$i]['valor'] + $arrRetorno['data'][$j]['valor'], 2);
                            $arrRetorno['data'][$i]['duracao'] = $this->sum_the_time($arrRetorno['data'][$i]['duracao'], $arrRetorno['data'][$j]['duracao']);
                            unset($arrRetorno['data'][$j]);
                        }
                    }
                }

            } else if ($tipo == 'Equipe') {
                $arraySize = sizeof($arrRetorno['data']);
                for ($i = 0; $i <= $arraySize; $i++) {
                    for ($j = $i + 1; $j <= $arraySize; $j++) {
                        if ($j < $arraySize && isset($arrRetorno['data'][$i]) && isset($arrRetorno['data'][$j]) && $arrRetorno['data'][$j]['equipe'] == $arrRetorno['data'][$i]['equipe']) {
                            $arrRetorno['data'][$i]['duracao_minutos'] = (string) round($arrRetorno['data'][$i]['duracao_minutos'] + $arrRetorno['data'][$j]['duracao_minutos'], 2);
                            $arrRetorno['data'][$i]['valor'] = (string) round($arrRetorno['data'][$i]['valor'] + $arrRetorno['data'][$j]['valor'], 2);
                            $arrRetorno['data'][$i]['duracao'] = $this->sum_the_time($arrRetorno['data'][$i]['duracao'], $arrRetorno['data'][$j]['duracao']);
                            unset($arrRetorno['data'][$j]);
                        }
                    }
                }
            }
        } else {
            $prestadoresarray = $objPrestadorMapper->empregadosMes($dtReferencia, $dtLDia);
            $cedesarray = $objLigacoesMapper->empregadosMes($dtReferencia, $dtLDia);
            $outrosarray = $objOutrosMapper->empregadoMes($dtReferencia, $dtLDia);
            $arrRetorno['data'] = array_merge($cedesarray, $prestadoresarray, $outrosarray);
        }

        $arrRetorno['data'] = array_values(array_unique($arrRetorno['data'], SORT_REGULAR));
        $arrRetorno['tipo'] = $tipo;

        echo json_encode($arrRetorno);

        die();
    }

    function sum_the_time($time1, $time2) {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time) {
            list($hour, $minute, $second) = explode(':', $time);
            $seconds += $hour * 3600;
            $seconds += $minute * 60;
            $seconds += $second;
        }
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    //public function ajaxGetOptTelAction()
    //{

    //}

    public function ajaxReportAction()
    {
        $data_inicio = $this->getRequest()->getPost('data_inicio', null);
        $data_fim = $this->getRequest()->getPost('data_fim', null);

        $objLigacoesMapper = new Application_Model_LigacoesMapper();

        $arrDados = $objLigacoesMapper->exportReport($data_inicio, $data_fim);
        set_time_limit(0);
        $filename = APPLICATION_PATH . "/tmp/RELATORIO_LIGACOES_".  str_replace('/', '', $data_fim) .".xls";
        $realPath = realpath($filename);
        if (false === $realPath) {
            touch($filename);
            chmod($filename, 0777);
        }
        $filename = realpath($filename);
        $handle = fopen($filename, "w");
        $finalData = array();
        $arrChaves = array_keys($arrDados[0]);
        $finalData[] = $arrChaves;
        foreach ($arrDados as $key => $value) {
            $arrAux = array();
            foreach ($value as $keyInterno => $valueInterno) {
                if (in_array($keyInterno, $arrChaves)) {
                    $arrAux[$keyInterno] = $valueInterno;
                }
            }
            $finalData[] = $arrAux;
        }
        foreach ($finalData as $finalRow) {
            fputcsv($handle, $finalRow, "\t");
        }
        fclose($handle);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()
            ->setRawHeader("Content-Type: application/vnd.ms-excel; charset=UTF-8")
            ->setRawHeader("Content-Disposition: attachment; filename=RELATORIO_LIGACOES_". $data_fim .".xls")
            ->setRawHeader("Content-Transfer-Encoding: binary")
            ->setRawHeader("Expires: 0")
            ->setRawHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0")
            ->setRawHeader("Pragma: public")
            ->setRawHeader("Content-Length: " . filesize($filename))
            ->sendResponse();
        readfile($filename);
        unlink($filename);
        exit();
    }
}
