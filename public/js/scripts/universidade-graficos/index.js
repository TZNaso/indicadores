$(document).ready(function () {

    var graphics = new Graphics();
    loadMsg();
    $.ajax({
        url: 'universidade-graficos/ajax-coordenacoes',
        async: true,
        type: 'POST',
        data: 'dt_referencia=' + $( "#dtReferencia option:selected" ).text(),
        dataType: 'json',
        success: function (retorno) {
            hideMsg();
            graphics.createChartsCoords(retorno);
        },
        statusCode: {
            500: function() {
                alert("Erro no servidor/banco de dados, favor recarregar a página");
                console.log('500 ');
            }
        }
    });

    $('#dtReferencia').change(function (e) {
        loadMsg();
        $.ajax({
            url: 'universidade-graficos/ajax-coordenacoes',
            async: true,
            type: 'POST',
            data: 'dt_referencia=' + $(this).val(),
            dataType: 'json',
            success: function (retorno) {
                hideMsg();
                graphics.resetViews();
                graphics.createChartsCoords(retorno);
            }
        });
    });

});


function Graphics () {

    // JQuery variables

    this.CoordFundamental = $("#GraphCoordFundamental");
    this.EquipeFundamental = $("#GraphEquipeFundamental");
    this.EmpregadoFundamental = $("#GraphEmpregadoFundamental");
    this.CoordLider = $("#GraphCoordLider");
    this.EquipeLider= $("#GraphEquipeLider");
    this.EmpregadoLider = $("#GraphEmpregadoLider");
    this.btnBackFundamental = $("#btnBackFundamental");
    this.btnBackLider = $("#btnBackLider");
    this.evoEmprFundamental = $("#GraphEmpregadoFundamentalTotal");
    this.evoEmprLider = $("#GraphEmpregadoLiderTotal");

    this.basicAxisX = {
        labelAngle: -45,
        labelFontColor: "rgb(0,75,141)",
        labelFontSize: 13,
        interval: 1,
    };

    this.basicAxisY = {
        labelFontSize: 15,
        titleFontColor: "rgb(0,75,141)",
        gridThickness: 0.5,
        minimum: 0,
        interval:10,
        maximum: 100
    };

    Graphics.prototype.createChartsCoords = function (argument) {
        this.barGraphFundamentalCoordenacao(argument.dadosFundamentalCoordenacao);
        this.barGraphLiderCoordenacao(argument.dadosLiderCoordenacao);
        this.pieFundamentalGeral(this.sumPorcentagensGeral(argument.dadosFundamentalGeral));
        this.pieLiderGeral(this.sumPorcentagensGeral(argument.dadosLiderGeral));
        this.Credits();
    };



//graficos


    Graphics.prototype.pieFundamentalGeral = function(arg) {
        $("#totalFuncionariosSimples").text(arg[0].totalEmpregados);
        var feito = parseFloat(arg[0].porcentagem);
        var falta = parseFloat(((arg[0].totalEmpregados*100) - feito).toFixed(2));
        var chart = new CanvasJS.Chart("GraphFundamentalGeral",
        {
            title:{
                text: "Fundamental",
                fontFamily: "arial black"
            },
                    animationEnabled: true,
            legend: {
                verticalAlign: "bottom",
                horizontalAlign: "center"
            },
            theme: "theme2",
            data: [
            {
                type: "pie",
                indexLabelFontFamily: "Garamond",
                indexLabelFontSize: 20,
                indexLabelFontWeight: "bold",
                startAngle:0,
                indexLabelFontColor: "MistyRose",
                indexLabelLineColor: "darkgrey",
                indexLabelPlacement: "inside",
                toolTipContent: "{name}",
                showInLegend: true,
                indexLabel: "#percent%",
                dataPoints: [
                    {  y: feito, name: "Total Trilhado", legendMarkerType: "triangle"},
                    {  y: falta, name: "Falta", legendMarkerType: "square"},
                ]
            }
            ]
        });
        chart.render();
    };

    Graphics.prototype.pieLiderGeral = function(arg) {
        $("#totalFuncionariosLideres").text(arg[0].totalEmpregados);
        var feito = parseFloat(arg[0].porcentagem);
        var falta = parseFloat(((arg[0].totalEmpregados*100) - feito).toFixed(2));
        var chart = new CanvasJS.Chart("GraphLiderGeral",
        {
            title:{
                text: "Líder",
                fontFamily: "arial black"
            },
                    animationEnabled: true,
            legend: {
                verticalAlign: "bottom",
                horizontalAlign: "center"
            },
            theme: "theme2",
            data: [
            {
                type: "pie",
                indexLabelFontFamily: "Garamond",
                indexLabelFontSize: 20,
                indexLabelFontWeight: "bold",
                startAngle:0,
                indexLabelFontColor: "MistyRose",
                indexLabelLineColor: "darkgrey",
                indexLabelPlacement: "inside",
                toolTipContent: "{name}",
                showInLegend: true,
                indexLabel: "#percent%",
                dataPoints: [
                    {  y: feito, name: "Total Trilhado", legendMarkerType: "triangle"},
                    {  y: falta, name: "Falta", legendMarkerType: "square"},
                ]
            }
            ]
        });
        chart.render();
    };

    Graphics.prototype.barGraphFundamentalCoordenacao = function (arg) {
        var dPoints = [];
        var self = this;
        for (var i = 0; i < arg.length; i++) {
            var labels = arg[i].coordenacao.split("-");
            dPoints.push({
                label:labels[0].trim(),
                y:parseFloat( arg[i].total_trilhado),
                indexLabel:(arg[i].total_trilhado + "% "),
                toolTipContent:labels[labels.length - 1]
           });
        }

        var chart = new CanvasJS.Chart("GraphCoordFundamental", {
            title:{text: "Trilha Fundamental por Coordenações"},
            animationEnabled: true,
            axisX: this.basicAxisX,
            axisY: this.basicAxisY,
            data:[{
                type: "column",
                labelOrientation: "horizontal",
                indexLabelFontSize: 13,
                indexLabelFontFamily:"Lucida Console" ,
                indexLabelOrientation: "vertical",
                indexLabelPlacement: "inside",
                indexLabelFontColor: "rgb(245, 150, 0)",
                indexLabelFontWeight:"bold",
                color: "rgba(29,54,108,0.8)",
                dataPoints: dPoints,
                click:  function(e){
                    self.clickBarFundamentalCoord([{label:e.dataPoint.label}]);
                }
            }]
        });
        chart.render();
    };

    Graphics.prototype.barGraphFundamentalEquipe = function (coord, arg) {
        if (arg && coord != 'CEDESBR') {
            var dPoints = [];
            var self = this;
            for (var i = 0; i < arg.length; i++) {
                var labels = arg[i].area.split("-");
                dPoints.push({
                    label:labels[0].trim(),
                    y:parseFloat( arg[i].total_trilhado),
                    indexLabel:(arg[i].total_trilhado + "% "),
                    toolTipContent:labels[labels.length - 1]
               });
            }

            var chart = new CanvasJS.Chart("GraphEquipeFundamental", {
                title:{text: "Trilha Fundamental por Equipes"},
                animationEnabled: true,
                axisX: this.basicAxisX,
                axisY: this.basicAxisY,
                data: [{
                    type: "column",
                    labelOrientation: "horizontal",
                    indexLabelFontSize: 13,
                    indexLabelFontFamily:"Lucida Console" ,
                    indexLabelOrientation: "vertical",
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "rgb(245, 150, 0)",
                    indexLabelFontWeight:"bold",
                    color: "rgba(29,54,108,0.8)",
                    dataPoints: dPoints,
                    click: function(e){
                        self.clickBarFundamentalEquipe([{label:e.dataPoint.label}],true);
                    }
                }]
            });
            chart.render();
        } else {
            //simular click em equipe
            this.clickBarFundamentalEquipe([{label:coord}],false);
        }
    };

    Graphics.prototype.barGraphFundamentalEmpregados = function (equipe, arg) {
        if (arg) {
            var dPoints = [];
            var self = this;
            for (var i = 0; i < arg.length; i++) {
                dPoints.push({
                    label:this.formatName(arg[i].nome.trim()),
                    y:parseFloat(arg[i].porcentagem),
                    toolTipContent: arg[i].matricula,
                    indexLabel:(arg[i].porcentagem + "% ")
               });
            }

            var chart = new CanvasJS.Chart("GraphEmpregadoFundamental", {
                title:{text: "Trilha Fundamental por Empregados"},
                animationEnabled: true,
                axisX: this.basicAxisX,
                axisY: this.basicAxisY,
                data:  [{
                    type: "column",
                    labelOrientation: "horizontal",
                    indexLabelFontSize: 13,
                    indexLabelFontFamily:"Lucida Console" ,
                    indexLabelOrientation: "vertical",
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "rgb(245, 150, 0)",
                    indexLabelFontWeight:"bold",
                    color: "rgba(29,54,108,0.8)",
                    dataPoints: dPoints,
                    click: function(e){
                        self.clickBarFundamentalEmpregado(e.dataPoint.toolTipContent);
                    }
                }]
            });
            chart.render();
        }
    };

    Graphics.prototype.lineGraphFundamentalEmpregado = function(arg) {
        dPoints = [];
        var self = this;
        var empregado = {text:arg[0].no_funcionario};
        let lastDate = new Date(arg[0].dt_referencia)
        for (var i = 0; i < arg.length; i++) {
        	let currentDate = new Date(arg[i].dt_referencia)
        	let timeDiff = 51000
        	
        	if (i > 0 && i !== arg.length) {
        		timeDiff = Math.abs(currentDate.getTime() - lastDate.getTime())/100000;
        	}

            dPoints.push({
                x:currentDate,
                y:parseFloat(arg[i].passos_porcentagem),
                indexLabel: timeDiff > 50000 ? (arg[i].passos_porcentagem + "% "): '',
                toolTipContent:arg[i].dt_referencia
            });
            
        	if (timeDiff > 50000 || i === arg.length) {
        		lastDate = new Date(arg[i].dt_referencia)
        	}
        }

        var chart = new CanvasJS.Chart("GraphEmpregadoFundamentalTotal", {
            title:empregado,
            animationEnabled: true,
            axisX: this.basicAxisX,
            axisY: this.basicAxisY,
            data: [{
                type: "area",
                labelOrientation: "horizontal",
                indexLabelFontSize: 13,
                indexLabelFontFamily:"Lucida Console" ,
                indexLabelOrientation: "horizontal",
                indexLabelPlacement: "inside",
                indexLabelFontColor: "rgb(245, 150, 0)",
                indexLabelFontWeight:"bold",
                color: "rgba(29,54,108,0.8)",
                dataPoints: dPoints,
                click: function(e){}
            }]
        });
        chart.render();
    };

    Graphics.prototype.barGraphLiderCoordenacao = function (arg) {
        var dPoints = [];
        var self = this;
        for (var i = 0; i < arg.length; i++) {
            var labels = arg[i].coordenacao.split("-");
            dPoints.push({
                label:labels[0].trim(),
                y:parseFloat( arg[i].total_trilhado),
                indexLabel:(arg[i].total_trilhado + "% "),
                toolTipContent:labels[labels.length - 1]
           });
        }

        var chart = new CanvasJS.Chart("GraphCoordLider", {
            title:{text: "Trilha Fundamental Líder por Coordenações"},
            animationEnabled: true,
            axisX: this.basicAxisX,
            axisY: this.basicAxisY,
            data: [{
                labelOrientation: "horizontal",
                indexLabelFontSize: 13,
                indexLabelFontFamily:"Lucida Console" ,
                indexLabelOrientation: "vertical",
                indexLabelPlacement: "inside",
                indexLabelFontColor: "rgb(0,75,141)",
                indexLabelFontWeight:"bold",
                type: "column",
                color: "rgba(245, 150, 29,0.8)",
                dataPoints: dPoints ,
                click: function(e){
                    self.clickBarLiderCoord([{label:e.dataPoint.label}]);
                }
            }]
        });
        chart.render();
    };

    Graphics.prototype.barGraphLiderEquipe = function (coord, arg) {
        if (arg && coord != 'CEDESBR') {
            var dPoints = [];
            var self = this;
            for (var i = 0; i < arg.length; i++) {
                var labels = arg[i].area.split("-");
                dPoints.push({
                    label:labels[0].trim(),
                    y:parseFloat( arg[i].total_trilhado),
                    indexLabel:(arg[i].total_trilhado + "% "),
                    toolTipContent:labels[labels.length - 1]
               });
            }

            var chart = new CanvasJS.Chart("GraphEquipeLider", {
                title:{text: "Trilha Fundamental por Equipes"},
                animationEnabled: true,
                axisX: this.basicAxisX,
                axisY: this.basicAxisY,
                data: [{
                    labelOrientation: "horizontal",
                    indexLabelFontSize: 13,
                    indexLabelFontFamily:"Lucida Console" ,
                    indexLabelOrientation: "vertical",
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "rgb(0,75,141)",
                    indexLabelFontWeight:"bold",
                    type: "column",
                    color: "rgba(245, 150, 29,0.8)",
                    dataPoints: dPoints ,
                    click: function(e){
                        self.clickBarLiderEquipe([{label:e.dataPoint.label}],true);
                    }
                }]
            });
            chart.render();
        } else {
            //simular click em equipe
            this.clickBarLiderEquipe([{label:coord}],false);
        }
    };

    Graphics.prototype.barGraphLiderEmpregados = function (equipe, arg) {
      if (arg) {
            var dPoints = [];
            var self = this;
            for (var i = 0; i < arg.length; i++) {
                dPoints.push({
                    label:this.formatName(arg[i].nome.trim()),
                    y:parseFloat(arg[i].porcentagem),
                    toolTipContent: arg[i].matricula,
                    indexLabel:(arg[i].porcentagem + "% ")
               });
            }

            var chart = new CanvasJS.Chart("GraphEmpregadoLider", {
                title:{text: "Trilha Fundamental Líder por Empregado"},
                animationEnabled: true,
                axisX: this.basicAxisX,
                axisY: this.basicAxisY,
                data: [{
                    labelOrientation: "horizontal",
                    indexLabelFontSize: 13,
                    indexLabelFontFamily:"Lucida Console" ,
                    indexLabelOrientation: "vertical",
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "rgb(0,75,141)",
                    indexLabelFontWeight:"bold",
                    type: "column",
                    color: "rgba(245, 150, 29,0.8)",
                    dataPoints: dPoints ,
                    click: function(e){
                        self.clickBarLiderEmpregado(e.dataPoint.toolTipContent);
                    }
                }]
            });
            chart.render();
        }
    };

    Graphics.prototype.lineGraphLiderEmpregado = function(arg) {
        dPoints = [];
        var self = this;
        var empregado = {text:arg[0].no_funcionario};
        let lastDate = new Date(arg[0].dt_referencia)
        for (var i = 0; i < arg.length; i++) {
        	let currentDate = new Date(arg[i].dt_referencia)
        	let timeDiff = 51000
        	
        	if (i > 0 && i !== arg.length) {
        		timeDiff = Math.abs(currentDate.getTime() - lastDate.getTime())/100000;
        	}

            dPoints.push({
                x:new Date(arg[i].dt_referencia),
                y:parseFloat( arg[i].passos_porcentagem),
                indexLabel: timeDiff > 50000 ? (arg[i].passos_porcentagem + "% "): '',
                toolTipContent:arg[i].dt_referencia
            });


        	if (timeDiff > 50000 || i === arg.length) {
	            lastDate = new Date(arg[i].dt_referencia)
        	}
        }
        
        

        var chart = new CanvasJS.Chart("GraphEmpregadoLiderTotal", {
            title:empregado,
            animationEnabled: true,
            axisX: this.basicAxisX,
            axisY: this.basicAxisY,
            data: [{
                type: "area",
                labelOrientation: "horizontal",
                indexLabelFontSize: 13,
                indexLabelFontFamily:"Lucida Console" ,
                indexLabelOrientation: "horizontal",
                indexLabelPlacement: "inside",
                indexLabelFontColor:"rgb(0,75,141)",
                indexLabelFontWeight:"bold",
                color: "rgba(245, 150, 29,0.8)",
                dataPoints: dPoints,
                click: function(e){}
            }]
        });
        chart.render();
    };



//Clicks



    Graphics.prototype.clickBarFundamentalCoord = function (argument) {
        var self = this;
        this.showEquipeFundamental();
        this.getFundamentalEquipe(argument);
        this.btnBackFundamental.show();
        this.btnBackFundamental.unbind( "click" );
        this.btnBackFundamental.click(function (evt) {
            self.showCoordFundamental();
        });
    };

    Graphics.prototype.clickBarFundamentalEquipe = function (argument, bool) {
        var self = this;
        this.showEmpregadoFundamental();
        this.getFundamentalEmpregados(argument);
        this.btnBackFundamental.unbind( "click" );
        this.btnBackFundamental.click(function (evt) {
            if (bool) {
                self.showEquipeFundamental();
            } else {
                self.showCoordFundamental();
            }
        });
    };

    Graphics.prototype.clickBarFundamentalEmpregado = function(argument) {
        var self = this;
        this.showEmpregadoFundamentalTotal();
        this.getFundamentalEmpregadoTotal(argument);
        this.btnBackFundamental.unbind( "click" );
        this.btnBackFundamental.click(function (evt) {
            self.showEmpregadoFundamental();
        });
    };

    Graphics.prototype.clickBarLiderCoord = function (argument) {
        var self = this;
        this.showEquipeLider();
        this.getLiderEquipe(argument);
        this.btnBackLider.show();
        this.btnBackLider.unbind("click");
        this.btnBackLider.click(function (evt) {
            self.showCoordLider();
        });
    };

    Graphics.prototype.clickBarLiderEquipe = function (argument, bool) {
        var self = this;
        this.showEmpregadoLider();
        this.getLiderEmpregados(argument);
        this.btnBackLider.unbind( "click" );
        this.btnBackLider.click(function (evt) {
            if (bool) {
                self.showEquipeLider();
            } else{
                self.showCoordLider();
            }
        });
    };

    Graphics.prototype.clickBarLiderEmpregado = function (argument, bool) {
        var self = this;
        this.showEmpregadoLiderTotal();
        this.getLiderEmpregadoTotal(argument);
        this.btnBackLider.unbind( "click" );
        this.btnBackLider.click(function (evt) {
            self.showEmpregadoLider();
        });
    };



//show



    Graphics.prototype.showCoordFundamental = function() {
        this.hideAllFundamental();
        this.CoordFundamental.show();
        this.btnBackFundamental.unbind( "click" );
        this.btnBackFundamental.hide();
    };

    Graphics.prototype.showEquipeFundamental = function() {
        var self = this;
        this.hideAllFundamental();
        this.EquipeFundamental.show();
        this.btnBackFundamental.unbind("click");
        this.btnBackFundamental.click(function (evt) {
            self.showCoordFundamental();
        });
    };

    Graphics.prototype.showEmpregadoFundamental = function() {
        var self = this;
        this.hideAllFundamental();
        this.EmpregadoFundamental.show();
        this.btnBackFundamental.unbind("click");
        this.btnBackFundamental.click(function (evt) {
            self.showEquipeFundamental();
        });
    };

    Graphics.prototype.showEmpregadoFundamentalTotal = function() {
        var self = this;
        this.hideAllFundamental();
        this.evoEmprFundamental.show();
        this.btnBackFundamental.unbind("click");
        this.btnBackFundamental.click(function (evt) {
            self.showEmpregadoFundamental();
        });
    };

    Graphics.prototype.showCoordLider = function() {
        this.hideAllLider();
        this.CoordLider.show();
        this.btnBackLider.unbind( "click" );
        this.btnBackLider.hide();
    };

    Graphics.prototype.showEquipeLider = function() {
        var self = this;
        this.hideAllLider();
        this.EquipeLider.show();
        this.btnBackLider.unbind("click");
        this.btnBackLider.click(function (evt) {
            self.showCoordLider();
        });
    };

    Graphics.prototype.showEmpregadoLider = function() {
        var self = this;
        this.hideAllLider();
        this.EmpregadoLider.show();
        this.btnBackLider.unbind("click");
        this.btnBackLider.click(function (evt) {
            self.showEquipeLider();
        });
    };

    Graphics.prototype.showEmpregadoLiderTotal = function() {
        var self = this;
        this.hideAllLider();
        this.evoEmprLider.show();
        this.btnBackLider.unbind("click");
        this.btnBackLider.click(function (evt) {
            self.showEmpregadoLider();
        });
    };


//hide


    Graphics.prototype.hideAllFundamental = function() {
        this.CoordFundamental.hide();
        this.EquipeFundamental.hide();
        this.EmpregadoFundamental.hide();
        this.evoEmprFundamental.hide();
    };

    Graphics.prototype.hideAllLider = function() {
        this.CoordLider.hide();
        this.EquipeLider.hide();
        this.EmpregadoLider.hide();
        this.evoEmprLider.hide();
    };


//ajax


    Graphics.prototype.getFundamentalEquipe = function (argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-equipes-fundamental',
            async: true,
            type: 'POST',
            data: { 'dt_referencia' : $( "#dtReferencia option:selected" ).text(), 'coordenacao' : argument[0].label },
            dataType: 'json',
            success: function (retorno) {
                var dados = retorno.dadosEquipe;
                if(retorno.dadosEmpregadosCoord && dados){
                    dados.push(self.addEmpregadosCoord(argument[0].label , retorno.dadosEmpregadosCoord));
                }
                self.barGraphFundamentalEquipe( argument[0].label , dados);
            }
        });
    };

    Graphics.prototype.getFundamentalEmpregados = function (argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-empregados-fundamental',
            async: true,
            type: 'POST',
            data: { 'dt_referencia' : $( "#dtReferencia option:selected" ).text(), 'equipe' : argument[0].label },
            dataType: 'json',
            success: function (retorno) {
                self.barGraphFundamentalEmpregados( argument[0].label , retorno.dadosEmpregados);
            }
        });
    };

    Graphics.prototype.getFundamentalEmpregadoTotal = function(argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-empregados-fundamental-total',
            async: true,
            type: 'POST',
            data: { 'matricula' : argument },
            dataType: 'json',
            success: function (retorno) {
                self.lineGraphFundamentalEmpregado(retorno.dadosEmpregado);
            }
        });
    };

    Graphics.prototype.getLiderEmpregados = function (argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-empregados-Lider',
            async: true,
            type: 'POST',
            data: { 'dt_referencia' : $( "#dtReferencia option:selected" ).text(), 'equipe' : argument[0].label },
            dataType: 'json',
            success: function (retorno) {
                self.barGraphLiderEmpregados( argument[0].label , retorno.dadosEmpregados);
            }
        });
    };

    Graphics.prototype.getLiderEquipe = function (argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-equipes-lider',
            async: true,
            type: 'POST',
            data: { 'dt_referencia' : $( "#dtReferencia option:selected" ).text(), 'coordenacao' : argument[0].label },
            dataType: 'json',
            success: function (retorno) {
                var dados = retorno.dadosEquipe;
                if(retorno.dadosEmpregadosCoord && dados){
                    dados.push(self.addEmpregadosCoord(argument[0].label , retorno.dadosEmpregadosCoord));
                }
                self.barGraphLiderEquipe( argument[0].label ,dados);
            }
        });
    };

    Graphics.prototype.getLiderEmpregadoTotal = function(argument) {
        var self = this;
        $.ajax({
            url: 'universidade-graficos/ajax-empregados-lider-total',
            async: true,
            type: 'POST',
            data: { 'matricula' : argument },
            dataType: 'json',
            success: function (retorno) {
                self.lineGraphLiderEmpregado(retorno.dadosEmpregado);
            }
        });
    };



