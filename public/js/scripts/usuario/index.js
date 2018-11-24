$(document).ready(function () {

    $("#horasExtras").removeClass("active");
    $("#ocorrenciasSipon").removeClass("active");
    $("#menuCargaDados").removeClass("active");
    $("#menuAtualizacaoDados").removeClass("active");
    $("#menuUsuarios").removeClass("active");

    $('#btnCadastrar').click(function (e) {

        if ($.trim($("[name='usuario']").val()) == '' || $.trim($("[name='senha']").val()) == '') {
            
            alertPage($("#novo .well"), 'Preencha todos os campos', 'error');
            
        } else {

            $.ajax({
                url: 'usuario/ajax-cadastrar-usuario',
                async: false,
                type: 'POST',
                data: $("#frmCadastro").serialize(),
                dataType: 'json',
                success: function (retorno) {

                    if (retorno) {

                        alertPage($("#novo .well"), retorno['msg'], retorno['type']);

                        if (retorno['type'] == 'success') {

                            var strTable = "";

                            strTable = "<tr>";
                            strTable += "<td>" + retorno['user_id'] + "</td>";
                            strTable += "<td>" + retorno['nome'] + "</td>";
                            strTable += "<td>" + retorno['area'] + "</td>";
                            strTable += '<td></td>';
                            strTable += "</tr>";

                            $('#todos tbody').append(strTable);
                            $("#frmCadastro :input").val('');

                        }

                    }
                }
            });

        }

    });

});

function clickTodos() {

    $("#novo").hide();
    $("#tabNovo").removeClass('active');
    $("#administradores").hide();
    $("#tabAdmin").removeClass('active');
    $("#todos").show("slow");
    $("#tabTodos").addClass('active');


}

function clickAdmin() {

    $("#novo").hide();
    $("#tabNovo").removeClass('active');
    $("#administradores").show('slow');
    $("#tabAdmin").addClass('active');
    $("#todos").hide();
    $("#tabTodos").removeClass('active');

}

function clickNovo() {

    $("#novo").show('slow');
    $("#tabNovo").addClass('active');
    $("#administradores").hide();
    $("#tabAdmin").removeClass('active');
    $("#todos").hide();
    $("#tabTodos").removeClass('active');

}

function tornaAdmin(nuUsuario) {

    $.ajax({
        url: 'usuario/ajax-torna-admin',
        async: false,
        type: 'POST',
        data: 'nu_usuario=' + nuUsuario,
        dataType: 'json',
        success: function (retorno) {

            if (retorno) {
                alertPage($("#todos .well"), retorno['msg'], retorno['type']);
                $("#todos-" + nuUsuario).clone().appendTo('#administradores tbody');
                $('#administradores tbody #todos-' + nuUsuario + ' td:last').html('');
            }
        }
    });

}

function excluiAdmin(nuUsuario) {
    $.ajax({
        url: 'usuario/ajax-exclui-admin',
        async: false,
        type: 'POST',
        data: 'nu_usuario=' + nuUsuario,
        dataType: 'json',
        success: function (retorno) {

            if (retorno) {
                alertPage($("#administradores .well"), retorno['msg'], retorno['type']);
                $("#admin-" + nuUsuario).remove();
            }
        }
    });
}

function excluiUsuario(nuUsuario) {
    $.ajax({
        url: 'usuario/ajax-exclui-usuario',
        async: false,
        type: 'POST',
        data: 'nu_usuario=' + nuUsuario,
        dataType: 'json',
        success: function (retorno) {

            if (retorno) {
                alertPage($("#todos .well"), retorno['msg'], retorno['type']);
                $("#admin-" + nuUsuario).remove();
                $("#todos-" + nuUsuario).remove();
            }
        }
    });
}