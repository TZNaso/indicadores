$(document).ready(function() {
    loadMsg();

    var build = new buildView();

    var tabelas = {
        ponto : $('#tbodypontoAberto'),
        areg  : $('#tbodyAreg'),
        maior : $('#tbodyMaiorHora'),
        menor : $('#tbodyMenorHora'),
        intev : $('#tbodyausenciaIntervalo'),
        faltas: $('#tbodyfaltaAbonada'),
        faltaNH : $('#tbodyfaltaNaoHomologada'),
        ausenciaNH : $('#tbodyausenciaNaoHomologada'),
        bloqueio : $('#tbodybloqueioTrava')
    };

    var labels = {
        somAberto : $('#somAberto'),
        somAREG : $('#somAREG'),
        somHE : $('#somHE'),
        somIntervalo : $('#somIntervalo'),
        somAusencia : $('#somAusencia'),
        somFaltas : $('#somFaltas'),
        somFaltaNH : $('#somFaltaNH'),
        somAusenciaNH : $('#somAusenciaNH'),
        somBloqueio : $('#somBloqueio')
    };

    $.ajax({
        url: 'ocorrencias-sipon-diario/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $("#dtReferencia option:selected").text(),
        dataType: 'json',
        success: function(retorno) {
            hideMsg();
            cleanTabs();
            build.populateAll(tabelas,retorno.dadosEmpregados,labels);
        }
    });


    $('#dtReferencia').change(function(e) {
        loadMsg();
        cleanTabs();
        $.ajax({
            url: 'ocorrencias-sipon-diario/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function(retorno) {
                hideMsg();
                build.populateAll(tabelas,retorno.dadosEmpregados,labels);
            }
        });
    });

    function cleanTabs() {
        labels.somAberto.text('---');
        labels.somAREG.text('---');
        labels.somHE.text('---');
        labels.somIntervalo.text('---');
        labels.somAusencia.text('---');
        labels.somFaltas.text('---');
        labels.somFaltaNH.text('---');
        labels.somAusenciaNH.text('---');
        labels.somBloqueio.text('---');
        tabelas.ponto.empty();
        tabelas.areg.empty();
        tabelas.maior.empty();
        tabelas.menor.empty();
        tabelas.intev.empty();
        tabelas.faltas.empty();
        tabelas.faltaNH.empty();
        tabelas.ausenciaNH.empty();
        tabelas.bloqueio.empty();
    }

});


function buildView() {


    buildView.prototype.populateAll = function(tabelas, dadosEmpregados,labels) {

        var somAberto = 0 ,somAREG = 0,somHE = 0,
        somIntervalo = 0,somAusencia = 0,somFaltas = 0
        ,somFaltaNH = 0,somAusenciaNH = 0, somBloqueio = 0;

        for ( var pick in dadosEmpregados){
            var empregado = dadosEmpregados[pick];
            if (empregado.qt_195) {
                somAberto += parseInt(empregado.qt_195);
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_195 + "</td>";
                html += "</tr>";
                tabelas.ponto.append(html);
            }

            if (empregado.qt_56) {
                somAREG +=parseInt(empregado.qt_56);
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_56 + "</td>";
                html += "</tr>";
                tabelas.areg.append(html);
            }

            if (empregado.qt_70) {
                somHE +=parseInt(empregado.qt_70);
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_70 + "</td>";
                html += "</tr>";
                tabelas.maior.append(html);
            }

            if (empregado.qt_57) {
                somIntervalo +=parseInt(empregado.qt_57);
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_57 + "</td>";
                html += "</tr>";
                tabelas.menor.append(html);
            }

            if (empregado.qt_58) {
                somAusencia +=parseInt(empregado.qt_58);
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_58 + "</td>";
                html += "</tr>";
                tabelas.intev.append(html);
            }

            if (empregado.qt_53) {
                somFaltas +=1;
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_53 + "</td>";
                html += "</tr>";
                tabelas.faltas.append(html);
            }
            
            if (empregado.qt_19) {
            	somFaltaNH +=1;
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_19 + "</td>";
                html += "</tr>";
                tabelas.faltaNH.append(html);
            }
            
            if (empregado.qt_20) {
            	somAusenciaNH +=1;
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_20 + "</td>";
                html += "</tr>";
                tabelas.ausenciaNH.append(html);
            }
            
            if (empregado.qt_bloqueio) {
            	somBloqueio +=1;
                html = "<tr>";
                html += "<td>" + empregado.coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + empregado.qt_bloqueio + "</td>";
                html += "</tr>";
                tabelas.bloqueio.append(html);
            }
            $.bootstrapSortable(applyLast=true,'AZ');
        }
        labels.somAberto.text(somAberto);
        labels.somAREG.text(somAREG);
        labels.somHE.text(somHE);
        labels.somIntervalo.text(somIntervalo);
        labels.somAusencia.text(somAusencia);
        labels.somFaltas.text(somFaltas);
        labels.somFaltaNH.text(somFaltaNH);
        labels.somAusenciaNH.text(somAusenciaNH);
        labels.somBloqueio.text(somBloqueio);
    };
}
