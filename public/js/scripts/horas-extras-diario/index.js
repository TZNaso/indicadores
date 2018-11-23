$(document).ready(function () {
    loadMsg();
    var diario = new Diario();
    // ajax principal
    $.ajax({
        url:  'horas-extras-diario/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $( "#dtReferencia option:selected" ).val(),
        dataType: 'json',
        success: function (retorno) {
            hideMsg();
            diario.mainGateway(retorno);
        },
        error: function (argument) {
            console.log('error',argument);
        }
    });

    //Ajax da mudança da seleção do mês
    $('#dtReferencia').change(function (e) {
        loadMsg();
        var tbodyCoordenacao = $("#tbodyCoordenacao");
        var tbodyEmpregado = $("#tbodyEmpregado");

        $.ajax({
            url:  'horas-extras-diario/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {
                hideMsg();
                diario.mainGateway(retorno);
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


function Diario(argument) {
    var tbodyEmpregado = $('#tbodyEmpregado');
    var tbodyCoordenacao = $('#tbodyCoordenacao');
    var dtInicial = $('#dtInicial');
    var dtFinal = $('#DtFinal');
    var totalCedes = $('#totalCedes');

    Diario.prototype.mainGateway = function(retorno) {
        console.log(retorno);
        this.popEmpregados(retorno.dadosDtSelecionada,retorno.dadosDtAnterior);
        this.setDatas(retorno.ontem , retorno.hoje );
    };

    Diario.prototype.setDatas = function(inicio , fim) {
        dtInicial.text(this.formatDate(inicio));
        dtFinal.text(this.formatDate(fim));
    };

    Diario.prototype.formatDate = function(argument) {
        var datePart = argument.match(/\d+/g);
        var year = datePart[0];
        var month = datePart[1];
        var day = datePart[2];
        return day+'/'+month+'/'+year;
    };

    Diario.prototype.popEmpregados = function(hoje, ontem) {
        var merged = [];
        var Merging = true;
        var coords = [];
        while( Merging ) {
            if (hoje.length > 0 && ontem.length > 0) {
                var hj = this.toInt(hoje[0]);
                var ot = this.toInt(ontem[0]);
                if ( hj.nu_funcionario > ot.nu_funcionario ) {
                    ontem.shift();
                } else if ( hj.nu_funcionario < ot.nu_funcionario ) {
                    coords.push(hj.no_sigla_coord);
                    merged.push(hj);
                    hoje.shift();
                } else {
                    var horasFeitas = hj.he_total - ot.he_total;
                    if (horasFeitas && ( hj.he_total > ot.he_total )) {
                        hj.he_total = horasFeitas;
                        coords.push(hj.no_sigla_coord);
                        merged.push(hj);
                    }
                    hoje.shift();
                    ontem.shift();
                }
            } else {
                if (hoje.length > 0) {
                    for (var i = 0; i < hoje.length; i++) {
                        coords.push(hoje[i].no_sigla_coord);
                        merged.push(hoje[i]);
                    }
                }
                Merging = false;
            }
        }
        if (merged.length) {
            merged = _.sortBy(merged,'no_funcionario');
            coords =  _.uniq(coords.sort());
            this.fillSelect(coords);
            this.popularEmpregados(merged);
            this.generateCoords(coords,merged);
        } else {
            setTimeout(function(){alertPage($("#loadmsg"), "Não ocorreram horas-extras neste intervalo", "success");},1000);
            totalCedes.text('---');
            tbodyCoordenacao.empty();
            tbodyEmpregado.empty();
        }
    };


    Diario.prototype.generateCoords = function(coords,empregados) {
        var names = {};
        for (var j = 0; j < coords.length; j++) {
            names[coords[j]] = [0];
        }
        for (var i = 0; i < empregados.length; i++) {
            names[empregados[i].no_sigla_coord][0] += parseInt(empregados[i].he_total);
        }
        this.popularCoords(names);
    };



    Diario.prototype.popularCoords = function(names) {
        tbodyCoordenacao.empty();
        var html = "";
        for (var key in names){
            html = "<tr>";
            html += "<td>" + key + "</td>";
            html += "<td>" + formatTime(names[key]) + "</td>";
            html += "</tr>";
            tbodyCoordenacao.append(html);
        }
        this.generateGeral(names);
    };

    Diario.prototype.generateGeral = function(names) {
        var total = 0;
        for (var key in names){
            total = total + parseInt(names[key]);
        }
        totalCedes.text(formatTime( total) + 'hrs');
    };

    Diario.prototype.fillSelect = function(coords) {
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


        Diario.prototype.popularEmpregados = function(merged) {
            var html = "";
            tbodyEmpregado.empty();
            for (var i = 0; i < merged.length; i++) {
                var empregado = merged[i];
                html = "<tr>";
                html += "<td>" + empregado.no_sigla_coord + "</td>";
                html += "<td>" + empregado.no_matricula_caixa + "</td>";
                html += "<td>" + empregado.no_funcionario + "</td>";
                html += "<td>" + formatTime(empregado.he_total) + "</td>";
                html += "</tr>";
                tbodyEmpregado.append(html);
            }
        };

        Diario.prototype.toInt = function(argument) {
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
