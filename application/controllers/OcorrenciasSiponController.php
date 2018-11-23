<?php

class OcorrenciasSiponController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Ocorrências do Sipon";
        $objOcorSiponMapper = new Application_Model_OcorrenciasSiponMapper();
        $ultimoMesDisponivel = $objOcorSiponMapper->getUltimoMesDisponivel();
        $this->view->ultimoMesDisponivel = $ultimoMesDisponivel;
        $this->view->mesesDisponiveis = $objOcorSiponMapper->getMesesDisponiveis();
        $this->view->anosDisponiveis = $objOcorSiponMapper->getAnosDisponiveis();
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objOcorSiponMapper = new Application_Model_OcorrenciasSiponMapper();

        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $objOcorSiponMapper->getUltimoDiaMes("01/" . $dtSelecionada);

        $arrRetorno['dtldia'] = $dtLDia;
        $arrRetorno['dtsel'] = ("01/" . $dtSelecionada);

        $arrRetorno['dadosEmpregados'] = $objOcorSiponMapper->empregadosMes("01/" . $dtSelecionada, $dtLDia);

        // Gerando percentual de pontos utilizados
        for ($i = 0, $size = count($arrRetorno['dadosEmpregados']); $i < $size; ++ $i) {
            if ($arrRetorno['dadosEmpregados'][$i]['qt_limite'] && $arrRetorno['dadosEmpregados'][$i]['qt_limite'] != 0) {
                $arrRetorno['dadosEmpregados'][$i]['percentual_utilizacao'] = ($arrRetorno['dadosEmpregados'][$i]['qt_total_pontos_utilizados'] / $arrRetorno['dadosEmpregados'][$i]['qt_limite']) * 100;
            } else {
                $arrRetorno['dadosEmpregados'][$i]['percentual_utilizacao'] = - 1;
            }
        }
        $arrRetorno['dadosOcorXEmp'] = $objOcorSiponMapper->ocorXFunc("01/" . $dtSelecionada, $dtLDia);
        $arrRetorno['dt_selecionada'] = $dtSelecionada;

        echo json_encode($arrRetorno);

        die();
    }

    public function exportacaoAction()
    {
        $arrCampos = array();

        foreach ($this->_request->getParam('campos') as $value) {

            $arrCampos['dt_referencia'] = $this->_request->getParam('dt_referencia');

            switch ($value) {

                case 'no_sigla_coord':
                case 'de_coord':
                    $arrCampos['coordenacao'][] = $value;
                    break;

                case 'no_funcionario':
                case 'no_matricula_caixa':
                case 'no_matr_func':
                    $arrCampos['empregado'][] = $value;
                    break;

                case 'no_sigla_area':
                case 'de_area':
                    $arrCampos['equipe'][] = $value;
                    break;

                case 'qt_56':
                case 'qt_57':
                case 'qt_58':
                case 'qt_70':
                case 'qt_195':
                case 'qt_53':
                case 'qt_19':
                case 'qt_20':
                case 'qt_bloqueio':
                case 'qt_total_ocorrencias':
                case 'qt_total_pontos_utilizados':
                case 'qt_limite':
                    $arrCampos['ocorrencias'][] = $value;
                    break;

                default:
                    break;
            }
        }

        $objOcorrenciasMapper = new Application_Model_OcorrenciasSiponMapper();

        $arrDados = $objOcorrenciasMapper->getExportacao($arrCampos);
        set_time_limit(0);

        $filename = APPLICATION_PATH . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "RELATORIO_SIPON.xls";

        $realPath = realpath($filename);

        if (false === $realPath) {
            touch($filename);
            chmod($filename, 0777);
        }

        $filename = realpath($filename);
        $handle = fopen($filename, "w");
        $finalData = array();

        $arrChaves = $this->_request->getParam('campos');

        $arrTitulos = $this->nomeiaCamposRelatorio($arrChaves);

        $finalData[] = $arrTitulos;

        foreach ($arrDados as $key => $value) {

            $arrAux = array();

            foreach ($value as $keyInterno => $valueInterno) {

                if (in_array($keyInterno, $arrChaves)) {

                    switch ($keyInterno) {

                        case 'no_sigla_area':
                        case 'de_area':
                        case 'no_sigla_coord':
                        case 'de_coord':
                        case 'no_funcionario':
                        case 'no_matricula_caixa':
                        case 'no_matr_func':
                        case 'dt_referencia':
                            $arrAux[$keyInterno] = utf8_decode($valueInterno);
                            break;

                        case 'qt_56':
                        case 'qt_57':
                        case 'qt_58':
                        case 'qt_70':
                        case 'qt_195':
                        case 'qt_53':
                        case 'qt_19':
                        case 'qt_20':
                        case 'qt_bloqueio':
                        case 'qt_total_ocorrencias':
                        case 'qt_total_pontos_utilizados':
                        case 'qt_limite':
                            $arrAux[$keyInterno] = $valueInterno;
                            break;

                        default:
                            break;
                    }
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
            ->setRawHeader("Content-Disposition: attachment; filename=RELATORIO_SIPON.xls")
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

    public function exportacaoAnualAction()
    {
        $objOcorrenciasMapper = new Application_Model_OcorrenciasSiponMapper();
        $arrDados = $objOcorrenciasMapper->getExportacaoAnual($this->_request->getParam('ano'));
        set_time_limit(0);
        $filename = APPLICATION_PATH . "/tmp/RELATORIO_OCORRENCIAS_ANUAL.xls";
        $realPath = realpath($filename);
        if (false === $realPath) {
            touch($filename);
            chmod($filename, 0777);
        }
        $filename = realpath($filename);
        $handle = fopen($filename, "w");
        $finalData = array();
        $arrChaves = array_keys($arrDados[0]);
        $arrTitulos = $this->nomeiaCamposRelatorio($arrChaves);
        $finalData[] = $arrTitulos;
        foreach ($arrDados as $key => $value) {
            $arrAux = array();
            foreach ($value as $keyInterno => $valueInterno) {
                if (in_array($keyInterno, $arrChaves)) {
                    switch ($keyInterno) {
                        case 'no_sigla_area':
                        case 'de_area':
                        case 'no_sigla_coord':
                        case 'de_coord':
                        case 'no_funcionario':
                        case 'no_matricula_caixa':
                        case 'no_matr_func':
                        case 'dt_referencia':
                            $arrAux[$keyInterno] = utf8_decode($valueInterno);
                            break;
                        case 'qt_56':
                        case 'qt_57':
                        case 'qt_58':
                        case 'qt_70':
                        case 'qt_195':
                        case 'qt_53':
                        case 'qt_19':
                        case 'qt_20':
                        case 'qt_bloqueio':
                        case 'qt_total_ocorrencias':
                        case 'qt_total_pontos_utilizados':
                        case 'qt_limite':
                            $arrAux[$keyInterno] = $valueInterno;
                            break;
                        default:
                            break;
                    }
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
            ->setRawHeader("Content-Disposition: attachment; filename=RELATORIO_OCORRENCIAS_ANUAL.xls")
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

    private function nomeiaCamposRelatorio($arrCampos)
    {
        $arrRetorno = array();

        foreach ($arrCampos as $value) {

            switch ($value) {

                case 'dt_referencia':
                    $arrRetorno[] = utf8_decode("Data de Referência");
                    break;

                case 'no_sigla_coord':
                    $arrRetorno[] = utf8_decode("Sigla da Coordenação");
                    break;

                case 'de_coord':
                    $arrRetorno[] = utf8_decode("Coordenação");
                    break;

                case 'no_sigla_area':
                    $arrRetorno[] = utf8_decode("Sigla da Equipe");
                    break;

                case 'de_area':
                    $arrRetorno[] = utf8_decode("Equipe");
                    break;

                case 'no_funcionario':
                    $arrRetorno[] = utf8_decode("Empregado");
                    break;

                case 'no_matricula_caixa':
                    $arrRetorno[] = utf8_decode("Matrícula");
                    break;

                case 'no_matr_func':
                    $arrRetorno[] = utf8_decode("User ID");
                    break;

                case 'qt_56':
                    $arrRetorno[] = utf8_decode("Ocorrência 56");
                    break;

                case 'qt_57':
                    $arrRetorno[] = utf8_decode("Ocorrência 57");
                    break;

                case 'qt_58':
                    $arrRetorno[] = utf8_decode("Ocorrência 58");
                    break;

                case 'qt_70':
                    $arrRetorno[] = utf8_decode("Ocorrência 70");
                    break;

                case 'qt_195':
                    $arrRetorno[] = utf8_decode("Ocorrência 195");
                    break;

                case 'qt_53':
                    $arrRetorno[] = utf8_decode("Ocorrência 53");
                    break;

                case 'qt_19':
                    $arrRetorno[] = utf8_decode("Ocorrência 19");
                    break;

                case 'qt_20':
                    $arrRetorno[] = utf8_decode("Ocorrência 20");
                    break;

                case 'qt_bloqueio':
                    $arrRetorno[] = utf8_decode("Ocorrência bloqueio");
                    break;

                case 'qt_total_ocorrencias':
                    $arrRetorno[] = utf8_decode("Total de ocorrências");
                    break;

                case 'qt_total_pontos_utilizados':
                    $arrRetorno[] = utf8_decode("Total de pontos utilizados");
                    break;

                case 'qt_limite':
                    $arrRetorno[] = utf8_decode("Limite de pontos");
                    break;

                default:
                    break;
            }
        }

        return $arrRetorno;
    }
}
