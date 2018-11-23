$(document).ready(function() {
	loadMsg()
	createTables()
	createRamaisNotFound()

	$("#ligacoes").addClass("active")

	// Ajax da mudança da seleção do mês
	$('#dtReferencia').change(function(e) {
		loadMsg()

		$.ajax({
			type: "POST",
			url: "/indicadores/ligacoes/ajax-coord-equipe",
			data: {
				dt_referencia: $("#dtReferencia option:selected").text(),
			},
			success: function (data) {
				$('coordsEquipes').remove()

				data = JSON.parse(data)
				data = data['data']

				keysOnly = Object.keys(data)

				$('.coordsEquipes').remove()
				$('#equipes').find('option').remove()

				keysOnly = Object.keys(data)
				for (var i = 0; i < keysOnly.length; i++) {
					$('#equipes').append(`<option>${keysOnly[i]}</option>`)
				}
				$.each(data, function(index, value) {
				    let equipes = value.join('|');

						$(`<input class='coordsEquipes' id='${index}' type='hidden' value='${equipes}'>`).appendTo('body');
				});
			}
		})

		createTables()
		createRamaisNotFound()
	})

	// Create modal with clicked number
	$('#exampleModal').on('show.bs.modal', function (event) {
	  var link = $(event.relatedTarget)
	  var ramal = link.data('whatever') // Extract info from data-* attributes
	  var modal = $(this)
	  modal.find('.modal-title').text('Novo número a serviço para: ' + ramal)


	  // Retrieve already registered numbers
	  $.ajax({
		  type: "POST",
		  url: "/indicadores/telefones-servico/ajax-list-tel",
		  data: {
			  ramal: ramal,
		  },
		  success: function (data) {
			  data = JSON.parse(data)
			  for (let i = 0; i < data.length; i++) {
				  $("#listaCadastrados").append(`<div class="d-block"> <img class="mydeletetag" id="${data[i].id}" src='/img/delete.png' height="18" width="18"> <p>${data[i].numero_servico}</p></div>`)
			  }
		  	  // Delete new number to db
			  $( ".mydeletetag").click(function() {
		  		  $.ajax({
					  type: "POST",
					  url: "/indicadores/telefones-servico/ajax-delete-telefone-servico",
					  data: {
						  id: this.id
					  }
		  		  })

				  $(this).parent().remove()
			  })
		  }
	  })

	  // Add new number to db
	  $('#addNumeroServico').unbind().click(function() {
		  $.ajax({
			  type: "POST",
			  url: "/indicadores/telefones-servico/ajax-save-telefone-servico",
			  data: {
				  ramal: ramal,
				  numeroServico: $('#telefoneServico').val()
			  }
		  })

		  $('#exampleModal').modal('toggle')
	  })

		// clear modal on close
		$('#exampleModal').on('hidden.bs.modal', function (e) {
		  $(this)
		    .find("input,textarea,select")
		       .val('')
		       .end()

	       $("#listaCadastrados").empty()
		})
	})
})

var ramais = {}
function createTables() {
	let counter = 0
	let coordData
	let equipeData
	const tipos = ['Coord', 'Equipe', 'Funcionario', 'All']
	for (let i = 0; i < tipos.length; i++) {
		$('#tabelaPor' + tipos[i]).DataTable().destroy()
		let columns = undefined
		let columnDefs = []
		let columnSum = undefined

		if (tipos[i] === 'Funcionario') {
			columns = [
				{ "data": "equipe" },
				{ "data": "matricula" },
				{ "data": tipos[i].toLowerCase() },
				{ "data": "numero" },
				{ "data": "duracao" },
				{ "data": "valor" },
			]

			columnDefs = [
				{
					"targets": [0],
					"visible": false
				},
				{
					"targets": [3],
		            render: function ( data, type, row, meta ) {
		                if(type === 'display'){
		                    data = '<a data-toggle="modal" data-target="#exampleModal" data-whatever="'+ data +'">' + data + '</a>'
		                }

		                return data
		            }
				}
			]
			columnSum = [4, 5]
		} else if (tipos[i] === 'All') {
			columnDefs = [
				{
					"targets": [0],
					"visible": false
				}
			]

			columns = [
				{ "data": "coord" },
				{ "data": "matricula" },
				{ "data": "funcionario" },
				{ "data": "numero" },
				{ "data": "numero_chamado" },
				{ "data": "duracao" },
				{ "data": "valor" },
				{ "data": "hora_ocorrencia", render: function(data,type,row) { data = new Date(data); return data.toLocaleDateString() + ' ' + data.toLocaleTimeString() } },
				{ "data": "a_servico", render: function(data,type,row) { return data ? 'Sim': 'Indefinido' } }
			]

			columnSum = [5, 6]

		} else {
			columns = [
				{ "data": tipos[i].toLowerCase() },
				{ "data": "duracao" },
				{ "data": "valor" }
			]
			columnSum = [1, 2]
		}

		createDataTable(`#tabelaPor${tipos[i]}`, tipos[i], columns, columnDefs, columnSum)

	}
}

