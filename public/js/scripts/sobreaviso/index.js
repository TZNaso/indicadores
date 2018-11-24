$(document).ready(function () {

    var sobreaviso = new Sobreaviso();
        sobreaviso.loadMsg();

    $.ajax({
        url:  'sobreaviso/ajax-atualiza-dados',
        async: true,
        type: 'POST',
        data:  sobreaviso.splitDate( $( "#dtReferencia option:selected" ).text()),
        dataType: 'json',
        success: function (retorno) {
            sobreaviso.mainGateway(retorno);
        },
        statusCode: {
            500: function() {
                alert("Erro no servidor/banco de dados, favor recarregar a página");
                console.log('500 ');
            }
        }
    });

    $('#dtReferencia').change(function (e) {
        sobreaviso.loadMsg();
        sobreaviso.emptyView();
        $.ajax({
            url:  'sobreaviso/ajax-atualiza-dados',
            async: true,
            type: 'POST',
            data:  sobreaviso.splitDate( $(this).val()),
            dataType: 'json',
            success: function (retorno) {
                sobreaviso.mainGateway(retorno);
            },
        statusCode: {
            500: function() {
                alert("Erro no servidor/banco de dados, favor recarregar a página");
                console.log('500 ');
            }
        }
        });
    });


    $('#coords').change(function (e) {
        var tableChildrens = $("#tbodySobreavisoEmpregado tr");
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


    function Sobreaviso () {

        var tbodySobreavisoCoord = $("#tbodySobreavisoCoord");
        var tbodySobreavisoEmpregado = $("#tbodySobreavisoEmpregado");
        var mesSobreaviso = $('#MesSobreaviso');
        var metaSobreaviso = $('#MetaSobreaviso');
        var Meta ='';

        Sobreaviso.prototype.mainGateway = function(retorno) {
            this.hideMsg();
            this.defineMeta(retorno.Meta);
            this.mergeSimples(retorno.SobrPrevisto,retorno.SobrRealizado);
        };

        /*
        * tipo 1 = previsto e não exec
        * tipo 2 = exec e não previsto
        * tipo 3 = caminho feliz
        */
        Sobreaviso.prototype.mergeSimples = function(previsto, realizado) {
            var Merging = true;
            var merged = [];
            while( Merging ) {
                if (previsto.length > 0 && realizado.length > 0 ) {
                    var prev = previsto[0];
                    prev.a_tipo = 1;
                    var real = realizado[0];
                    real.a_tipo = 2;
                    if ( parseInt(prev.nu_funcionario) < parseInt(real.nu_funcionario) ) {
                        merged.push(prev);
                        previsto.shift();
                    } else if( parseInt(prev.nu_funcionario) > parseInt(real.nu_funcionario) ){
                        merged.push(real);
                        realizado.shift();
                    } else {
                        if(parseInt(prev.total)>= parseInt(real.total)){
                            prev.a_tipo = 3;
                            merged.push(prev);
                            previsto.shift();
                            realizado.shift();
                        } else {
                            prev.a_tipo = 3;
                            merged.push(prev);
                            real.total = parseInt(real.total) - parseInt(prev.total);
                            merged.push(real);
                            previsto.shift();
                            realizado.shift();
                        }
                    }
                } else {
                    if (previsto.length > 0) {
                        for (var i = 0; i < previsto.length; i++) {
                            previsto[i].a_tipo = 1;
                            merged.push(previsto[i]);
                        }
                    }
                    if (realizado.length >0) {
                        for (var j = 0; j < realizado.length; j++) {
                            realizado[j].a_tipo = 2;
                            merged.push(realizado[j]);
                        }
                    }
                    Merging = false;
                }
            }
            this.populateEmpregados(merged);
        };

        Sobreaviso.prototype.populateEmpregados = function(argument) {
            argument =  _(argument).sortBy('no_funcionario');
            tbodySobreavisoEmpregado.empty();
            var html = "";
            var coords = [];
            for (var i in argument) {
                coords.push(argument[i].no_sigla_area);
                if (argument[i].a_tipo == 1) {
                    html = "<tr class='bleh'>";
                } else if (argument[i].a_tipo == 2) {
                    html = "<tr class='sobad'>";
                } else {
                    html = "<tr class='yahoo'>";
                }
                html += "<td>" + argument[i].no_sigla_area + "</td>";
                html += "<td>" + argument[i].no_funcionario + "</td>";
                html += "<td>" + argument[i].total + "</td>";
                html += "</tr>";
                tbodySobreavisoEmpregado.append(html);
            }
            this.fillSelect(coords);
            this.makeCoord(argument, _.uniq(coords.sort()));
        };

        Sobreaviso.prototype.EmpregadosMerged = function(argument) {
            tbodySobreavisoEmpregado.empty();
            var html = "";
            var coords = [];
            for (var i in argument) {
                coords.push(argument[i].no_sigla_area);
                if (argument[i].a_tipo == 1) {
                    html = "<tr class='bleh'>";
                } else if (argument[i].a_tipo == 2) {
                    html = "<tr class='sobad'>";
                } else if (argument[i].a_tipo == 3) {
                    html = "<tr class='yahoo'>";
                } else {
                    html = "<tr class='warning'>";
                }
                html += "<td>" + argument[i].no_sigla_area + "</td>";
                html += "<td>" + argument[i].no_funcionario + "</td>";
                html += "<td>" + argument[i].dt_sobreaviso + "</td>";
                if (argument[i].a_tipo == 1 ) {
                    html += "<td>" + this.dealWithDates(argument[i].dt_sobreaviso,argument[i].qt_minutos) + "</td>";
                    html += "<td>" + this.testSis(argument[i].sis) + "</td>";
                    html += "<td>" + this.formatNumber(argument[i].qt_minutos) + "</td>";
                    html += "<td>" + '0' + "</td>";
                } else if (argument[i].a_tipo == 2  || argument[i].a_tipo == 4) {
                    html += "<td>" + 'não informado' + "</td>";
                    html += "<td>" + 'não informado' + "</td>";
                    html += "<td>" + '0' + "</td>";
                    html += "<td>" + this.formatNumber(argument[i].realizado) + "</td>";
                } else {
                    html += "<td>" + this.dealWithDates(argument[i].dt_sobreaviso,argument[i].qt_minutos) + "</td>";
                    html += "<td>" + this.testSis(argument[i].sis) + "</td>";
                    html += "<td>" + this.formatNumber(argument[i].qt_minutos) + "</td>";
                    html += "<td>" + this.formatNumber(argument[i].realizado) + "</td>";
                }
                html += "</tr>";
                tbodySobreavisoEmpregado.append(html);
            }
            this.fillSelect(coords);
            this.genCoord(argument, _.uniq(coords.sort()));
        };

        Sobreaviso.prototype.makeCoord = function(empregados, coords) {
            var names = {};
            for (var j = 0; j < coords.length; j++) {
                names[coords[j]] = [0,0];
            }
            for (var i = 0; i < empregados.length; i++) {
                if (empregados[i].a_tipo === 1) {
                    names[empregados[i].no_sigla_area][0] += parseInt(empregados[i].total);
                } else if (empregados[i].a_tipo === 2) {
                    names[empregados[i].no_sigla_area][1] += parseInt(empregados[i].total);
                } else{
                    names[empregados[i].no_sigla_area][0] += parseInt(empregados[i].total);
                    names[empregados[i].no_sigla_area][1] += parseInt(empregados[i].total);
                }
            }
            this.PopulateGenCoord(names);
        };

        Sobreaviso.prototype.fillSelect = function(argument) {
            var sel = $('#coords');
            var childs = sel.children();

            //limpar o select
            for (var j = 0; j < childs.length; j++) {
                if(j >0){
                    $(childs[j]).remove();
                }
            }
            //repopular
            coords = _.uniq(argument.sort());
            for(var i = 0; i < coords.length; i++) {
                var opt = coords[i];
                var el = document.createElement("option");
                el.textContent = opt;
                el.value = opt;
                sel.append(el);
            }
        };

        Sobreaviso.prototype.toDate = function(argument) {
            var nd = argument.split('/');
            return nd[2] + '-' + nd[1] + '-' + nd[0];
        };

        Sobreaviso.prototype.orderObj = function(map) {
            var keys = _.sortBy(_.keys(map), function(a) { return a; });
            var newmap = {};
            _.each(keys, function(k) {
                newmap[k] = map[k];
            });
            return newmap;
        };

        Sobreaviso.prototype.defineMeta = function(argument) {
            metaSobreaviso.text(this.formatNumber(argument[0].meta));
            mesSobreaviso.text( this.formatMonth(argument[0].dt));
            Meta = argument[0].meta;
        };

        Sobreaviso.prototype.splitDate = function(argument) {
            formatData = {};
            tmp = argument.split("/");
            formatData.mes = tmp[0];
            formatData.ano = tmp[1];
            return(formatData);
        };

        Sobreaviso.prototype.testSis = function(argument) {
            if (argument === null) {
                argument = "não informado";
            }
            return argument;
        };

        Sobreaviso.prototype.dealWithDates = function(dt, mins) {
            var ndt = dt.replace(' ','/').replace(':','/').split('/');
            var dtFim = new Date(ndt[2],ndt[1],ndt[0],ndt[3],ndt[4]);
            dtFim.setMinutes ( dtFim.getMinutes() + mins );
            var dd = this.addZeros(dtFim.getDate());
            var mm = this.addZeros(dtFim.getMonth());
            var yy = this.addZeros(dtFim.getFullYear());
            var hh = this.addZeros(dtFim.getHours());
            var min= this.addZeros(dtFim.getMinutes());
            var concFim = dd +'/'+ mm +'/'+ yy +' '+ hh +':'+ min;
            return concFim;
        };

        Sobreaviso.prototype.addZeros = function(argument) {
            if(argument <10){
                argument = '0'+ argument;
            }
            return argument;
        };

        Sobreaviso.prototype.PopulateGenCoord = function(argument) {
            tbodySobreavisoCoord.empty();
            var html = "";
            for(var coord in argument){
                var qtPrevisto = argument[coord][0];
                var qtRealizado = argument[coord][1];
                html = "<tr>";
                html += "<td>" + coord + "</td>";
                if( parseInt(Meta) === 0 ) {
                    html += "<td>" + this.formatNumber(String(qtPrevisto)) + "</td>";
                    html += "<td>" + this.formatNumber(String(qtRealizado)) + "</td>";
                } else{
                    if ( parseInt(qtPrevisto) > parseInt(Meta) ) {
                        html += "<td class='sobad'>" + this.formatNumber(String(qtPrevisto)) + "</td>";
                    } else {
                        html += "<td class='yahoo'>" + this.formatNumber(String(qtPrevisto)) + "</td>";
                    }
                    if ( parseInt(qtRealizado) > parseInt(Meta) ) {
                        html += "<td class='sobad'>" + this.formatNumber(String(qtRealizado)) + "</td>";
                    } else {
                        html += "<td class='yahoo'>" + this.formatNumber(String(qtRealizado)) + "</td>";
                    }
                }
                html += "</tr>";
                tbodySobreavisoCoord.append(html);
            }
        };

        Sobreaviso.prototype.formatMonth = function(argument) {
            var monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
            "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
            var d = new Date(argument);
            return monthNames[d.getMonth()] + ' de '+d.getFullYear();
        };

        Sobreaviso.prototype.formatNumber = function(argument) {
            return argument.replace(/\d(?=(?:\d{3})+(?:\D|$))/g,"$&.");
        };

        Sobreaviso.prototype.loadMsg = function() {
            alertPage($("#loadmsg"), "Carregando dados do banco..." , 'info');
        };

        Sobreaviso.prototype.hideMsg = function() {
            $("#loadmsg").hide('slow');
        };

        Sobreaviso.prototype.emptyView = function() {
            tbodySobreavisoCoord.empty();
            tbodySobreavisoEmpregado.empty();
            mesSobreaviso.empty();
            metaSobreaviso.empty();
        };
    }
});

function hideTypeBleh (argument) {
    var visibles = $("#tbodySobreavisoEmpregado tr");
    var valu = $('#coords').val();
    for (var i = 0; i < visibles.length; i++) {
        var text = $(visibles[i]).children(0).first().text();
        var line = $(visibles[i]).children(0);
        if (parseInt(valu) === 0) {
            if(visibles[i].className == 'bleh'){
                line.show();
            } else{
                line.hide();
            }
        } else{
            if(text == valu && visibles[i].className == 'bleh'){
                line.show();
            } else {
                line.hide();
            }
        }
    }
}


function hideTypeSobad (argument) {
    var visibles = $("#tbodySobreavisoEmpregado tr");
    var valu = $('#coords').val();
    for (var i = 0; i < visibles.length; i++) {
        var text = $(visibles[i]).children(0).first().text();
        var line = $(visibles[i]).children(0);
        if (parseInt(valu) === 0) {
            if(visibles[i].className == 'sobad'){
                line.show();
            } else{
                line.hide();
            }
        } else{
            if(text == valu && visibles[i].className == 'sobad'){
                line.show();
            } else {
                line.hide();
            }
        }
    }
}


function hideTypeYahoo (argument) {
    var visibles = $("#tbodySobreavisoEmpregado tr");
    var valu = $('#coords').val();
    for (var i = 0; i < visibles.length; i++) {
        var text = $(visibles[i]).children(0).first().text();
        var line = $(visibles[i]).children(0);
        if (parseInt(valu) === 0) {
            if(visibles[i].className == 'yahoo'){
                line.show();
            } else{
                line.hide();
            }
        } else{
            if(text == valu && visibles[i].className == 'yahoo'){
                line.show();
            } else {
                line.hide();
            }
        }
    }
}

function clickPorCoordenacao(obj) {
    hideAll();
    $("#porCoordenacao").show("slow");
    $("#tabPorCoordenacao").addClass('active');
}

function clickPorEmpregado() {
    hideAll();
    $("#porEmpregado").show("slow");
    $("#tabPorEmpregado").addClass('active');
}

function hideAll() {
    $("#porCoordenacao").hide();
    $("#porEmpregado").hide();

    $("#tabPorCoordenacao").removeClass('active');
    $("#tabPorEmpregado").removeClass('active');
}
