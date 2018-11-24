$(document).ready(function () {
    $("#ocorrenciasSipon").removeClass("active");
    $("#horasExtras").removeClass("active");

    //Ajax da mudança da seleção do mês

    $('#dtReferencia').change(function (e) {
        $.ajax({
            url: 'universidade-lider/ajax-atualiza-dados',
            async: false,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {

                $("#tbodyEquipe").hide();
                $("#tbodyEmpregado").hide();

                $("#porCoordenacao #titulo").html("Totalização por Coordenação - " + retorno['dt_selecionada']);
                $("#porEquipe #titulo").html("Totalização por Equipe - " + retorno['dt_selecionada']);
                $("#porEmpregado #titulo").html("Totalização por Empregado - " + retorno['dt_selecionada']);

                $("#tbodyCoordenacao").html("");

                var html = "";

                $.each(retorno['dadosCoordenacao'], function (index, value) {

                    html = "<tr>";
                    html += "<td>" + value.coordenacao + "</td>";
                    html += "<td>" + value.total_trilhado + "%</td>";
                    html += "<td>" + value.meta + "</td>";
                    html += "</tr>";

                    $("#tbodyCoordenacao").append(html);

                    html = "";

                });

                $("#tbodyEquipe").html("");

                var html = "";

                $.each(retorno['dadosEquipe'], function (index, value) {

                    html = "<tr>";
                    html += "<td>" + value.area + "</td>";
                    html += "<td>" + value.total_trilhado + "%</td>";
                    html += "<td>" + value.meta + "</td>";

                    $("#tbodyEquipe").append(html);

                    html = "";

                });

                $("#tbodyEmpregado").html("");
                var html = "";
                $.each(retorno['dadosEmpregados'], function (index, value) {
                    html = "<tr id='" + value.nu_area + "'>";
                    html += "<td>" + value.sigla_area + "</td>";
                    html += "<td>" + $.trim(value.nome_func) + "</td>";
                    html += "<td>" + value.porcentagem + "%</td>";
                    html += "<td>" +   "100%" + "</td>";
                    html += "</tr>";

                    $("#tbodyEmpregado").append(html);
                    html = "";

                });
                $("#tbodyEquipe").show('slow');
                $("#tbodyEmpregado").show('slow');
            }
        });
    });

    $('#area').change(function (e) {
        $("#tbodyEmpregado tr").each(function (index) {
            if ($("#area").val() != 0) {
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
});

function clickPorEquipe() {
    $("#porCoordenacao").hide();
    $("#tabPorCoordenacao").removeClass('active');
    $("#porEmpregado").hide();
    $("#tabPorEmpregado").removeClass('active');
    $("#exportacao").hide();
    $("#tabExportacao").removeClass('active');
    $("#exportacaoAnual").hide();
    $("#tabExportacaoAnual").removeClass('active');
    $("#porEquipe").show("slow");
    $("#tabPorEquipe").addClass('active');

}

function clickPorCoordenacao() {
    $("#porEmpregado").hide();
    $("#tabPorEmpregado").removeClass('active');
    $("#exportacao").hide();
    $("#tabExportacao").removeClass('active');
    $("#porEquipe").hide();
    $("#tabPorEquipe").removeClass('active');
    $("#exportacaoAnual").hide();
    $("#tabExportacaoAnual").removeClass('active');
    $("#porCoordenacao").show("slow");
    $("#tabPorCoordenacao").addClass('active');

}

function clickPorEmpregado() {
    $("#porCoordenacao").hide();
    $("#tabPorCoordenacao").removeClass('active');
    $("#porEquipe").hide();
    $("#tabPorEquipe").removeClass('active');
    $("#exportacao").hide();
    $("#tabExportacao").removeClass('active');
    $("#exportacaoAnual").hide();
    $("#tabExportacaoAnual").removeClass('active');
    $("#porEmpregado").show("slow");
    $("#tabPorEmpregado").addClass('active');
}