$(document).ready(function () {

    $('#btnValidar').click(function (e) {

        $.ajax({
            url: './../admin/ajax-get-empregado',
            async: false,
            type: 'POST',
            data: 'matricula=' + $("[name='matricula']").val(),
            dataType: 'json',
            success: function (retorno) {
                    
                   if(retorno['type'] == 'success') {
                       
                       $("#respostaValidacao").html(retorno['msg']);
                       $("[name='senha']").parent().show('slow');
                       $("[name='nu_funcionario']").val(retorno['nu_funcionario']);
                       $("#btnValidar").hide();
                       $("#btnCadastrar").show('slow');
                       
                   } else {
                       
                       $("[name='nu_funcionario']").val('');
                       $("#respostaValidacao").html(retorno['msg']);
                       
                   }
            
            }
        });

    });

});