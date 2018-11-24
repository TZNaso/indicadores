<?php

class HorasExtrasController extends Zend_Controller_Action
{

    public function init()
    {}

    public function indexAction()
    {
        $this->view->title = "Horas Extras";
        $objHeMapper = new Application_Model_HoraExtraMapper();
        $ultimoMesDisponivel = $objHeMapper->getUltimoMesDisponivel();
        $this->view->ultimoMesDisponivel = $ultimoMesDisponivel;
        $this->view->anosDisponiveis = $objHeMapper->getAnosDisponiveis();
        $this->view->mesesDisponiveis = $objHeMapper->getMesesDisponiveis();
    }

    public function ajaxAtualizaDadosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $objHeMapper = new Application_Model_HoraExtraMapper();
        $dtSelecionada = $this->_request->getParam('dt_referencia');
        $dtLDia = $this->getultimoDia("01/" . $dtSelecionada);

        $dozemeses = date("d/m/Y", strtotime($dtLDia . ' -13 months'));

        $arrRetorno['dadosEmpregados'] = $objHeMapper->EmpregadosPorMes("01/" . $dtSelecionada, $dtLDia);
        $arrRetorno['areasDisponiveis'] = $objHeMapper->getAreasPorData("01/" . $dtSelecionada, $dtLDia);
        $arrRetorno['dt_selecionada'] = $dtSelecionada;
        // $arrRetorno['totalGeral'] = $this->getGeral();
        $arrRetorno['totalTeste'] = $objHeMapper->testeGeral($dozemeses);
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

                case 'nu_he_pg_285':
                case 'nu_he_pg_296':
                case 'nu_he_pg_302':
                case 'nu_he_pg_demais_proj':
                case 'nu_valor_he_pg_285':
                case 'nu_valor_he_pg_296':
                case 'nu_valor_he_pg_302':
                case 'nu_valor_he_pg_demais_proj':
                case 'nu_he_comp_284':
                case 'nu_valor_he_comp_284':
                case 'nu_total_he':
                case 'nu_valor_total_he':
                    $arrCampos['hora_extra'][] = $value;
                    break;

