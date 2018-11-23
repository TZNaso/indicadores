<?php

class AdminController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->view->title = "Administração do Sistema";
    }

    public function cargaDadosAction()
    {
        $this->view->title = "Carga de Dados";
        $prestador = new Application_Model_PrestadorMapper();
        $uploads['prestadores'] = $prestador->allUploads();

        $sipon = new Application_Model_OcorrenciasSiponDiarioMapper();
        $uploads['sipon'] = $sipon->allUploads();

        $h_extra = new Application_Model_HoraExtraMapper();
        $uploads['h_extra'] = $h_extra->allUploads();

        $ligacoes = new Application_Model_LigacoesMapper();
        $uploads['ligacoes'] = $ligacoes->allUploads();

        $fundamental = new Application_Model_UnivTrilhaFundamentalMapper();
        $uploads['fundamental'] = $fundamental->allUploads();

        $lider = new Application_Model_UnivTrilhaLiderMapper();
        $uploads['lider'] = $lider->allUploads();

        $this->view->data = $uploads;
    }

    public function cadastrarAction()
    {
        $this->view->title = "Adicionar usuário";
    }

    public function adicionarUsuarioAction()
    {
        $objUsuarioMapper = new Application_Model_UsuarioMapper();
        $objUsuario = new Application_Model_Usuario();
        $objUsuario->setNuFuncionario($this->_request->getParam('nu_funcionario'));
        $objUsuario->setSenha(md5($this->_request->getParam('senha')));
        $objUsuario->setNoMatrFunc(trim($this->_request->getParam('matricula')));
        try {
            $objUsuarioMapper->save($objUsuario);
            $this->view->retorno = "Empregado cadastrado com sucesso!";
        } catch (Exception $e) {
            $this->view->retorno = "Erro ao cadastrar o funcionário: " . $e->getMessage();
        }
        $this->render("cadastro");
    }

    public function ajaxGetEmpregadoAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $objFuncionarioMapper = new Application_Model_FuncionarioSiptiMapper();
        $objFuncionario = $objFuncionarioMapper->fetchByUserId(strtoupper($this->_request->getParam('matricula')));

        if ($objFuncionario->getNuFuncionario()) {

            // Verificando Função
            $objFuncaoMapper = new Application_Model_FuncaoSiptiMapper();
            $objFuncao = $objFuncaoMapper->fetchByNuFuncao($objFuncionario->getNuFuncao());
        }

        $arrRetorno = array();

        if ($objFuncionario->getNuFuncionario() && trim($objFuncao->getNuTipoFuncao()) == 'G') {

            // Verificando se o usuário ja está cadastrado no painel
            $objUsuarioMapper = new Application_Model_UsuarioMapper();

            $objUsuario = new Application_Model_Usuario();
            $objUsuario = $objUsuarioMapper->fetchByUserId($objFuncionario->getNoMatrFunc());

            if ($objUsuario->getNuUsuario()) {

                $arrRetorno['type'] = 'error';
                $arrRetorno['msg'] = "Empregado já cadastrado no sistema";
            } else {

                $arrRetorno['type'] = 'success';
                $arrRetorno['msg'] = "User ID do empregado <b>" . $objFuncionario->getNoFuncionario() . "</b> realizada com sucesso! Digite uma senha com 6 dígitos";
                $arrRetorno['nu_funcionario'] = $objFuncionario->getNuFuncionario();
            }
        } else {

            if ($objFuncionario->getNuFuncionario()) {

                $arrRetorno['type'] = 'error';
                $arrRetorno['msg'] = "Empregado não possui função gerencial";
            } else {

                $arrRetorno['type'] = 'error';
                $arrRetorno['msg'] = "Empregado não encontrado";
            }
        }

        echo Zend_Json_Encoder::encode($arrRetorno);
        die();
    }

    public function carregarGenericoAction()
    {
        $arquivo = $_FILES['arquivo_gen'];
        $tipo = $this->_request->getParam('tipo_arquivo');
        $dtReferencia = $this->_request->getParam('dt_referencia');
        $dtReferenciaMes = '01' . substr($dtReferencia, 2);
        $linhaInicio = $this->_request->getParam('linha_inicio');
        $numeroArquivo = $this->_request->getParam('nr_arquivo');
        $nomeArquivo = $arquivo['name'];

        $destination_path = getcwd() . DIRECTORY_SEPARATOR;
        $target_path = $destination_path . "files" . DIRECTORY_SEPARATOR . basename($nomeArquivo);
        @move_uploaded_file($arquivo['tmp_name'], $target_path);
        $handle = fopen($target_path, "r");
        // php perde reff ao handle apos primeira chamada
        $handle2 = fopen($target_path, "r");
        if ($tipo === 'hem') {
            $objMapperHeDia = new Application_Model_HoraExtraAcumuladoMapper();
            $objTableHeDia = new Application_Model_HoraExtraAcumulado();
            $dia = $this->saveHe($handle, $objMapperHeDia, $linhaInicio, $dtReferencia, $objTableHeDia);

            $objMapperHEmes = new Application_Model_HoraExtraMapper();
            $objTableHEmes = new Application_Model_HoraExtra();
            list ($a, $b, $c) = $this->saveHe($handle2, $objMapperHEmes, $linhaInicio, $dtReferenciaMes, $objTableHEmes);

            $this->sucessoCarga($a, $b, $c);
        }
        if ($tipo === 'sipon') {
            $objMapperDia = new Application_Model_OcorrenciasSiponDiarioMapper();
            $objTableDia = new Application_Model_OcorrenciasSiponDiario();
            $dia = $this->saveSipon($handle, $objMapperDia, $linhaInicio, $dtReferencia, $objTableDia);

            $objMapperMes = new Application_Model_OcorrenciasSiponMapper();
            $objTableMes = new Application_Model_OcorrenciasSipon();
            list ($a, $b, $c) = $this->saveSipon($handle2, $objMapperMes, $linhaInicio, $dtReferenciaMes, $objTableMes);

            $this->sucessoCarga($a, $b, $c);
        }
        if ($tipo === 'ligacoes') {
            $objMapperLiga = new Application_Model_LigacoesMapper();
            $objTableLiga = new Application_Model_Ligacoes();

            list ($a, $b, $c) = $this->saveLiga($handle2, $objMapperLiga, $linhaInicio, $dtReferenciaMes, $objTableLiga, $numeroArquivo);
            $this->sucessoCarga($a, $b, $c);
        }
        if ($tipo === 'fununiv') {
            $objMapper = new Application_Model_UnivTrilhaFundamentalMapper();
            $this->saveFundamental($handle, $objMapper, $linhaInicio, $dtReferencia);
        }
        if ($tipo === 'liduniv') {
            $objMapper = new Application_Model_UnivTrilhaLiderMapper();
            $this->saveLider($handle, $objMapper, $linhaInicio, $dtReferencia);
        }
        if ($tipo === 'prestadores') {
            $objMapperMes = new Application_Model_PrestadorMapper();
            $objTableMes = new Application_Model_Prestador();
            list ($a, $b, $c) = $this->savePrestadores($handle2, $objMapperMes, $linhaInicio, $dtReferenciaMes, $objTableMes);

            $this->sucessoCarga($a, $b, $c);
        }
        if ($tipo === 'sitel') {
            $objMapperRamais = new Application_Model_RamalSitelMapper();
            $objTableRamais = new Application_Model_RamalSitel();
            $dia = $this->saveRamal($handle, $objMapperRamais, $linhaInicio, $dtReferencia, $objTableRamais);

            $objMapperHist = new Application_Model_HistRamalSitelMapper();
            $objTableHist = new Application_Model_HistRamalSitel();
            list ($a, $b, $c) = $this->saveHistRamal($handle2, $objMapperHist, $linhaInicio, $dtReferenciaMes, $objTableHist, $objMapperRamais);
            $this->sucessoCarga($a, $b, $c);
        }
    }

    private function saveHe($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable)
    {
        $objMapper->deleteByDtReferencia($dtReferencia);
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setDtReferencia($dtReferencia);
                $objTable->setNuEmpregado($this->getNuFuncionario(trim($data[0], " \n\r\t\0\x0b\xa0")));
                $objTable->setTotalHe($this->hoursToMinutes($data[14]));
                $objTable->setValorHe($this->formataValor($data[15]));
                if ($objMapper instanceof Application_Model_HoraExtraMapper) {
                    $objTable->setNoFg($data[3]);
                    $objTable->setNuCodigoFg($data[2]);
                    $objTable->setNuHePg285($this->hoursToMinutes($data[4]));
                    $objTable->setNuHePg296($this->hoursToMinutes($data[5]));
                    $objTable->setNuHePg302($this->hoursToMinutes($data[6]));
                    $objTable->setNuHePgDemaisProj($this->hoursToMinutes($data[7]));
                    $objTable->setNuValorHePg285($this->formataValor($data[8]));
                    $objTable->setNuValorHePg296($this->formataValor($data[9]));
                    $objTable->setNuValorHePg302($this->formataValor($data[10]));
                    $objTable->setNuValorHePgDemaisProj($this->formataValor($data[11]));
                    $objTable->setNuHeComp284($this->hoursToMinutes($data[12]));
                    $objTable->setNuValorHeComp284($this->formataValor($data[13]));
                }
                if (! $objTable->getNuEmpregado()) {
                    $empregadosNaoEncontrados[] = $this->getEmpregadoDesconhecido($data);
                } else {
                    try {
                        $objMapper->save($objTable);
                        $totalCarregado ++;
                    } catch (Exception $e) {
                        var_dump($e);
                        if ($e->getCode() == '23505') {
                            $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                        }
                    }
                }
            }
            $row ++;
        }
        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function saveRamal($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable)
    {
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setRamal(rtrim($data[0]));
                try {
                    $objMapper->save($objTable);
                    $totalCarregado ++;
                } catch (Exception $e) {
                    if ($e->getCode() == '23505') {
                        $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                    } else {
                      var_dump($e);
                    }
                }
            }
            $row ++;
        }
        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function saveHistRamal($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable, $ramais)
    {
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setMatricula(rtrim($data[2]));
                $objTable->setNome(rtrim(utf8_encode($data[3])));
                $objTable->setData_inicio(rtrim($dtReferencia));
                $objTable->setData_fim(null);
                $ramal_id = $ramais->getIDByRamal(rtrim($data[0]));
                $objTable->setRamal_sitel_id(rtrim($ramal_id));

                try {
                    $objMapper->save($objTable);
                    $totalCarregado ++;
                } catch (Exception $e) {
                    var_dump($e);
                    if ($e->getCode() == '23505') {
                        $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                    }
                }
            }
            $row ++;
        }
        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function saveSipon($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable)
    {
        $objMapper->deleteByDtReferencia($dtReferencia);
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            // $data = array_map("utf8_encode", $data);
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setDtReferencia($dtReferencia);
                $objTable->setNuEmpregado($this->getNuFuncionario($this->cleanFields($data[0])));
                $objTable->setNoFg($this->cleanFields($data[3]));
                $objTable->setNuCodigoFg(str_replace(';', '', $this->cleanFields($data[2])));
                $objTable->setQt56($this->cleanFields($data[4]));
                $objTable->setQt57($this->cleanFields($data[5]));
                $objTable->setQt58($this->cleanFields($data[6]));
                $objTable->setQt70($this->cleanFields($data[7]));
                $objTable->setQt195($this->cleanFields($data[8]));
                $objTable->setQt53($this->cleanFields($data[16]));
                $objTable->setQt19($this->cleanFields($data[9]));
                $objTable->setQt20($this->cleanFields($data[10]));
                $objTable->setQtBloqueio($this->cleanFields($data[11]));
                $objTable->setQtTotalOcorrencias($this->cleanFields($data[12]));
                $objTable->setQtTotalPontosUtilizados($this->cleanFields($data[13]));
                $objTable->setQtLimite($this->cleanFields($data[14]));

                if (! $objTable->getNuEmpregado()) {
                    $empregadosNaoEncontrados[] = $this->getEmpregadoDesconhecido($data);
                } else {
                    try {
                        $objMapper->save($objTable);
                        $totalCarregado ++;
                    } catch (Exception $e) {
                        var_dump($e);
                        if ($e->getCode() == '23505') {
                            $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                        }
                    }
                }
            }
            $row ++;
        }
        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function saveLiga($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable, $numeroArquivo)
    {
        $objMapper->deleteByDtReferencia($dtReferencia, $numeroArquivo);
        $totalCarregado = 0;
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setMes_referencia($dtReferencia);
                $objTable->setCgc($this->cleanFields($data[0]));
                $objTable->setUnidade($this->cleanFields($data[1]));
                $objTable->setDdd($this->cleanFields($data[2]));
                $objTable->setNumero($this->cleanFields($data[3]));
                $objTable->setNome_usuario($this->cleanFields($data[4]));
                $objTable->setDescricao($this->cleanFields($data[5]));
                $objTable->setNumero_chamado($this->cleanFields($data[6]));
                $objTable->setHora_ocorrencia($this->cleanFields($data[7]));
                $objTable->setDuracao($this->cleanFields($data[8]));
                $objTable->setDuracao_minutos(str_replace(',', '.', $this->cleanFields($data[9])));
                $objTable->setValor(str_replace(',', '.', $this->cleanFields($data[10])));
                $objTable->setNome($this->cleanFields($data[11]));
                $objTable->setNr_arquivo($numeroArquivo);

                try {
                    $objMapper->save($objTable);
                    $totalCarregado ++;
                } catch (Exception $e) {
                    error_log($e);
                    var_dump($e);
                    if ($e->getCode() == '23505') {
                        $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                    }
                }
            }
            $row ++;
        }

        $lastDay = $objMapper->getUltimoDiaMes($dtReferencia);
        $empregadosNaoEncontrados = $objMapper->getRamaisNotFound($dtReferencia, $lastDay);
        $falha = 0;

        for($i = 0; $i < sizeof($empregadosNaoEncontrados); $i++) {
            $empregadosNaoEncontrados[$i] = join(" ", $empregadosNaoEncontrados[$i]);
        }

        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function savePrestadores($handle, $objMapper, $linhaInicio, $dtReferencia, $objTable)
    {
        $objMapper->deleteByDtReferencia($dtReferencia);
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && $data[0] != '') {
                $objTable->setData_referencia($dtReferencia);
                $objTable->setCpf($this->cleanFields($data[0]));
                $objTable->setStatus($this->cleanFields($data[1]));
                $objTable->setMatricula($this->cleanFields($data[2]));
                $objTable->setRestricao($this->cleanFields($data[3]));
                $objTable->setLotacao($this->cleanFields($data[4]));
                $objTable->setLotacao_fisica($this->cleanFields($data[6]));
                $objTable->setEmpresa($this->cleanFields($data[7]));
                $objTable->setTipo($this->cleanFields($data[8]));
                $objTable->setNome($this->cleanFields($data[9]));
                $objTable->setSexo($this->cleanFields($data[10]));
                $objTable->setData_nascimento($this->cleanFields($data[11]));
                $objTable->setIdentidade($this->cleanFields($data[12]));
                $objTable->setOrgao_emissor($this->cleanFields($data[13]));
                $objTable->setNome_mae($this->cleanFields($data[14]));

                if (! $objTable->getNome()) {
                    $empregadosNaoEncontrados[] = $this->getEmpregadoDesconhecido($data);
                } else {
                    try {
                        $objMapper->save($objTable);
                        $totalCarregado ++;
                    } catch (Exception $e) {
                        error_log($e);
                        var_dump($e);
                        if ($e->getCode() == '23505') {
                            $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                        }
                    }
                }
            }
            $row ++;
        }
        return array(
            $falha,
            $empregadosNaoEncontrados,
            $totalCarregado
        );
    }

    private function saveFundamental($handle, $objMapper, $linhaInicio, $dtReferencia)
    {
        $objMapper->deleteByDtReferencia($dtReferencia);
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && ! empty($data[2])) {
                $objTable = new Application_Model_UnivTrilhaFundamental();
                $objTable->setDtReferencia($dtReferencia);
                $objTable->setNuEmpregado($this->getNuFuncionario(trim($data[2])));
                $objTable->setNoMatrFunc($data[2]);
                $objTable->setNoFuncionario($data[6]);
                if (empty($data[17])) {
                    $objTable->setPassosTrilhados('0');
                } else {
                    $objTable->setPassosTrilhados(floatval(round(str_replace(",", ".", $data[17]), 2)));
                }

                if (empty($data[13])) {
                    $objTable->setPassosTotal('0');
                } else {
                    $objTable->setPassosTotal(floatval(round(str_replace(",", ".", $data[13]), 2)));
                }

                if (empty($data[20])) {
                    $objTable->setPassosPorcentagem('0');
                } else {
                    $objTable->setPassosPorcentagem(floatval(round(str_replace(",", ".", $data[20]), 2)));
                }

                if (! $objTable->getNuEmpregado()) {
                    $empregadosNaoEncontrados[] = $this->getEmpregadoDesconhecido($data);
                } else {
                    try {
                        $objMapper->save($objTable);
                        $totalCarregado ++;
                    } catch (Exception $e) {
                        if ($e->getCode() == '23505') {
                            $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                        }
                    }
                }
            }
            $row ++;
        }
        $this->sucessoCarga($falha, $empregadosNaoEncontrados, $totalCarregado);
    }

    private function saveLider($handle, $objMapper, $linhaInicio, $dtReferencia)
    {
        $objMapper->deleteByDtReferencia($dtReferencia);
        $totalCarregado = 0;
        $empregadosNaoEncontrados = array();
        $falha = "";
        $row = 1;
        while (($data = fgetcsv($handle, 1000, ";")) !== false) {
            if ($row >= $linhaInicio && ! empty($data[2])) {
                $objTable = new Application_Model_UnivTrilhaLider();
                $objTable->setDtReferencia($dtReferencia);
                $objTable->setNuEmpregado($this->getNuFuncionario(trim($data[2])));
                $objTable->setNuFuncao($this->getNuFuncao(trim($data[2])));
                $objTable->setDtReferencia($dtReferencia);
                $objTable->setNoMatrFunc($data[2]);
                $objTable->setNoFuncionario($data[6]);
                if (empty($data[17])) {
                    $objTable->setPassosTrilhados('0');
                } else {
                    $objTable->setPassosTrilhados(floatval(round(str_replace(",", ".", $data[17]), 2)));
                }

                if (empty($data[13])) {
                    $objTable->setPassosTotal('0');
                } else {
                    $objTable->setPassosTotal(floatval(round(str_replace(",", ".", $data[13]), 2)));
                }

                if (empty($data[20])) {
                    $objTable->setPassosPorcentagem('0');
                } else {
                    $objTable->setPassosPorcentagem(floatval(round(str_replace(",", ".", $data[20]), 2)));
                }

                if (! $objTable->getNuEmpregado()) {
                    $empregadosNaoEncontrados[] = $this->getEmpregadoDesconhecido($data);
                } else {
                    try {
                        // var_dump($objTable);
                        $objMapper->save($objTable);
                        $totalCarregado ++;
                    } catch (Exception $e) {
                        if ($e->getCode() == '23505') {
                            $falha = 'Arquivo já carregado anteriormente. Confira os dados e selecione a opção "Sobrescrever caso já existam registros para o mês informado" caso seja necessário';
                        }
                    }
                }
            }
            $row ++;
        }
        $this->sucessoCarga($falha, $empregadosNaoEncontrados, $totalCarregado);
    }

    private function fixDate($value)
    {
        $months = array(
            'jan',
            'fev',
            'mar',
            'abr',
            'mai',
            'jun',
            'jul',
            'ago',
            'set',
            'out',
            'nov',
            'dez'
        );
        $parts = explode('-', $value);
        return $parts[0] . '/' . (array_search($parts[1], $months) + 1) . '/' . '2015';
    }

    private function toFloat($value)
    {
        $roundNumber;
        if (empty($value)) {
            $roundNumber = '0';
        } else {
            $roundNumber = floatval(round(str_replace(",", ".", $value), 2));
        }
        return $roundNumber;
    }

    private function testHour($value)
    {
        return $this->hoursToMinutes($value);
    }

    private function getNuFuncionario($value)
    {
        $objFuncSiptiMapper = new Application_Model_FuncionarioSiptiMapper();
        $objFuncionarioSipti = $objFuncSiptiMapper->fetchByUserId($value);
        return $objFuncionarioSipti->getNuFuncionario();
    }

    private function getNuFuncionarioByName($value)
    {
        $objFuncSiptiMapper = new Application_Model_FuncionarioSiptiMapper();
        $objFuncionarioSipti = $objFuncSiptiMapper->fetchByUserName($value);
        return $objFuncionarioSipti->getNuFuncionario();
    }

    private function getNuFuncao($value)
    {
        $objFuncSiptiMapper = new Application_Model_FuncionarioSiptiMapper();
        $objFuncionarioSipti = $objFuncSiptiMapper->fetchByUserId($value);
        return $objFuncionarioSipti->getNuFuncao();
    }

    private function getEmpregadoDesconhecido($value)
    {
        $desc = '';
        for ($i = 0; $i < count($value); $i ++) {
            $desc = $desc . ' ' . $value[$i];
        }
        return $desc;
    }

    private function sucessoCarga($falha, $empregadosNaoEncontrados, $totalCarregado)
    {
        $this->view->erro = $falha;
        $this->view->empregadosNaoEncontrados = $empregadosNaoEncontrados;
        $this->view->totalCarregado = $totalCarregado;
        $this->view->totalNaoCarregado = count($empregadosNaoEncontrados);
        $this->render("sucesso-carga");
    }

    public function atualizacaoDadosAction()
    {
        $this->view->title = "Atualização de Dados";
        $objParametrosMapper = new Application_Model_ParametroSistemaMapper();
        $rs = $objParametrosMapper->fetchAll();
        foreach ($rs as $key => $value) {
            $chave = $value->getNoChaveParametro();
            $this->view->$chave = $value->getNoValorParametro();
        }
    }

    public function ajaxAtualizaEmpregadosAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        // Atualizando cargos
        $objMapperCargo = new Application_Model_CargoSiptiMapper();
        $objMapperCargo->deleteAll();
        $objMapperCargo->cargaSipti();

        // Atualizando funções
        $objMapperFuncao = new Application_Model_FuncaoSiptiMapper();
        $objMapperFuncao->deleteAll();
        $objMapperFuncao->cargaSipti();

        // Atualizando empregados
        $objMapperFuncionario = new Application_Model_FuncionarioSiptiMapper();
        $objMapperFuncionario->deleteAll();
        $objMapperFuncionario->cargaSipti();

        // Atualizando o histórico de área dos empregados
        $objMapperHstro = new Application_Model_HstroFuncionarioAreaSiptiMapper();
        $objMapperHstro->deleteAll();
        $objMapperHstro->cargaSipti();

        // Salvando a data da última atualização
        $objParametroSistema = new Application_Model_ParametroSistema();
        $objParametroSistema->setNuParametroSistema(1);
        $objParametroSistema->setNoChaveParametro('DT_CARGA_EMPREGADOS');
        $objParametroSistema->setNoValorParametro(date('d/m/Y'));

        // //Atualizando os sobreavisos
        // $objSobreaviso = new Application_Model_SobreavisoMapper();
        // $objSobreaviso->deleteAll();
        // $objSobreaviso->cargaSipti();
        //
        // //Atualizando a ligação sobreaviso sistemas
        // $objSobreavisoSistema = new Application_Model_SobreavisoSistemaMapper();
        // $objSobreavisoSistema->deleteAll();
        // $objSobreavisoSistema->cargaSipti();

        // Atualizando os sistemas
        $objSistema = new Application_Model_SistemaMapper();
        $objSistema->deleteAll();
        $objSistema->cargaSipti();

        $objParametroSistemaMapper = new Application_Model_ParametroSistemaMapper();
        $objParametroSistemaMapper->save(($objParametroSistema));

        $arrRetorno['type'] = 'success';
        $arrRetorno['data'] = date('d/m/Y');

        echo Zend_Json_Encoder::encode($arrRetorno);
        die();
    }

    public function ajaxAtualizaAreasAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        // Atualizando a tabela tipo_area

        $objMapperTipoAreaSipti = new Application_Model_TipoAreaSiptiMapper();
        $objMapperTipoAreaSipti->deleteAll();
        $objMapperTipoAreaSipti->cargaSipti();

        // Atualizando a tabela area

        $objMapperArea = new Application_Model_AreaSiptiMapper();
        $objMapperArea->deleteAll();
        $objMapperArea->cargaSipti();

        // Salvando a data da última atualização
        $objParametroSistema = new Application_Model_ParametroSistema();
        $objParametroSistema->setNuParametroSistema(2);
        $objParametroSistema->setNoChaveParametro('DT_CARGA_AREAS');
        $objParametroSistema->setNoValorParametro(date('d/m/Y'));

        $objParametroSistemaMapper = new Application_Model_ParametroSistemaMapper();
        $objParametroSistemaMapper->save(($objParametroSistema));

        $arrRetorno['type'] = 'success';
        $arrRetorno['data'] = date('d/m/Y');

        echo Zend_Json_Encoder::encode($arrRetorno);
        die();
    }

    public function hoursToMinutes($hours)
    {
        // Transform hours like "1:45" into the total number of minutes, "105".
        $correct;
        $minutes = 0;
        if (strpos($hours, ':') !== false) {
            // Split hours and minutes.
            list ($hours, $minutes) = explode(':', $hours);
        }
        $correct = $hours * 60 + $minutes;
        if ($correct == "") {
            $correct = "0";
        }
        return $correct;
    }

    private function cleanFields($value = '')
    {
        $value = trim($value, chr(0xC2) . chr(0xA0));
        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
        $value = trim($value);
        // mb_convert_encoding(trim(str_replace("?", "",preg_replace( '/[^[:print:]]/','',$value))), 'UTF-8', 'Windows-1252');
        // mb_convert_encoding(trim(str_replace("?", "",preg_replace( '/[^[:print:]]/','',$value))), 'UTF-8', 'Windows-1252');
        // mb_convert_encoding(trim(str_replace("?", "",preg_replace( '/[^[:print:]]/','',$value))), 'UTF-8', 'Windows-1252');
        // return mb_convert_encoding(trim(str_replace("?", "",preg_replace( '/[^[:print:]]/','',$value))), 'UTF-8', 'Windows-1252');
        return $value;
    }

    public function minutesToHours($minutes)
    {
        // Transform minutes like "105" into hours like "1:45".
        $hours = (int) ($minutes / 60);
        $minutes -= $hours * 60;
        return sprintf("%d:%02.0f", $hours, $minutes);
    }

    private function formataValor($strArquivo)
    {
        $valorFinal = str_replace(".", "", (str_replace("R$", "", $strArquivo)));
        return str_replace(",", ".", $valorFinal);
    }
}
