$(document).ready(function () {
    loadMsg();
    var acumulado = new Acumulado();
    // ajax principal
    $.ajax({
        url:  'horas-extras-acumulado/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $( "#dtReferencia option:selected" ).val(),
        dataType: 'json',
        success: function (retorno) {
            hideMsg();
            acumulado.mainGateway(retorno);
        }
    });

    //Ajax da mudança da seleção do mês
    $('#dtReferencia').change(function (e) {
        loadMsg();
        var tbodyCoordenacao = $("#tbodyCoordenacao");
        var tbodyEmpregado = $("#tbodyEmpregado");

        $.ajax({
            url:  'horas-extras-acumulado/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {
                hideMsg();
                acumulado.mainGateway(retorno);
            }
        });
    });
    $('#coords').change(function (e) {
        var tableChildrens = $("#tbodyEmpregado tr");
        var valu = $('#coords').val();
        for (var i = 0; i < tableChildrens.length; i++) {
            if (parseInt(valu) === 0) {
                $(tableChildrens[i]).children(0).show();
            } else{
                var test = $(tableChildrens[i]).children(0).first().text();
                var line = $(tableChildrens[i]).children(0);
                if (test == valu) {
                    line.show();
                } else {
                    line.hide();
                }
            }
        }
    });

});


function Acumulado(argument) {
    var tbodyEmpregado = $('#tbodyEmpregado');
    var tbodyCoordenacao = $('#tbodyCoordenacao');
    var ttotalHoras = $("#totalHorasCEDES");
    var ttotalValor = $("#totalValorCEDES");

    // var dtFinal = $('#DtFinal');
    // var totalCedes = $('#totalCedes');

    Acumulado.prototype.mainGateway = function(retorno) {
        this.popularEmpregados( retorno.dadosMeta, retorno.dadosEmpregados);
    };

    // Acumulado.prototype.setDatas = function(inicio , fim) {
    //     dtInicial.text(this.formatDate(inicio));
    //     dtFinal.text(this.formatDate(fim));
    // };

    Acumulado.prototype.formatDate = function(argument) {
        var datePart = argument.match(/\d+/g);
        var year = datePart[0];
        var month = datePart[1];
        var day = datePart[2];
        return day+'/'+month+'/'+year;
    };

    Acumulado.prototype.generateCoords = function(metas, coords, empregados) {
        var names = {};
        for (var j = 0; j < coords.length; j++) {
            names[coords[j]] = [0];
        }
        for (var i = 0; i < empregados.length; i++) {
            names[empregados[i].no_sigla_coord][0] += parseFloat(empregados[i].valor_he);
        }
        this.popularCoords(metas, names);
    };

    Acumulado.prototype.popularCoords = function(metas, names) {
        tbodyCoordenacao.empty();
        var html = "";
        var rMetas = {};
        for(var okey in metas){
            rMetas[metas[okey].coord]=metas[okey].meta;
        }
        for (var key in names){
            // console.log(names[key]);
            var val = parseFloat(names[key]);
            var saldo = 0;
            if (rMetas[key]) {
                saldo = parseFloat(rMetas[key]) - parseFloat(names[key]) ;
            }

            html = "<tr>";
            html += "<td>" + key + "</td>";
            html += "<td>" + formatMoeda(val.toFixed(2)) + "</td>";
            html += "<td>" + formatMoeda(saldo.toFixed(2)) + "</td>";
            html += "</tr>";
            tbodyCoordenacao.append(html);
        }
    };

    Acumulado.prototype.fillSelect = function(coords) {
        var sel = $('#coords');
        var childs = sel.children();
        //limpar o select
        for (var j = 0; j < childs.length; j++) {
            if(j >0){
                $(childs[j]).remove();
            }
        }
        //repopular
        for(var i = 0; i < coords.length; i++) {
            var opt = coords[i];
            var el = document.createElement("option");
            el.textContent = opt;
            el.value = opt;
            sel.append(el);
        }
    };

    Acumulado.prototype.popularEmpregados = function(metas, merged) {
        var coords = [];
        var html = "";
        tbodyEmpregado.empty();

        var totalValor = 0;
        var totalTempo = 0;

        for (var i = 0; i < merged.length; i++) {
            var empregado = merged[i];
            coords.push(empregado.no_sigla_coord);


            // console.log(empregado);

            totalValor += parseFloat(empregado.valor_he);
            totalTempo += parseInt(empregado.total_he);



            html = "<tr>";
            html += "<td>" + empregado.no_sigla_coord + "</td>";
            html += "<td>" + empregado.no_matricula_caixa + "</td>";
            html += "<td>" + empregado.no_funcionario + "</td>";
            html += "<td>" + formatTime(empregado.total_he) + "</td>";
            html += "<td>" + formatMoeda(empregado.valor_he) + "</td>";
            html += "</tr>";
            tbodyEmpregado.append(html);
        }

        ttotalHoras.text(formatTime(totalTempo));
        ttotalValor.text(formatMoeda(totalValor));

        coords =  _.uniq(coords.sort());
        this.fillSelect(coords);
        this.generateCoords(metas, coords, merged);
    };

    Acumulado.prototype.toInt = function(argument) {
        argument.he_total = parseInt(argument.he_total);
        argument.nu_funcionario = parseInt(argument.nu_funcionario);
        return argument;
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
    return horas + ":" + minutos;
}

function formatMoeda(arg) {
    return parseFloat(arg).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
}



function clickPorCoordenacao() {
    hideAll();
    $("#porCoordenacao").show("slow");
    $("#tabPorCoordenacao").addClass('active');
}

function clickPorEmpregado() {
    hideAll();
    $("#porEmpregado").show("slow");
    $("#tabPorEmpregado").addClass('active');
}

function hideAll () {
    $("#porCoordenacao").hide();
    $("#porEmpregado").hide();

    $("#tabPorCoordenacao").removeClass('active');
    $("#tabPorEmpregado").removeClass('active');
}