//misc

    Graphics.prototype.sumPorcentagensGeral = function(argument) {
        var dadosGeral = [];
        var dados = {'porcentagem':0,'totalEmpregados':0};
        for (var i = 0; i < argument.length; i++) {
            dados.porcentagem += parseFloat(argument[i].porcentagem_soma);
            dados.totalEmpregados += parseInt(argument[i].total_funcionarios);
        }
        dados.porcentagem = parseFloat(dados.porcentagem.toFixed(2));
        dadosGeral.push(dados);
        return dadosGeral;
    };

    Graphics.prototype.resetViews = function() {
        this.showCoordLider();
        this.showCoordFundamental();
    };

    Graphics.prototype.addEmpregadosCoord = function  (label, argument) {
        var soma = 0;
        for (var i = 0; i < argument.length; i++) {
            soma += parseFloat( argument[i].porcentagem);
        }
        var retorno = {area: label ,total_trilhado:((soma*100)/ (argument.length*100)).toFixed(2)};
        return retorno;
    };

    Graphics.prototype.formatName = function  (argument) {
        var names = argument.split(" ");
        var name;
        for (var i = 0; i < names.length; i++) {
            if( names[i].toLowerCase()  == "de" ||
                names[i].toLowerCase()  == "da" ||
                names[i].toLowerCase()  == "do" ||
                names[i].toLowerCase()  == "das"||
                names[i].toLowerCase()  == "dos"||
                names[i].toLowerCase()  == "e" ){
                names.splice(i, 1);
            }
        }
        for ( var j = 0; j <= names.length; j++ ) {
            if( j === 0 ){
                name = names[j] + " ";
            } else if( j == names.length ){
                name += names[j-1];
            } else{
                if( j != names.length -1 ) {
                    name += ( names[j].split("").shift().toUpperCase() + ". " );
                }
            }
        }
        return name;
    };

    Graphics.prototype.Credits = function() {
        $(".canvasjs-chart-credit").each(
            function( index ) {
                if (index !== 0) {
                    aaa = $( this );
                    $( this ).css({display:"block"});
                    $( this ).removeAttr("href");
                    $( this ).text("fonte: Universidade Caixa/SIPTI");
                }
            }
        );
    };
}
