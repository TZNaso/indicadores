$(document).ready(function() {
	loadMsg();
	var gate = new mainGateway();
	// ajax de criação dos graficos
	$.ajax({
		url : 'ocorrencias-sipon/ajax-atualiza-dados',
		async : true,
		type : 'POST',
		data : 'dt_referencia=' + $("#dtReferencia option:selected").text(),
		dataType : 'json',
		success : function(retorno) {
			hideMsg();
			gate.proxy(retorno);
		}
	});

	geraTooltips();

	$("#ocorrenciasSipon").addClass("active");
	$("#horasExtras").removeClass("active");

	// Ajax da mudança da seleção do mês

	$('#dtReferencia').change(function(e) {
		loadMsg();
		var tbodyCoordenacao = $("#tbodyCoordenacao");
		var tbodyEquipe = $("#tbodyEquipe");
		var tbodyEmpregado = $("#tbodyEmpregado");

		$.ajax({
			url : 'ocorrencias-sipon/ajax-atualiza-dados',
			async : true,
			type : 'POST',
			data : 'dt_referencia=' + $(this).val(),
			dataType : 'json',
			success : function(retorno) {
				hideMsg();
				gate.proxy(retorno);

				tbodyCoordenacao.hide();
				tbodyEquipe.hide();
				tbodyEmpregado.hide();

				tbodyCoordenacao.show('slow');
				tbodyEquipe.show('slow');
				tbodyEmpregado.show('slow');
			}
		});
	});

	$('#area').change(function(e) {
		$("#tbodyEmpregado tr").each(function(index) {
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

	$('#selecionaTodos').click(function(e) {
		$("#exportacao input[type='checkbox']").attr('checked', true);
	});

	$("#btnExportar").click(function(e) {
		$("#formExportacao").submit();
	});

	$("#btnExportarAnual").click(function(e) {
		$("#formExportacaoAnual").submit();
	});
});

function mainGateway(argument) {
	var graphicSIPON = new GraphicSIPON();
	var repopSIPON = new RepopSIPON();

	mainGateway.prototype.proxy = function(argument) {
		argument.equipe = this.generateGeneric(argument.dadosEmpregados,
				'equipe');
		argument.coord = this
				.generateGeneric(argument.dadosEmpregados, 'coord');
		argument.dadosCoordenacao = argument.coord;
		graphicSIPON.createCharts(argument);
		repopSIPON.populateAll(argument);
	};

	mainGateway.prototype.generateGeneric = function(Things, field) {
		var names = {};
		for (var i = 0; i < Things.length; i++) {
			var team = Things[i][field];
			names[team] = [];
		}
		for (i = 0; i < Things.length; i++) {
			var time = Things[i][field];
			names = this.TestVazio(names, time);
			names[time].nome = time;
			names[time].qt_53 += parseInt(Things[i].qt_53);
			names[time].qt_56 += parseInt(Things[i].qt_56);
			names[time].qt_57 += parseInt(Things[i].qt_57);
			names[time].qt_58 += parseInt(Things[i].qt_58);
			names[time].qt_70 += parseInt(Things[i].qt_70);
			names[time].qt_195 += parseInt(Things[i].qt_195);
			names[time].qt_19 += parseInt(Things[i].qt_19);
			names[time].qt_20 += parseInt(Things[i].qt_20);
			names[time].qt_bloqueio += parseInt(Things[i].qt_bloqueio);
			names[time].qt_total_ocorrencias += parseInt(Things[i].qt_total_ocorrencias);
			names[time].qt_total_pontos_utilizados += parseInt(Things[i].qt_total_pontos_utilizados);
			names[time].qt_limite += parseInt(Things[i].qt_limite);
		}
		names = _.sortBy(names, 'nome');
		return names;
	};

	mainGateway.prototype.TestVazio = function(names, area) {
		if (_.isUndefined(names[area].qt_53)) {
			names[area].qt_53 = 0;
		}
		if (_.isUndefined(names[area].qt_56)) {
			names[area].qt_56 = 0;
		}
		if (_.isUndefined(names[area].qt_57)) {
			names[area].qt_57 = 0;
		}
		if (_.isUndefined(names[area].qt_58)) {
			names[area].qt_58 = 0;
		}
		if (_.isUndefined(names[area].qt_70)) {
			names[area].qt_70 = 0;
		}
		if (_.isUndefined(names[area].qt_195)) {
			names[area].qt_195 = 0;
		}
		if (_.isUndefined(names[area].qt_19)) {
			names[area].qt_19 = 0;
		}
		if (_.isUndefined(names[area].qt_20)) {
			names[area].qt_20 = 0;
		}
		if (_.isUndefined(names[area].qt_bloqueio)) {
			names[area].qt_bloqueio = 0;
		}
		if (_.isUndefined(names[area].qt_total_ocorrencias)) {
			names[area].qt_total_ocorrencias = 0;
		}
		if (_.isUndefined(names[area].qt_total_pontos_utilizados)) {
			names[area].qt_total_pontos_utilizados = 0;
		}
		if (_.isUndefined(names[area].qt_limite)) {
			names[area].qt_limite = 0;
		}
		return names;
	};
}

function GraphicSIPON() {

	this.GraphOcorrencias = $("#GraphOcorrencias");
	this.GraphCoords = $("#GraphCoords");
	this.GraphTipo = $("#GraphCoordsTipo");
	this.GraphOcorXEmp = $("#GraphCoordsPorFunc");

	GraphicSIPON.prototype.createCharts = function(arg) {
		dadosCoordenacao = arg.dadosCoordenacao;
		this.Ocorrencias(dadosCoordenacao);
		this.CoordsGeral(dadosCoordenacao);
		this.CoordPorTipo(dadosCoordenacao);
		this.empXOcor(arg.dadosOcorXEmp);
		// console.log(arg.dadosOcorXEmp);
	};

	GraphicSIPON.prototype.Ocorrencias = function(arg) {
		this.GraphOcorrencias.empty();
		this.GraphOcorrencias
				.append($("<div id='ocorTipo' style='height: 500px'></div>"));
		var chart = new CanvasJS.Chart("ocorTipo", {
			title : {
				text : "Ocorrências por Tipo",
				fontSize : 20
			},
			legend : {
				verticalAlign : "bottom",
				horizontalAlign : "center",
				fontSize : 15
			},
			animationEnabled : true,
			data : [ {
				type : "doughnut",
				indexLabel : "{y}",
				indexLabelPlacement : "outside",
				showInLegend : true,
				dataPoints : this.mergeTipos(arg),
			} ]
		});
		chart.render();
	};

	GraphicSIPON.prototype.CoordsGeral = function(arg) {
		this.GraphCoords.empty();
		this.GraphCoords
				.append($("<div id='ocorCoord' style='height: 500px'></div>"));
		var chart = new CanvasJS.Chart("ocorCoord", {
			title : {
				text : "Ocorrências por Coordenações",
				fontSize : 20
			},
			legend : {
				verticalAlign : "bottom",
				horizontalAlign : "center",
				fontSize : 12,
				itemmouseover : function(e) {
					if (typeof e.dataPoint.exploded !== 'undefined'
							&& e.dataPoint.exploded === true)
						e.dataPoint.exploded = false;
					else
						e.dataPoint.exploded = true;
					e.chart.render();
				},
				itemmouseout : function(e) {
					if (typeof e.dataPoint.exploded !== 'undefined'
							&& e.dataPoint.exploded === true)
						e.dataPoint.exploded = false;
					e.chart.render();
				}
			},
			animationEnabled : true,
			data : [ {
				type : "pie",
				indexLabel : "{y}",
				indexLabelPlacement : "outside",
				indexLabelFontSize : 15,
				showInLegend : true,
				dataPoints : this.mergeCoords(arg),
			} ],
		});
		chart.render();
	};

	GraphicSIPON.prototype.CoordPorTipo = function(arg) {
		this.GraphTipo.empty();
		var dtpoints = this.mergeCoordsTipo(arg);
		this.GraphTipo
				.append($("<div id='ocorCoordTipo' style='height: 500px'></div>"));
		var chart = new CanvasJS.Chart("ocorCoordTipo", {
			title : {
				text : "Detalhes por Coordenações"
			},
			axisX : {
				title : "Coordenações",
				labelAngle : -45,
				labelFontColor : "rgb(0,75,141)",
				labelFontSize : 13,
				interval : 1,
				titleFontSize : 20
			},
			axisY : {
				title : "Ocorrências",
				labelFontSize : 15,
				gridThickness : 0.5,
				titleFontSize : 20
			},
			legend : {
				verticalAlign : "bottom",
				horizontalAlign : "center",
				fontSize : 15
			},
			animationEnabled : true,
			data : [ {
				type : "stackedColumn",
				legendText : "Falta abonada",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_53,
			}, {
				type : "stackedColumn",
				legendText : "AREG",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_56,
			}, {
				type : "stackedColumn",
				legendText : "Intervalo < 1h",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_57,
			}, {
				type : "stackedColumn",
				legendText : "Ausência de Intervalo",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_58,
			}, {
				type : "stackedColumn",
				legendText : "Hora extra > 2hrs",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_70,
			}, {
				type : "stackedColumn",
				legendText : "Falta Não Homologada",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_19,
			}, {
				type : "stackedColumn",
				legendText : "Ausência Não homologada",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_20,
			}, {
				type : "stackedColumn",
				legendText : "Bloqueio da Trava SIPON",
				showInLegend : "true",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_bloqueio,
			}, {
				type : "stackedColumn",
				legendText : "Ponto Aberto",
				showInLegend : "true",
				indexLabel : "#total",
				indexLabelFontSize : 13,
				indexLabelPlacement : "outside",
				indexLabelFontFamily : "Lucida Console",
				indexLabelOrientation : "horizontal",
				indexLabelFontColor : "rgb(0, 0, 0)",
				indexLabelFontWeight : "bold",
				toolTipContent : "{legendText} : {y} ",
				dataPoints : dtpoints.qt_195,
			} ]
		});
		chart.render();
	};

	GraphicSIPON.prototype.empXOcor = function(arg) {
		this.GraphOcorXEmp.empty();
		var dtpoints = this.mergeOcorXemp(arg);
		this.GraphTipo
				.append($("<div id='ocorxemp' style='height: 500px'></div>"));
		var chart = new CanvasJS.Chart("ocorxemp", {
			title : {
				text : "Ocorrências / Empregados",
			// fontSize: 20
			},

			toolTip : {
				shared : "true"
			},
			axisX : {
				title : "Coordenações",
				labelAngle : -45,
				labelFontColor : "rgb(0,75,141)",
				labelFontSize : 13,
				interval : 1,
				titleFontSize : 20
			},
			axisY : {
				labelFontSize : 15,
				gridThickness : 0.5,
				titleFontSize : 20,
				title : "Total Funcionarios",
				minimum : 0,
				valueFormatString : "0.00"
			// ,
			// maximum: 5
			},
			// axisY2: {
			// labelFontSize: 15,
			// gridThickness: 0.5,
			// titleFontSize:20,
			// title: "Total Ocorrências",
			// maximum: 100
			//
			// },

			legend : {
				verticalAlign : "bottom",
				horizontalAlign : "center",
				fontSize : 15
			},
			animationEnabled : "true",
			data : [ {
				type : "column",
				name : "Funcionarios",
				legendText : "Funcionários",
				showInLegend : "true",
				indexLabel : "{y}",
				indexLabelFontSize : 13,
				yValueFormatString : "0.00",
				indexLabelPlacement : "outside",
				indexLabelFontFamily : "Lucida Console",
				indexLabelOrientation : "horizontal",
				indexLabelFontColor : "rgb(0, 0, 0)",
				indexLabelFontWeight : "bold",
				dataPoints : dtpoints.funcionarios
			}, ]
		});
		chart.render();
	};

	GraphicSIPON.prototype.mergeOcorXemp = function(arg) {
		var length = arg.length;
		var dtpoint = {};
		var nodo_funcionarios = [];
		// var nodo_ocorrencias = [];
		for (var i = 0; i < length; i++) {

			nodo_funcionarios.push({
				label : arg[i].coord,
				y : parseInt(arg[i].ocorrencias)
						/ parseInt(arg[i].funcionarios)
			});

			// console.log(parseFloat(
			// parseInt(arg[i].ocorrencias)
			// / parseInt(arg[i].funcionarios)).toFixed(2));
			// nodo_ocorrencias.push({
			// label: arg[i].coord,
			// y:parseInt(arg[i].ocorrencias)
			// });
		}
		dtpoint.funcionarios = nodo_funcionarios;
		// dtpoint.ocorrencias = nodo_ocorrencias;
		return dtpoint;
	};

	GraphicSIPON.prototype.mergeCoordsTipo = function(arg) {
		var length = arg.length;
		var dtpoint = {};
		var nodo_53 = [];
		var nodo_56 = [];
		var nodo_57 = [];
		var nodo_58 = [];
		var nodo_70 = [];
		var nodo_195 = [];
		var nodo_19 = [];
		var nodo_20 = [];
		var nodo_bloqueio = [];
		var nodo_total_ocorrencias = [];
		var nodo_total_pontos_utilizados = [];
		var nodo_limite = [];
		for (var i = 0; i < length; i++) {
			nodo_53.push({
				label : arg[i].nome,
				y : arg[i].qt_53
			});
			nodo_56.push({
				label : arg[i].nome,
				y : arg[i].qt_56
			});
			nodo_57.push({
				label : arg[i].nome,
				y : arg[i].qt_57
			});
			nodo_58.push({
				label : arg[i].nome,
				y : arg[i].qt_58
			});
			nodo_70.push({
				label : arg[i].nome,
				y : arg[i].qt_70
			});
			nodo_195.push({
				label : arg[i].nome,
				y : arg[i].qt_195
			});
			nodo_19.push({
				label : arg[i].nome,
				y : arg[i].qt_19
			});
			nodo_20.push({
				label : arg[i].nome,
				y : arg[i].qt_20
			});
			nodo_bloqueio.push({
				label : arg[i].nome,
				y : arg[i].qt_bloqueio
			});
			nodo_total_ocorrencias.push({
				label : arg[i].nome,
				y : arg[i].qt_total_ocorrencias
			});
			nodo_total_pontos_utilizados.push({
				label : arg[i].nome,
				y : arg[i].qt_total_pontos_utilizados
			});
			nodo_limite.push({
				label : arg[i].nome,
				y : arg[i].qt_limite
			});
		}

		dtpoint.qt_53 = nodo_53;
		dtpoint.qt_56 = nodo_56;
		dtpoint.qt_57 = nodo_57;
		dtpoint.qt_58 = nodo_58;
		dtpoint.qt_70 = nodo_70;
		dtpoint.qt_195 = nodo_195;
		dtpoint.qt_19 = nodo_19;
		dtpoint.qt_20 = nodo_20;
		dtpoint.qt_bloqueio = nodo_bloqueio;
		dtpoint.qt_total_ocorrencias = nodo_total_ocorrencias;
		dtpoint.qt_total_pontos_utilizados = nodo_total_pontos_utilizados;
		dtpoint.qt_limite = nodo_limite;

		return dtpoint;
	};

	GraphicSIPON.prototype.mergeCoords = function(arg) {
		var dtpoint = [];
		var length = arg.length;
		for (var i = 0; i < length; i++) {
			var nodo = {
				label : arg[i].nome,
				name : arg[i].nome,
				y : parseInt(arg[i].qt_53) + parseInt(arg[i].qt_56)
						+ parseInt(arg[i].qt_57) + parseInt(arg[i].qt_58)
						+ parseInt(arg[i].qt_70) + parseInt(arg[i].qt_195)
						+ parseInt(arg[i].qt_19) + parseInt(arg[i].qt_20)
						+ parseInt(arg[i].qt_bloqueio)
			};
			dtpoint.push(nodo);
		}
		return dtpoint;
	};

	GraphicSIPON.prototype.mergeTipos = function(arg) {
		var dtpoint = [ {
			y : 0,
			label : 'Falta Abonada',
			name : 'Falta Abonada'
		}, {
			y : 0,
			label : 'AREG',
			name : 'AREG'
		}, {
			y : 0,
			label : 'Intervalo < 1h',
			name : 'Intervalo < 1h'
		}, {
			y : 0,
			label : 'Ausência de Intervalo',
			name : 'Ausência de Intervalo'
		}, {
			y : 0,
			label : 'Hora Extra > 2hrs',
			name : 'Hora Extra > 2hrs'
		}, {
			y : 0,
			label : 'Ponto Aberto',
			name : 'Ponto Aberto'
		}, {
			y : 0,
			label : 'Falta Não homologada',
			name : 'Falta Não homologada'
		}, {
			y : 0,
			label : 'Ausência Não homologada',
			name : 'Ausência Não homologada'
		}, {
			y : 0,
			label : 'Bloqueio da Trava SIPON',
			name : 'Bloqueio da Trava SIPON'
		} ];

		var length = arg.length;
		for (var i = 0; i < length; i++) {
			dtpoint[0].y += parseInt(arg[i].qt_53);
			dtpoint[1].y += parseInt(arg[i].qt_56);
			dtpoint[2].y += parseInt(arg[i].qt_57);
			dtpoint[3].y += parseInt(arg[i].qt_58);
			dtpoint[4].y += parseInt(arg[i].qt_70);
			dtpoint[5].y += parseInt(arg[i].qt_195);
			dtpoint[6].y += parseInt(arg[i].qt_19);
			dtpoint[7].y += parseInt(arg[i].qt_20);
			dtpoint[8].y += parseInt(arg[i].qt_bloqueio);
		}
		return dtpoint;
	};
}

function RepopSIPON() {
	var tbodyCoordenacao = $("#tbodyCoordenacao");
	var tbodyEquipe = $("#tbodyEquipe");
	var tbodyEmpregado = $("#tbodyEmpregado");
	var areaList = $("#area");
	var porCoordenacaoTitulo = $("#porCoordenacao #titulo");
	var porEquipeTitulo = $("#porEquipe #titulo");
	var porEmpregadoTitulo = $("#porEmpregado #titulo");

	RepopSIPON.prototype.populateAll = function(retorno) {
		this.Areas(retorno.dadosEmpregados);
		this.Empregados(retorno.dadosEmpregados, retorno.dt_selecionada);
		this.TabelaGen(tbodyEquipe, porEquipeTitulo, retorno.dt_selecionada,
				retorno.equipe);
		this.TabelaGen(tbodyCoordenacao, porCoordenacaoTitulo,
				retorno.dt_selecionada, retorno.coord);
	};

	RepopSIPON.prototype.TabelaGen = function(tabela, titulo, dt_selecionada,
			dados) {
		titulo.html("Data de referência - " + dt_selecionada);
		tabela.empty();
		var html = "";
		for ( var times in dados) {
			var time = dados[times];
			html = "<tr>";
			html += "<td>" + time.nome + "</td>";
			html += "<td>" + time.qt_19 + "</td>";
			html += "<td>" + time.qt_20 + "</td>";
			html += "<td>" + time.qt_53 + "</td>";
			html += "<td>" + time.qt_56 + "</td>";
			html += "<td>" + time.qt_57 + "</td>";
			html += "<td>" + time.qt_58 + "</td>";
			html += "<td>" + time.qt_70 + "</td>";
			html += "<td>" + time.qt_195 + "</td>";
			html += "<td>" + time.qt_bloqueio + "</td>";
			html += "</tr>";
			tabela.append(html);
		}
	};

	RepopSIPON.prototype.Empregados = function(empregados, dta) {
		porEmpregadoTitulo.html("Totalização por Empregado - " + dta);
		tbodyEmpregado.empty();
		var html = "";
		var length = empregados.length;
		for (var i = 0; i < length; i++) {
			var empregado = empregados[i];
			html = "<tr id='" + empregado.coord + "'>";
			html += "<td>" + empregado.coord + "</td>";
			html += "<td>" + empregado.no_funcionario + "</td>";
			html += "<td>" + empregado.qt_19 + "</td>";
			html += "<td>" + empregado.qt_20 + "</td>";
			html += "<td>" + empregado.qt_53 + "</td>";
			html += "<td>" + empregado.qt_56 + "</td>";
			html += "<td>" + empregado.qt_57 + "</td>";
			html += "<td>" + empregado.qt_58 + "</td>";
			html += "<td>" + empregado.qt_70 + "</td>";
			html += "<td>" + empregado.qt_195 + "</td>";
			html += "<td>" + empregado.qt_bloqueio + "</td>";
			html += "<td>" + empregado.qt_total_pontos_utilizados + "</td>";
			html += "<td>" + empregado.percentual_utilizacao.toFixed(2) + "</td>";
			html += "</tr>";	
			tbodyEmpregado.append(html);
			html = "";
		}
	};

	RepopSIPON.prototype.Areas = function(retorno) {
		var areas = [];
		for (var i = 0; i < retorno.length; i++) {
			areas.push(retorno[i].coord);
		}
		areas = _.uniq(areas.sort());
		areaList.empty();
		var html = "";
		areaList.append("<option value='0'>Selecione...</option>");
		for (var j = 0; j < areas.length; j++) {
			html = "<option value='" + areas[j] + "'>";
			html += areas[j];
			html += "</option>";
			areaList.append(html);
			html = "";
		}
		areaList.val(0);
	};
}

function clickGraficos() {
	hideAll();
	$("#Graficos").show("slow");
	$("#tabGraficos").addClass('active');
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

function hideAll() {
	$("#Graficos").hide();
	$("#porCoordenacao").hide();
	$("#porEquipe").hide();
	$("#porEmpregado").hide();
	$("#exportacao").hide();
	$("#exportacaoAnual").hide();

	$("#tabGraficos").removeClass('active');
	$("#tabPorCoordenacao").removeClass('active');
	$("#tabPorEquipe").removeClass('active');
	$("#tabPorEmpregado").removeClass('active');
	$("#tabExportacao").removeClass('active');
	$("#tabExportacaoAnual").removeClass('active');
}

function geraTooltips() {
	$("#porCoordenacao #56, #porEquipe #56, #porEmpregado #56").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});

	$("#porCoordenacao #57, #porEquipe #57, #porEmpregado #57").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});

	$("#porCoordenacao #58, #porEquipe #58, #porEmpregado #58").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});

	$("#porCoordenacao #70, #porEquipe #70, #porEmpregado #70").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});

	$("#porCoordenacao #195, #porEquipe #195, #porEmpregado #195").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});

	$("#porCoordenacao #53, #porEquipe #53, #porEmpregado #53").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
	
	$("#porCoordenacao #19, #porEquipe #19, #porEmpregado #19").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
	
	$("#porCoordenacao #20, #porEquipe #20, #porEmpregado #20").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
	
	$("#porCoordenacao #bloqueio, #porEquipe #bloqueio, #porEmpregado #bloqueio").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
	
	$("#porCoordenacao #bloqueio, #porEquipe #bloqueio, #porEmpregado #pontos_utilizados").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
	
	$("#porCoordenacao #bloqueio, #porEquipe #bloqueio, #porEmpregado #percentual_utilizacao").tooltip({
		show : null,
		position : {
			my : "left top",
			at : "left bottom"
		},
		open : function(event, ui) {
			ui.tooltip.animate({
				top : ui.tooltip.position().top + 10
			}, "fast");
		}
	});
}
