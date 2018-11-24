$(document).ready(function () {
    loadMsg();
    $("#horasExtras").addClass("active");
    $("#ocorrenciasSipon").removeClass("active");

    var repopHE = new RepopHE();
    var graphicHE = new GraphicHE();

    //ajax principal
    $.ajax({
        url:  'horas-extras/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $( "#dtReferencia option:selected" ).text(),
        dataType: 'json',
        success: function (retorno) {
            console.log(retorno);
            hideMsg();
            graphicHE.createCharts(retorno);
            repopHE.populateAll(retorno);
        }
    });

    //Ajax da mudança da seleção do mês

    $('#dtReferencia').change(function (e) {
        loadMsg();
        var tbodyCoordenacao = $("#tbodyCoordenacao");
        var tbodyEquipe = $("#tbodyEquipe");
        var tbodyEmpregado = $("#tbodyEmpregado");

        $.ajax({
            url: 'horas-extras/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {
                hideMsg();
                popHidden();

                tbodyCoordenacao.hide();
                tbodyEquipe.hide();
                tbodyEmpregado.hide();

                graphicHE.createSimple(retorno);
                repopHE.populateAll(retorno);

                tbodyCoordenacao.show('slow');
                tbodyEquipe.show('slow');
                tbodyEmpregado.show('slow');
            }
        });
    });

    $('#area').change(function (e) {
        $("#tbodyEmpregado tr").each(function (index) {
            if ($("#area").val() !== 0) {
                if ($(this).attr('id') != $("#area").val()) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            } else {
                $(this).show();
            }
        });
    });

    $('#selecionaTodos').click(function (e) {
        $("#exportacao input[type='checkbox']").attr('checked', true);
    });

    $("#btnExportar").click(function (e) {
        $("#formExportacao").submit();
    });

    $("#btnExportarAnual").click(function (e) {
        $("#formExportacaoAnual").submit();
    });

    popHidden();
});


