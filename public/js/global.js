
function loadMsg() {
    alertPage($("#loadmsg"), "Carregando dados do banco..." , 'info');
}

function hideMsg() {
    $("#loadmsg").hide('slow');
}

function alertPage(select, msg, type) {
    if (type == 'success') {
        select.html("<spam style='color:green;'>" + msg + "</spam>");
        select.show('slow');
        alertHide(select);
    }  else if (type == 'info') {
        select.html("<spam style='color:blue;'>" + msg + "</spam>");
        select.show('slow');
    }
    else {
        select.html("<spam style='color:red;'>" + msg + "</spam>");
        select.show('slow');
        alertHide(select);
    }
}

function alertHide(select) {
    setTimeout(function () {
        select.hide('slow');
    }, 5000);
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

function clickRamaisNotFound() {
	hideAll();
	$("#ramaisNotFound").show("slow");
	$("#tabRamaisNotFound").addClass('active');
  $('.currentDate').val("01/" + $('#dtReferencia').val());
}

function clickPorEmpregado() {
	hideAll();
	$("#porEmpregado").show("slow");
	$("#tabPorEmpregado").addClass('active');
}

function clickDetalhamentoEmpregado() {
	hideAll();
	$("#detalhamentoEmpregado").show("slow");
	$("#tabDetalhamentoEmpregado").addClass('active');
}

function clickExportarDados() {
  hideAll();
  $("#exportarDados").show("slow");
  $("#tabExportarDados").addClass('active');
}

function hideAll() {
	$("#Graficos").hide();
	$("#porCoordenacao").hide();
	$("#porEquipe").hide();
	$("#porEmpregado").hide();
	$("#detalhamentoEmpregado").hide();
	$("#ramaisNotFound").hide();
  $("#exportarDados").hide();


	$("#tabGraficos").removeClass('active');
	$("#tabPorCoordenacao").removeClass('active');
	$("#tabPorEquipe").removeClass('active');
	$("#tabPorEmpregado").removeClass('active');
	$("#tabDetalhamentoEmpregado").removeClass('active');
	$("#tabRamaisNotFound").removeClass('active');
  $("#tabExportarDados").removeClass('active');
}