var tables = {}
function createDataTable(tableID, dataType, columns, columnDefs, columnSum) {
	table = $(tableID).DataTable(
		{
			"search": {
				"regex": true
			},
			"bDestroy": true,
			"order": [[0, 'desc']],
			"ajax": {
				"url": "ligacoes/ajax-get-data",
				"type" : "POST",
				"data" : {
					dt_referencia: $("#dtReferencia option:selected").text(),
					tipo: dataType,
				},
				"dataType" : "json",
				"dataSrc" : function(json, b, c) {
					if ('Coord' === json.tipo) {
						nonCEDESGraph(json.data)
						CEDESGraph(json.data)
						hideMsg()
					}
					//console.log(json.data);
					return json.data
				}
			},
			"language": {
			    "sEmptyTable": "Nenhum registro encontrado",
			    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
			    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
			    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
			    "sInfoPostFix": "",
			    "sInfoThousands": ".",
			    "sLengthMenu": "_MENU_ resultados por página",
			    "sLoadingRecords": "Carregando...",
			    "sProcessing": "Processando...",
			    "sZeroRecords": "Nenhum registro encontrado",
			    "sSearch": "Pesquisar",
			 	"oPaginate": {
			        "sNext": "Próximo",
			        "sPrevious": "Anterior",
			        "sFirst": "Primeiro",
			        "sLast": "Último"
			    },
			    "oAria": {
			        "sSortAscending": ": Ordenar colunas de forma ascendente",
			        "sSortDescending": ": Ordenar colunas de forma descendente"
			    }
			},
			"columns": columns,
			"columnDefs": columnDefs,
			"footerCallback": function ( tfoot, data, start, end, display ) {
				var api = this.api()
				for (let i = 0; i < columnSum.length; i++) {
					$(api.column(columnSum[i]).footer()).html(
						api.column(columnSum[i], {page:'current'}).data().reduce((a, b) => {
							if(b.indexOf(':') !== -1) {
								if (a === 0)
									a = '00:00:00'
								return addTimes(a, b) // this function is on add_two_times.js
							} else {
								return (parseFloat(a) + parseFloat(b)).toFixed(2)
							}
						}, 0)
					)
				}
		    }
		}
	)

	expendablesCells(dataType, table, tableID)

}


function expendablesCells(dataType, table, tableID) {

	if (dataType === "Coord") {
		tables["Coord"] = table
	} else if (dataType === "Equipe") {
		tables["Equipe"] = table
	} else if (dataType === "Funcionario") {
		tables["Funcionario"] = table
	} else if (dataType === "All") {
		tables["All"] = table
	}

	$(tableID).off("click").on('click', 'tbody tr td:first-of-type', function() {

	  $('.nav-tabs > .active').next('li').find('a').trigger('click')

	  if (dataType === "Coord") {
		  return tables["Equipe"].search(this.textContent).draw()
	  } else if (dataType === "Equipe") {
		  tables["Funcionario"].column(0).search("^" +  this.textContent  + "$", true, false, true).draw()
	  } else if (dataType === "Funcionario") {
		  tables["All"].search(this.textContent).draw()
	  }

	})
}