                default:
                    break;
            }
        }

        $objHoraExtraMapper = new Application_Model_HoraExtraMapper();

        $arrDados = $objHoraExtraMapper->getExportacao($arrCampos);

        set_time_limit(0);

        $filename = APPLICATION_PATH . "/tmp/RELATORIO_HE.xls";

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
                            $arrAux[$keyInterno] = utf8_decode($valueInterno);
                            break;

                        case 'nu_valor_he_pg_285':
                        case 'nu_valor_he_pg_296':
                        case 'nu_valor_he_pg_302':
                        case 'nu_valor_he_pg_demais_proj':
                        case 'nu_valor_he_comp_284':
                        case 'nu_valor_total_he':
                            $arrAux[$keyInterno] = str_replace(".", ",", $valueInterno);
                            break;

                        case 'nu_he_pg_285':
                        case 'nu_he_pg_296':
                        case 'nu_he_pg_302':
                        case 'nu_he_pg_demais_proj':
                        case 'nu_he_comp_284':
                        case 'nu_total_he':
                            $arrAux[$keyInterno] = utf8_decode($this->converteHoras($valueInterno));
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
            ->setRawHeader("Content-Disposition: attachment; filename=RELATORIO_HE.xls")
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
        $objHoraExtraMapper = new Application_Model_HoraExtraMapper();

        $arrDados = $objHoraExtraMapper->getExportacaoAnual($this->_request->getParam('ano'));

        set_time_limit(0);

        $filename = APPLICATION_PATH . "/tmp/RELATORIO_HE_ANUAL.xls";

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

                        case 'nu_valor_he_pg_285':
                        case 'nu_valor_he_pg_296':
                        case 'nu_valor_he_pg_302':
                        case 'nu_valor_he_pg_demais_proj':
                        case 'nu_valor_he_comp_284':
                        case 'nu_valor_total_he':
                            $arrAux[$keyInterno] = str_replace(".", ",", $valueInterno);
                            break;

                        case 'nu_he_pg_285':
                        case 'nu_he_pg_296':
                        case 'nu_he_pg_302':
                        case 'nu_he_pg_demais_proj':
                        case 'nu_he_comp_284':
                        case 'nu_total_he':
                            $arrAux[$keyInterno] = utf8_decode($this->converteHoras($valueInterno));
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
            ->setRawHeader("Content-Disposition: attachment; filename=RELATORIO_HE_ANUAL.xls")
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

                case 'no_sigla_area':
                    $arrRetorno[] = utf8_decode("Sigla da Equipe");
                    break;

                case 'de_area':
                    $arrRetorno[] = utf8_decode("Equipe");
                    break;

                case 'no_sigla_coord':
                    $arrRetorno[] = utf8_decode("Sigla da Coordenação");
                    break;

                case 'de_coord':
                    $arrRetorno[] = utf8_decode("Coordenação");
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

                case 'nu_he_pg_285':
                    $arrRetorno[] = utf8_decode("285");
                    break;

                case 'nu_he_pg_296':
                    $arrRetorno[] = utf8_decode("296");
                    break;

                case 'nu_he_pg_302':
                    $arrRetorno[] = utf8_decode("302");
                    break;

                case 'nu_he_pg_demais_proj':
                    $arrRetorno[] = utf8_decode("Demais Projetos");
                    break;

                case 'nu_valor_he_pg_285':
                    $arrRetorno[] = utf8_decode("285 (R$)");
                    break;

                case 'nu_valor_he_pg_296':
                    $arrRetorno[] = utf8_decode("296 (R$)");
                    break;

                case 'nu_valor_he_pg_302':
                    $arrRetorno[] = utf8_decode("302 (R$)");
                    break;

                case 'nu_valor_he_pg_demais_proj':
                    $arrRetorno[] = utf8_decode("Demais Projetos (R$)");
                    break;

                case 'nu_he_comp_284':
                    $arrRetorno[] = utf8_decode("Saldo a Compensar");
                    break;

                case 'nu_valor_he_comp_284':
                    $arrRetorno[] = utf8_decode("Saldo a Compensar (R$)");
                    break;

                case 'nu_total_he':
                    $arrRetorno[] = utf8_decode("Total");
                    break;

                case 'nu_valor_total_he':
                    $arrRetorno[] = utf8_decode("Total (R$)");
                    break;

                default:
                    break;
            }
        }

        return $arrRetorno;
    }

    private function converteHoras($qtMinutos)
    {
        $minutos = $qtMinutos % 60;

        if (strlen($minutos) == 1) {
            $minutos = "0" . $minutos;
        }

        $horasAux = explode(".", $qtMinutos / 60);
        $horas = $horasAux[0];

        if (strlen($horas) == 1) {
            $horas = "0" . $horas;
        }

        return $horas . ":" . $minutos;
    }

    private function getGeral()
    {
        $objHeMapper = new Application_Model_HoraExtraMapper();
        $meses = $objHeMapper->getMesesDisponiveisPorAno();

        foreach ($meses as $key => $value) {
            $tempData = $objHeMapper->getCoordTotais('01/' . $value['dt_referencia'], $this->getultimoDia('01/' . $value['dt_referencia']));
            for ($i = 0; $i < sizeof($tempData); $i ++) {
                $tempData[$i]['horas'] = $this->converteHoras($tempData[$i]['horas']);
            }
            $arrRetorno[$value['dt_referencia']] = $tempData;
        }
        return $arrRetorno;
    }

    private function getultimoDia($datatual)
    {
        $objHeMapper = new Application_Model_HoraExtraMapper();
        $dt = $objHeMapper->getUltimoDiaMes($datatual);
        return $dt[0]['date'];
    }
}