function RepopHE () {

    var areaList = $("#area");

    var tbodyCoordenacao = $("#tbodyCoordenacao");
    var tbodyEquipe = $("#tbodyEquipe");
    var tbodyEmpregado = $("#tbodyEmpregado");

    var GeralCEDESBRTitulo = $("#GeralCEDESBR #titulo");
    var porCoordenacaoTitulo = $("#porCoordenacao #titulo");
    var porEquipeTitulo = $("#porEquipe #titulo");
    var porEmpregadoTitulo = $("#porEmpregado #titulo");

    var ttotalHoras = $("#totalHorasCEDES");
    var ttotalValor = $("#totalValorCEDES");

    RepopHE.prototype.populateAll = function(argument) {
        this.Empregado(argument);
        this.Areas(argument);
        this.Tabelas(
            tbodyEquipe,
            porEquipeTitulo,
            argument.dt_selecionada,
            this.generateGeneric(argument.dadosEmpregados,'area')
            );
        this.Tabelas(
            tbodyCoordenacao,
            porCoordenacaoTitulo,
            argument.dt_selecionada,
            this.generateGeneric(argument.dadosEmpregados,'no_sigla_coord')
            );
    };

    RepopHE.prototype.generateGeneric = function(Things, field) {
        var names = {};
        for (var i = 0; i < Things.length; i++) {
            var team = Things[i][field];
            names[team] = [];
        }
        for (i = 0; i < Things.length; i++) {
            var time = Things[i][field];
            names = this.TestVazio(names, time);
            names[time].nome = time;
            names[time].nu_he_comp_284 += parseInt(Things[i].nu_he_comp_284);
            names[time].nu_he_pg_285 += parseInt(Things[i].nu_he_pg_285);
            names[time].nu_he_pg_296 += parseInt(Things[i].nu_he_pg_296);

            names[time].nu_he_pg_302 += parseInt(Things[i].nu_he_pg_302);
            names[time].nu_he_pg_demais_proj +=parseInt(Things[i].nu_he_pg_demais_proj);
            names[time].nu_total_he +=parseInt(Things[i].nu_total_he);

            names[time].nu_valor_he_comp_284 += parseFloat(Things[i].nu_valor_he_comp_284);
            names[time].nu_valor_he_pg_285 += parseFloat(Things[i].nu_valor_he_pg_285);
            names[time].nu_valor_he_pg_296 += parseFloat(Things[i].nu_valor_he_pg_296);

            names[time].nu_valor_he_pg_302 += parseFloat(Things[i].nu_valor_he_pg_302);
            names[time].nu_valor_he_pg_demais_proj += parseFloat(Things[i].nu_valor_he_pg_demais_proj);
            names[time].nu_valor_total_he += parseFloat(Things[i].nu_valor_total_he);
        }
        names = _.sortBy(names,'nome');
        return names;
    };

    RepopHE.prototype.Tabelas = function(tabela, titulo, dta, dados) {
        titulo.html("Data de referência - " + dta);
        tabela.empty();
        var html = "";
        var totalValor = 0;
        var totalTempo = 0;
        for (var times in dados) {

            var time = dados[times];

            if (tabela == tbodyCoordenacao){
                totalValor += time.nu_valor_total_he;
                totalTempo += time.nu_total_he;

            }

            html = "<tr>";
            html += "<td>" + time.nome  + "</td>";
            html += "<td>" + formatTime(time.nu_he_pg_285) + "</td>";
            html += "<td>" + formatTime(time.nu_he_pg_296) + "</td>";
            html += "<td>" + formatTime(time.nu_he_pg_302) + "</td>";
            html += "<td>" + formatTime(time.nu_he_pg_demais_proj) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_he_pg_285) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_he_pg_296) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_he_pg_302) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_he_pg_demais_proj) + "</td>";
            html += "<td>" + formatTime(time.nu_he_comp_284) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_he_comp_284) + "</td>";
            html += "<td>" + formatTime(time.nu_total_he) + "</td>";
            html += "<td>" + formataMoeda(time.nu_valor_total_he) + "</td>";
            html += "</tr>";
            tabela.append(html);
        }
        ttotalHoras.text(formatTime(totalTempo));
        ttotalValor.text(formataMoeda(totalValor));
    };

    RepopHE.prototype.TestVazio = function(names, area) {
     if (_.isUndefined(names[area].nu_he_comp_284)) {
        names[area].nu_he_comp_284 = 0;
    }
    if (_.isUndefined(names[area].nu_he_pg_285)) {
        names[area].nu_he_pg_285 = 0;
    }
    if (_.isUndefined(names[area].nu_he_pg_296)) {
        names[area].nu_he_pg_296 = 0;
    }
    if (_.isUndefined(names[area].nu_he_pg_302)) {
        names[area].nu_he_pg_302 = 0;
    }
    if (_.isUndefined(names[area].nu_he_pg_demais_proj)) {
        names[area].nu_he_pg_demais_proj = 0;
    }
    if (_.isUndefined(names[area].nu_total_he)) {
        names[area].nu_total_he = 0;
    }
    if (_.isUndefined(names[area].nu_valor_he_comp_284)) {
        names[area].nu_valor_he_comp_284 = 0;
    }
    if (_.isUndefined(names[area].nu_valor_he_pg_285)) {
        names[area].nu_valor_he_pg_285 = 0;
    }
    if (_.isUndefined(names[area].nu_valor_he_pg_296)) {
        names[area].nu_valor_he_pg_296 = 0;
    }
    if (_.isUndefined(names[area].nu_valor_he_pg_302)) {
        names[area].nu_valor_he_pg_302 = 0;
    }
    if (_.isUndefined(names[area].nu_valor_he_pg_demais_proj)) {
        names[area].nu_valor_he_pg_demais_proj = 0;
    }
    if (_.isUndefined(names[area].nu_valor_total_he)) {
        names[area].nu_valor_total_he = 0;
    }
    return names;
};

