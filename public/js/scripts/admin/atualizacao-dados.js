$(document).ready(function () {

    $("#btnEmpregados").click(function () {

        $('#funcionarios #spanButton').addClass('glyphicon glyphicon-refresh');
        $('#funcionarios #spanText').html('Executando Atualização');
        $('#funcionarios #imgCarregando').show();

        $.ajax({
            url: './../admin/ajax-atualiza-areas',
            async: true,
            type: 'POST',
            dataType: 'json',
            success: function (retorno) {

                if (retorno) {

                    alertPage($("#areas .well"), "Atualização realizada com sucesso!", 'success');
                    $("#areas #dtAtualizacao").html("<b>Data da última atualização: </b>" + retorno['data']);
                    $('#areas #spanButton').addClass('glyphicon glyphicon-repeat');
                    $('#areas #spanText').html('Executar Atualização');
                    $('#areas #imgCarregando').hide();

                } else {

                    alertPage($("#areas .well"), "Ocorreu um erro durante a atualização!", 'error');

                    $('#areas #spanButton').addClass('glyphicon glyphicon-repeat');
                    $('#areas #spanText').html('Executar Atualização');
                    $('#areas #imgCarregando').hide();

                }

            }
        });

        $.ajax({
            url: './../admin/ajax-atualiza-empregados',
            async: true,
            type: 'POST',
            dataType: 'json',
            success: function (retorno) {

                if (retorno) {

                    alertPage($("#funcionarios .well"), "Atualização realizada com sucesso!", 'success');
                    $("#funcionarios #dtAtualizacao").html("<b>Data da última atualização: </b>" + retorno['data']);
                    $('#funcionarios #spanButton').addClass('glyphicon glyphicon-repeat');
                    $('#funcionarios #spanText').html('Executar Atualização');
                    $('#funcionarios #imgCarregando').hide();

                } else {

                    alertPage($("#funcionarios .well"), "Ocorreu um erro durante a atualização!", 'error');

                    $('#funcionarios #spanButton').addClass('glyphicon glyphicon-repeat');
                    $('#funcionarios #spanText').html('Executar Atualização');
                    $('#funcionarios #imgCarregando').hide();

                }

            }
        });

    //});


    //$("#btnAreas").click(function () {

        //$('#areas #spanButton').addClass('glyphicon glyphicon-refresh');
        //$('#areas #spanText').html('Executando Atualização');
        //$('#areas #imgCarregando').show();

        

    });

    $("#menuAtualizacaoDados").addClass("active");
    $("#menuCargaDados").removeClass("active");
    $("#ocorrenciasSipon").removeClass("active");
    $("#horaExtra").removeClass("active");


});

function clickEmpregados() {

    $("#areas").hide();
    $("#tabAreas").removeClass('active');
    $("#funcionarios").show("slow");
    $("#tabFuncionarios").addClass('active');

}

//function clickAreas() {

    //$("#funcionarios").hide();
    //$("#tabFuncionarios").removeClass('active');
    //$("#areas").show("slow");
   //$("#tabAreas").addClass('active');

//}
