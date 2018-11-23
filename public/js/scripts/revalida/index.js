$(document).ready(function () {
    loadMsg();

    var repopRevalida = new RepopRevalida();

    //ajax principal
    $.ajax({
        url:  'revalida/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $( "#dtReferencia option:selected" ).text(),
        dataType: 'json',
        success: function (retorno) {
            hideMsg();
            repopRevalida.populateAll(retorno);
        }
    });

    //Ajax da mudança da seleção do mês

    $('#dtReferencia').change(function (e) {
        loadMsg();
        var tbodyCoordenacao = $("#tbodyCoordenacao");
        var tbodyEquipe = $("#tbodyEquipe");
        var tbodyEmpregado = $("#tbodyEmpregado");

        $.ajax({
            url: 'revalida/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {
                hideMsg();
                repopRevalida.populateAll(retorno);
            }
        });
    });
});


function RepopRevalida () {

    var tbodyEmpregado = $("#tbodyEmpregado");

    RepopRevalida.prototype.populateAll = function(argument) {
        this.Tabelas(
            tbodyEmpregado,
            argument.Revalidados
        );
    };

    RepopRevalida.prototype.Tabelas = function(tabela,dados) {
        tabela.empty();
        var html = "";
        var totalGeral = 0;
        for (var empregados in dados) {
            var empregado = dados[empregados];
            totalGeral += parseFloat(empregado.total_valor);
            html = "<tr>";
            html += "<td>" + empregado.no_matricula_caixa  + "</td>";
            html += "<td>" + empregado.no_funcionario + "</td>";
            html += "<td>" + empregado.coord + "</td>";
            html += "<td>" + empregado.horas_284 + "</td>";
            html += "<td>" + empregado.horas_285 + "</td>";
            html += "<td>" + 'R$ '+formataMoeda(empregado.valor_284) + "</td>";
            html += "<td>" + 'R$ '+formataMoeda(empregado.valor_285) + "</td>";
            html += "<td>" + empregado.total_horas + "</td>";
            html += "<td>" +'R$ '+formataMoeda(empregado.total_valor) + "</td>";
            html += "</tr>";
            tabela.append(html);
        }
        $("#totalGeral").text('R$ '+formataMoeda(totalGeral));
        $("#totalCedes").text(dados.length);
    };
}


function formataMoeda(arg) {
    arg = parseFloat(arg).toFixed(2)
    .replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
    return arg;
}