RepopHE.prototype.Empregado = function(retorno) {
    porEmpregadoTitulo.html("Totalização por Empregado - " + retorno.dt_selecionada);
    tbodyEmpregado.empty();
    var html = "";
    var length = retorno.dadosEmpregados.length;
    for (var i = 0; i < length; i++) {
        var empregado = retorno.dadosEmpregados[i];
        html = "<tr>";
        html = "<tr id='" + empregado.nu_area + "'>";
        html += "<td>" + empregado.no_funcionario + "</td>";
        html += "<td>" + formatTime(empregado.nu_he_pg_285) + "</td>";
        html += "<td>" + formatTime(empregado.nu_he_pg_296) + "</td>";
        html += "<td>" + formatTime(empregado.nu_he_pg_302) + "</td>";
        html += "<td>" + formatTime(empregado.nu_he_pg_demais_proj) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_he_pg_285) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_he_pg_296) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_he_pg_302) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_he_pg_demais_proj) + "</td>";
        html += "<td>" + formatTime(empregado.nu_he_comp_284) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_he_comp_284) + "</td>";
        html += "<td>" + formatTime(empregado.nu_total_he) + "</td>";
        html += "<td>" + formataMoeda(empregado.nu_valor_total_he) + "</td>";
        html += "</tr>";
        tbodyEmpregado.append(html);
    }
};

RepopHE.prototype.Areas = function(retorno) {
    areaList.empty();
    var html = "";
    areaList.append("<option value='0'>Selecione...</option>");
    $.each(retorno.areasDisponiveis, function (index, value) {
        html = "<option value='" + value.nu_area + "'>";
        html += value.area;
        html += "</option>";
        areaList.append(html);
    });
};
}

function formatTime(qtMinutos) {
    var minutos = parseInt( qtMinutos) % 60;
    if (minutos.toString().length == 1 ) {
        minutos = "0"+ minutos;
    }
    var horasAux = (parseInt( qtMinutos) / 60).toString().split(".");
    var horas = horasAux[0];
    if (horas.toString().length ==1) {
        horas = "0"+ horas;
    }
    var aaa = String(horas + ":" + minutos);
    return aaa;
}

function clickGeralCEDESBR () {
    hideAll();
    $("#GeralCEDESBR").show("slow");
    $("#tabGeralCEDESBR").addClass('active');
}

function clickPorCoordenacao() {
    hideAll();
    $("#porCoordenacao").show("slow");
    $("#tabPorCoordenacao").addClass('active');
}

function clickPorEquipe() {
    hideAll();
    $("#porEquipe").show("slow");
    $("#tabPorEquipe").addClass('active');
}

function clickPorEmpregado() {
    hideAll();
    $("#porEmpregado").show("slow");
    $("#tabPorEmpregado").addClass('active');
}

function clickExportacao() {
    hideAll();
    $("#exportacao").show("slow");
    $("#tabExportacao").addClass('active');
}

function clickExportacaoAnual() {
    hideAll();
    $("#exportacaoAnual").show("slow");
    $("#tabExportacaoAnual").addClass('active');
}

function hideAll () {
    $("#GeralCEDESBR").hide();
    $("#porCoordenacao").hide();
    $("#porEquipe").hide();
    $("#porEmpregado").hide();
    $("#exportacao").hide();
    $("#exportacaoAnual").hide();

    $("#tabGeralCEDESBR").removeClass('active');
    $("#tabPorCoordenacao").removeClass('active');
    $("#tabPorEquipe").removeClass('active');
    $("#tabPorEmpregado").removeClass('active');
    $("#tabExportacao").removeClass('active');
    $("#tabExportacaoAnual").removeClass('active');
}

function popHidden () {
   $('#dtReferenciaExportacao').val($('#dtReferencia').val());
}



function formataMoeda(arg) {
    arg = parseFloat(arg).toFixed(2)
    .replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
    return arg;
}

