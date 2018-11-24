function GraphicHE() {

    this.containerTempo = $("#containerTempo");
    this.containerValor = $("#containerValor");
    this.containerGeral = $("#containerGraphGeral");
    this.GraphTipo = $("#containerMes");

    GraphicHE.prototype.createCharts = function(arg) {
        this.containerTempo.empty();
        this.acumuladoMeses(arg.totalGeral);
        this.createSimple(arg);
        console.log(arg);
    };


    GraphicHE.prototype.createSimple = function(arg) {

        this.barValorMesCoords(
            arg.dt_selecionada,
            this.formatDadosValor(arg.dadosEmpregados)
        );
        this.barTempoMesCoords(
            arg.dt_selecionada,
            this.formatDadosTempo(arg.dadosEmpregados)
        );
        this.StackCoordValor(
            arg.dt_selecionada,
            this.genCoordsValor(arg.dadosEmpregados, 'no_sigla_coord')
        );
    };

    GraphicHE.prototype.acumuladoMeses = function(totalGeral) {
        console.log(totalGeral);
        this.containerGeral.append(
            $("<div class='basicGraph row'  id='GeralCedes' style='height: 500px'></div>")
        );
        var dadosValor = [];
        var dadosMeta = [];
        for (var mes in totalGeral) {
            var tempData = totalGeral[mes][0].dt.split("-");
            this.formataMoeda(totalGeral[mes][0].valor);
            dadosValor.push({
                y: parseFloat(totalGeral[mes][0].valor),
                x: new Date(tempData[0], (tempData[1] - 1)),
                indexLabel: this.formataMoeda(totalGeral[mes][0].valor)
            });
            dadosMeta.push({
                y: parseFloat(totalGeral[mes][0].meta),
                x: new Date(tempData[0], (tempData[1] - 1))
            });
        }
        var chartGeralCedes = new CanvasJS.Chart("GeralCedes", {
            title: {
                text: "Evolução Mês a Mês"
            },
            animationEnabled: true,
            axisX: {
                valueFormatString: "MMM",
                interval: 1,
                intervalType: "month"
            },
            axisY: {
                maximum: 150000
            },
            data: [{
                type: "column",
                showInLegend: true,
                legendText: "Gasto (R$)",
                labelOrientation: "horizontal",
                indexLabelFontSize: 13,
                indexLabelFontFamily: "Lucida Console",
                indexLabelOrientation: "horizontal",
                indexLabelPlacement: "outside",
                indexLabelFontColor: "rgb(0, 0, 0)",
                indexLabelFontWeight: "bold",
                color: "rgba(29,54,108,0.8)",
                dataPoints: dadosValor
            }, {
                type: "line",
                showInLegend: true,
                legendText: "Meta (R$)",
                dataPoints: dadosMeta,
                color: "rgba(245, 150, 29, 0.8)"
            }]
        });
        chartGeralCedes.render();
    };


    GraphicHE.prototype.formatDadosTempo = function(demp) {
        var length = demp.length;
        var coords = [];
        for (var j = 0; j < length; j++) {
            if (!coords[demp[j].no_sigla_coord]) {
                coords[demp[j].no_sigla_coord] = 0;
            }
            coords[demp[j].no_sigla_coord] += parseFloat(demp[j].nu_total_he);
        }

        var dtpoint = [];
        for (var key in coords) {
            var nodo = {
                indexLabel: this.formataHoras(coords[key]) + "hrs ",
                label: key,
                y: coords[key]
            };
            dtpoint.push(nodo);
        }
        return (_.sortBy(dtpoint, 'label'));
    };

    GraphicHE.prototype.barTempoMesCoords = function(mes, args) {
        this.containerTempo.empty();
        this.containerTempo.append(
            $("<div id='totaltempomes' style='height: 500px'></div>")
        );
        var chart = new CanvasJS.Chart("totaltempomes", {
            title: {
                text: "Horas gastas por coordenação em " + mes,
                fontSize: 20
            },
            axisX: {
                labelFontSize: 12,
                interval: 1
            },
            axisY: {
                labelFontSize: 12
            },
            toolTip: {
                content: '{label}'
            },
            animationEnabled: true,
            data: [{
                type: "bar",
                indexLabelPlacement: "outside",
                indexLabelFontSize: 15,
                dataPoints: args,
            }],
        });
        chart.render();
    };


    GraphicHE.prototype.formatDadosValor = function(demp) {
        var length = demp.length;
        var coords = [];
        for (var j = 0; j < length; j++) {
            if (!coords[demp[j].no_sigla_coord]) {
                coords[demp[j].no_sigla_coord] = 0;
            }
            coords[demp[j].no_sigla_coord] += parseFloat(demp[j].nu_valor_total_he);
        }
        var dtpoint = [];
        for (var key in coords) {
            var nodo = {
                label: key,
                y: parseFloat(coords[key].toFixed(2)),
                legendText: key + " - R$ " + parseFloat(coords[key].toFixed(2))
            };
            dtpoint.push(nodo);

        }
        return (_.sortBy(dtpoint, 'label'));
    };

    GraphicHE.prototype.barValorMesCoords = function(mes, args) {
        this.containerValor.empty();
        this.containerValor.append(
            $("<div id='totalvalormes' style='height: 500px'></div>")
        );
        var chart = new CanvasJS.Chart("totalvalormes", {
            title: {
                text: "Valores gastos por coordenação em " + mes,
                fontSize: 20
            },
            axisX: {
                labelFontSize: 12,
                interval: 1
            },
            axisY: {
                labelFontSize: 12,
                // interval: 1
            },
            legend: {
                verticalAlign: "bottom",
                fontSize: 12
            },
            animationEnabled: true,
            data: [{
                type: "bar",
                indexLabel: "R$ {y}",
                indexLabelPlacement: "outside",
                indexLabelFontSize: 15,
                dataPoints: args,
            }],
        });
        chart.render();
    };


    GraphicHE.prototype.genCoordsValor = function(Things, field) {
        var names = {};
        for (var i = 0; i < Things.length; i++) {
            var team = Things[i][field];
            names[team] = [];
        }
        for (i = 0; i < Things.length; i++) {
            var time = Things[i][field];
            names = this.TestVazio(names, time);
            names[time].nome = time;
            names[time].nu_he_comp_284 += parseFloat(this.nullToZero(Things[i].nu_valor_he_comp_284));
            names[time].nu_he_pg_285 += parseFloat(this.nullToZero(Things[i].nu_valor_he_pg_285));
            names[time].nu_he_pg_296 += parseFloat(this.nullToZero(Things[i].nu_valor_he_pg_296));
            names[time].nu_he_pg_302 += parseFloat(this.nullToZero(Things[i].nu_valor_he_pg_302));
            names[time].nu_he_pg_demais_proj += parseFloat(this.nullToZero(Things[i].nu_valor_he_pg_demais_proj));
        }
        names = _.sortBy(names, 'nome');
        return names;
    };

    GraphicHE.prototype.nullToZero = function(arg) {
        if (arg === null) {
            arg = parseFloat(0.0);
        }
        return arg;
    };

    GraphicHE.prototype.TestVazio = function(names, area) {
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
        return names;
    };

    GraphicHE.prototype.StackCoordValor = function(dt, arg) {
        this.GraphTipo.empty();
        console.log(arg)
        var dtpoints = this.mergeCoordsTipo(arg);
        this.GraphTipo.append($("<div id='horasPorTipo' style='height: 500px'></div>"));
        var chart = new CanvasJS.Chart("horasPorTipo", {
            title: {
                text: "Valores Detalhados por Coordenações " + dt,
                fontSize: 17,
            },
            axisX: {
                title: "Coordenações",
                labelAngle: -45,
                labelFontColor: "rgb(0,75,141)",
                labelFontSize: 13,
                interval: 1,
                titleFontSize: 20
            },
            axisY: {
                title: "Valores(R$)",
                labelFontSize: 15,
                gridThickness: 0.5,
                titleFontSize: 20
            },
            legend: {
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 15
            },
            animationEnabled: true,
            data: [{
                type: "stackedColumn",
                legendText: "284",
                showInLegend: "true",
                toolTipContent: "{legendText} : {y} ",
                dataPoints: dtpoints.nu_he_comp_284,
            }, {
                type: "stackedColumn",
                legendText: "285",
                showInLegend: "true",
                toolTipContent: "{legendText} : {y} ",
                dataPoints: dtpoints.nu_he_pg_285,
            }, {
                type: "stackedColumn",
                legendText: "296",
                showInLegend: "true",
                toolTipContent: "{legendText} : {y} ",
                dataPoints: dtpoints.nu_he_pg_296,
            }, {
                type: "stackedColumn",
                legendText: "302",
                showInLegend: "true",
                toolTipContent: "{legendText} : {y} ",
                dataPoints: dtpoints.nu_he_pg_302,
            }, {
                type: "stackedColumn",
                legendText: "Demais projetos",
                showInLegend: "true",
                toolTipContent: "{legendText} : {y} ",
                indexLabel: "#total",
                indexLabelFontSize: 13,
                indexLabelPlacement: "outside",
                indexLabelFontFamily: "Lucida Console",
                indexLabelOrientation: "horizontal",
                indexLabelFontColor: "rgb(0, 0, 0)",
                indexLabelFontWeight: "bold",
                dataPoints: dtpoints.nu_he_pg_demais_proj,
            }]
        });
        chart.render();
    };

    GraphicHE.prototype.mergeCoordsTipo = function(arg) {
        var length = arg.length;
        var dtpoint = {};
        var nu_he_comp_284 = [];
        var nu_he_pg_285 = [];
        var nu_he_pg_296 = [];
        var nu_he_pg_302 = [];
        var nu_he_pg_demais_proj = [];

        for (var i = 0; i < length; i++) {
            nu_he_comp_284.push({
                label: arg[i].nome,
                y: arg[i].nu_he_comp_284,
            });
            nu_he_pg_285.push({
                label: arg[i].nome,
                y: arg[i].nu_he_pg_285
            });
            nu_he_pg_296.push({
                label: arg[i].nome,
                y: arg[i].nu_he_pg_296
            });
            nu_he_pg_302.push({
                label: arg[i].nome,
                y: arg[i].nu_he_pg_302
            });
            nu_he_pg_demais_proj.push({
                label: arg[i].nome,
                y: arg[i].nu_he_pg_demais_proj
            });
        }
        dtpoint.nu_he_comp_284 = nu_he_comp_284;
        dtpoint.nu_he_pg_285 = nu_he_pg_285;
        dtpoint.nu_he_pg_296 = nu_he_pg_296;
        dtpoint.nu_he_pg_302 = nu_he_pg_302;
        dtpoint.nu_he_pg_demais_proj = nu_he_pg_demais_proj;
        return dtpoint;
    };

    GraphicHE.prototype.formataHoras = function(qtMinutos) {
        var minutos = qtMinutos % 60;
        if (minutos < 10) {
            minutos = "0" + String(minutos);
        }
        var horasAux = (qtMinutos / 60).toString().split(".");
        var horas = horasAux[0];
        if (horas.length == 1) {
            horas = "0" + horas;
        }
        return horas + ":" + minutos;
    };

    GraphicHE.prototype.formataMoeda = function(arg) {
        arg = parseFloat(arg).toFixed(2)
            .replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1.");
        return arg;
    };
}