function createRamaisNotFound() {
	let ramaisNotFoundTable = $('#tabelaRamaisNotFound').DataTable({
		"ajax": {
			"url": "ligacoes/ajax-ramais-not-found",
			"type" : "POST",
			"dataType" : "json",
			"data" : {
				dt_referencia: $("#dtReferencia option:selected").text(),
			}
		},
		"bDestroy": true,
		"language": {
		    "sEmptyTable": "Nenhum registro encontrado",
		    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
		    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
		    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
		    "sInfoPostFix": "",
		    "sInfoThousands": ".",
		    "sLengthMenu": "_MENU_ resultados por página",
		    "sLoadingRecords": "Carregando...",
		    "sProcessing": "Processando...",
		    "sZeroRecords": "Nenhum registro encontrado",
		    "sSearch": "Pesquisar",
		 	"oPaginate": {
		        "sNext": "Próximo",
		        "sPrevious": "Anterior",
		        "sFirst": "Primeiro",
		        "sLast": "Último"
		    },
		    "oAria": {
		        "sSortAscending": ": Ordenar colunas de forma ascendente",
		        "sSortDescending": ": Ordenar colunas de forma descendente"
		    }
		},
		"fnDrawCallback": function() {
			$('.addRow').unbind().click(function(e){
				let row = $(this).closest('tr')
				ramaisNotFoundTable.row(row).remove().draw()

				let telefone = $(row.find('td')[0]).text()
				let nome = $(row.find('td')[1]).text()
				let matricula = $(row.find('td').find('input')[0]).val()
				let coord = $(row.find('td').find('input')[1]).val()
				let equipe = $(row.find('td').find('input')[2]).val()
				let dt_referencia = $(row.find('td').find('input')[3]).val()

	  		    $.ajax({
				    type: "POST",
				    url: "/indicadores/ligacoes/ajax-save-func",
				    data: {
				    	telefone: telefone,
				    	nome: nome,
				    	matricula: matricula,
				    	coord: coord,
				    	equipe: equipe,
				    	dt_referencia: dt_referencia
				    }
	  		    })
			})
		},
		"columns": [
			{ "data": "numero" },
			{ "data": "nome_usuario" },
			{ "data": "matricula",  render: function(data,type,row) { return `<input type="text" name="matricula" value="${data ? data: ''}">` } },
			{ "data": "coord",  render: function(data,type,row) { return `<input type="text" name="coord" value="${data ? data: ''}">` } },
			{ "data": "equipe",  render: function(data,type,row) { return `<input type="text" name="equipe" value="${data ? data: ''}">` } },
			{ "data": "",  render: function(data,type,row) { return `<input type="text" class="currentDate" name="dt_referencia">` } },
			{ "data": "",  render: function(data,type,row) {
				return '<a class="addRow" href="#"><img style="width: 25px; margin-left: 35px" src="/indicadores/img/plus.png" /> </a>'
				}
			}
		],
	} )
}

function nonCEDESGraph(coordData) {
	let data1 = coordData.map((v) => {
		if (!v.coord.includes('CEDESBR')) {
			return v
		}
	}).filter(v => v)


	let data = []
	for (let i = 0; i < data1.length; i++) {
		data.push({
				'label': data1[i].coord,
				'y': parseFloat(data1[i].duracao_minutos)
			})
	}
	var chart = new CanvasJS.Chart("chartContainerNONCEDES", {
		title : {
			text : "Ligações Prestadores",
		  fontSize: 21
		},

		toolTip : {
			shared : "true"
		},
		axisX : {
			title : "Prestadores",
			labelAngle : -70,
			labelFontColor : "rgb(0,75,141)",
			labelFontSize : 13,
			interval : 1,
			titleFontSize : 20
		},
		axisY : {
			labelFontSize : 15,
			gridThickness : 0.5,
			titleFontSize : 20,
			title : "Minutos",
			minimum : 0,
			valueFormatString : "0.00"
		},
		legend : {
			verticalAlign : "bottom",
			horizontalAlign : "center",
			fontSize : 15
		},
		animationEnabled : "true",
		data : [ {
			type : "column",
			name : "Minutos",
			legendText : "Minutos",
			showInLegend : "true",
			indexLabel : "{y}",
			indexLabelFontSize : 13,
			indexLabelPlacement : "outside",
			indexLabelFontFamily : "Lucida Console",
			indexLabelOrientation : "horizontal",
			indexLabelFontColor : "rgb(0, 0, 0)",
			indexLabelFontWeight : "bold",
			dataPoints : data
		}, ]
	})
	chart.render()
}

function CEDESGraph(coordData) {
	let data1 = coordData.map((v) => {
		if (v.coord.includes('CEDESBR')) {
			return v
		}
	}).filter(v => v)


	let data = []
	for (let i = 0; i < data1.length; i++) {
		data.push({
				'label': data1[i].coord,
				'y': parseFloat(data1[i].duracao_minutos)
			})
	}
	var chart = new CanvasJS.Chart("chartContainerCEDES", {
		title : {
			text : "Ligações CEDES",
		  fontSize: 21
		},

		toolTip : {
			shared : "true"
		},
		axisX : {
			title : "Funcionarios",
			labelAngle : -70,
			labelFontColor : "rgb(0,75,141)",
			labelFontSize : 13,
			interval : 1,
			titleFontSize : 20
		},
		axisY : {
			labelFontSize : 15,
			gridThickness : 0.5,
			titleFontSize : 20,
			title : "Minutos",
			minimum : 0,
			valueFormatString : "0.00"
		},
		legend : {
			verticalAlign : "bottom",
			horizontalAlign : "center",
			fontSize : 15
		},
		animationEnabled : "true",
		data : [ {
			type : "column",
			name : "Minutos",
			legendText : "Minutos",
			showInLegend : "true",
			indexLabel : "{y}",
			indexLabelFontSize : 13,
			indexLabelPlacement : "outside",
			indexLabelFontFamily : "Lucida Console",
			indexLabelOrientation : "horizontal",
			indexLabelFontColor : "rgb(0, 0, 0)",
			indexLabelFontWeight : "bold",
			dataPoints : data
		}, ]
	})
	chart.render()
}
