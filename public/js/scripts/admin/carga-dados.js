$(document).ready(function () {
   $('#container_nr_arquivo').hide()
    $("#menuCargaDados").addClass("active");
    $('#relGenerico #dia_mes_ano').mask('99/99/9999');

    $("#btnFormGenerico").click(function () {
        var camposNaoPreenchidos = "";
        if ($("#tipo_arquivo option:selected").val() === "default") {
            camposNaoPreenchidos += "Tipo, ";
        }
        if ($("[name='formRelGenerico'] #arquivo_gen").val() === "") {
            camposNaoPreenchidos += "arquivo, ";
        }
        if ($("[name='formRelGenerico'] #dia_mes_ano").val() === "") {
            camposNaoPreenchidos += " data de referência, ";
        }
        if ($("[name='formRelGenerico'] #linha_inicio").val() === "") {
            camposNaoPreenchidos += " linha de início dos dados";
        }
        if (camposNaoPreenchidos !== "") {
            alertPage($("#relGenerico .well"), "Campo(s) não preenchido(s): " + camposNaoPreenchidos, 'error');
            return false;
        }
        $("[name='formRelGenerico']").submit();
    });

    $('#tipo_arquivo').change(function() {
        if ($(this).val() === 'ligacoes') {
            $('#container_nr_arquivo').show()
        } else {
          $('#container_nr_arquivo').hide()
        }
    });

    $('#prestadores').on('change', function() {
    	$('#dia_mes_ano').val(this.value)
    	$('#tipo_arquivo').val('prestadores')
      $('#tipo_arquivo').change()
	});

    $('#sipon').on('change', function() {
    	$('#dia_mes_ano').val(this.value)
    	$('#tipo_arquivo').val('sipon')
      $('#tipo_arquivo').change()
	});

    $('#h_extra').on('change', function() {
    	$('#dia_mes_ano').val(this.value)
    	$('#tipo_arquivo').val('hem')
      $('#tipo_arquivo').change()
	});

    $('#liga').on('change', function() {
      values = this.value.split(' ')
      $('#dia_mes_ano').val(values[0])
      if (Number.isInteger(parseInt(values[1]))) {
        $('#nr_arquivo').val(values[1])
      } else {
        $('#nr_arquivo').val('')
      }
    	$('#tipo_arquivo').val('ligacoes')
      $('#tipo_arquivo').change()
	});

    $('#fundamental').on('change', function() {
    	$('#dia_mes_ano').val(this.value)
    	$('#tipo_arquivo').val('fununiv')
      $('#tipo_arquivo').change()
	});

    $('#lider').on('change', function() {
    	$('#dia_mes_ano').val(this.value)
    	$('#tipo_arquivo').val('liduniv')
      $('#tipo_arquivo').change()
	});
});
