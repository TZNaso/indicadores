$(document).ready(function () {

    $("#btnAlteraSenha").click(function () {

        if ($.trim($("[name='nova_senha']").val()) == "" || $.trim($("[name='nova_senha2']").val()) == "") {

            alertPage($("#alertaAlteraSenha"), "Preencha todos os campos", "error");

        } else {

            if ($("[name='nova_senha']").val() == $("[name='nova_senha2']").val()) {

                $.ajax({
                    url: './..' + baseUrl + '/usuario/ajax-alterar-senha',
                    async: false,
                    type: 'POST',
                    data: "senha=" + $("[name='nova_senha']").val(),
                    dataType: 'json',
                    success: function (retorno) {

                        if (retorno) {
                            alertPage($("#alertaAlteraSenha"), retorno['msg'], retorno['type']);

                            if (retorno['type'] = 'success') {
                                $("[name='nova_senha']").val('');
                                $("[name='nova_senha2']").val('');
                            }

                        }
                    }
                });

            } else {
                alertPage($("#alertaAlteraSenha"), "Senhas não conferem. Redigite e tente novamente", "error");
            }


        }


    });

});

function showModalAlteraSenha() {
    $("#modalAlteraSenha").modal('show');
}