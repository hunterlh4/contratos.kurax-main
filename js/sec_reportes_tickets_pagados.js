function sec_reportes_tickets_pagados() {
	if (sec_id == 'reportes' && sub_sec_id=='tickets_pagados') {
		$('#SecRepTkP_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepTkP_fecha_inicio').val($('#SecRepTkP_g_fecha_actual').val());

		$('#SecRepTkP_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepTkP_fecha_fin').val($('#SecRepTkP_g_fecha_actual').val());
        $('#SecRepTkP_zona').select2();

		$('#SecRepTkP_btn_buscar').click(function(){
			$.ajax({
				url: "/sys/get_reportes_tickets_pagados.php",
				type: 'POST',
				data: SecRptTkP_buscar_dt_data(),
				beforeSend: function() {
					loading("true");
				},
				complete: function() {
					loading();
				},
				success: function(resp) {
					var respuesta = JSON.parse(resp);
					var $tbodyTablaTicketsPagados = $('#tbody_tabla_tickets_pagados');
					var $divTablaTicketsPagadosFooter = $('#div_tabla_tickets_pagados_footer');

					switch (respuesta.http_code) {
						case 200:
							$tbodyTablaTicketsPagados.html(respuesta.result);
							$divTablaTicketsPagadosFooter.html(respuesta.result_footer);
							break;

						case 400:
							$tbodyTablaTicketsPagados.html(respuesta.result);
							break;

						default:
							swal({
								title: respuesta.consulta_error,
								type: 'info',
								timer: 5000,
								showConfirmButton: true
							});
					}
					loading(false);
				},
				error: function() {}
			});
		});
		$('#SecRepTkP_btn_exportar').click(function(){
			SecRptTkP_exportar_xls();
		});

		$('#SecRepTkP_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepTkP_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTkP_fecha_inicio").val($("#SecRepTkP_g_fecha_actual").val());
			}
		});

		$('#SecRepTkP_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepTkP_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepTkP_fecha_fin").val($("#SecRepTkP_g_fecha_actual").val());
			}
		});
	}
}

function SecRptTkP_exportar_xls(){
	var SecRepTkP_tipo_busqueda = $("#SecRepTkP_tipo_busqueda").val();
	var SecRepTkP_fecha_inicio = $.trim($("#SecRepTkP_fecha_inicio").val());
	var SecRepTkP_fecha_fin = $.trim($("#SecRepTkP_fecha_fin").val());
	var SecRepTkP_cliente_tipo = $("#SecRepTkP_cliente_tipo").val();
	var SecRepTkP_cliente_texto = $("#SecRepTkP_cliente_texto").val();
	var SecRepTkP_proveedor = $("#SecRepTkP_proveedor").val();
    var SecRepTkP_zona = $("#SecRepTkP_zona").val();
    var SecRepTkP_num_transaccion = $('#SecRepTkP_num_transaccion').val();
	
	var data = {
		"accion": "SecRptTkP_exportar_xls",
		"tipo_busqueda": SecRepTkP_tipo_busqueda,
		"fecha_inicio": SecRepTkP_fecha_inicio,
		"fecha_fin": SecRepTkP_fecha_fin,
		"cliente_tipo": SecRepTkP_cliente_tipo,
		"cliente_texto": SecRepTkP_cliente_texto,
		"proveedor": SecRepTkP_proveedor,
		"num_transaccion": SecRepTkP_num_transaccion,
		"zona": SecRepTkP_zona
	}

	$.ajax({
        url: "/sys/get_reportes_tickets_pagados.php",
        type: 'POST',
        data: data,
        beforeSend: function() {
            loading("true");
        },
        complete: function() {
            loading();
        },
        success: function(resp) {
            let obj = JSON.parse(resp);
            window.open(obj.path);
            loading(false);
        },
        error: function() {}
    });
}

// LISTAS TRANSACCIONES
function SecRptTkP_buscar_dt() {
	var ftable = $('#SecRepTkP_tabla_registros').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepTkP_tabla_registros').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_tickets_pagados.php",
			"data": SecRptTkP_buscar_dt_data()
		},
		"order": [],
		"language": {
			"processing": "Procesando...",
			"lengthMenu": "Mostrar _MENU_ registros",
			"zeroRecords": "No se encontraron resultados",
			"emptyTable": "Ningún dato disponible en esta tabla",
			"info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
			"infoFiltered": "(filtrado de un total de _MAX_ registros)",
			"infoPostFix": "",
			"search": "Buscar: ",
			"url": "",
			"infoThousands": ",",
			"loadingRecords": "Cargando...",
			"paginate": {
				"first": "Primero",
				"last": "Último",
				"next": "Siguiente",
				"previous": "Anterior"
			},
			"aria": {
				"sortAscending": ": Activar para ordenar la columna de manera ascendente",
				"sortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		},
		"columns": [
			{
				"data": null,
				"sortable": false,
				render: function (data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{ "data": "txn_id" },
			{ "data": "fecha_creacion" },
			{ "data": "zona_creadora" },
			{ "data": "caja_creadora" },
			{ "data": "fecha_calculo" },
			{ "data": "fecha_pago" },
			{ "data": "caja_pagadora" },
			{ "data": "monto_apostado" },
			{ "data": "monto_ganado" },
			{ "data": "num_doc" },
			{ "data": "cliente" },
			{ "data": "proveedor" }
		]
	});
}

function SecRptTkP_buscar_dt_data(){
	var SecRepTkP_tipo_busqueda = $("#SecRepTkP_tipo_busqueda").val();
	var SecRepTkP_fecha_inicio = $.trim($("#SecRepTkP_fecha_inicio").val());
	var SecRepTkP_fecha_fin = $.trim($("#SecRepTkP_fecha_fin").val());
	var SecRepTkP_cliente_tipo = $("#SecRepTkP_cliente_tipo").val();
	var SecRepTkP_cliente_texto = $("#SecRepTkP_cliente_texto").val();
	var SecRepTkP_proveedor = $("#SecRepTkP_proveedor").val();
    var SecRepTkP_zona = $("#SecRepTkP_zona").val();
    var SecRepTkP_num_transaccion = $('#SecRepTkP_num_transaccion').val();
    var currentPage = $('#currentPage').val();
	
	if(parseInt(SecRepTkP_cliente_tipo) != 0){
		if(SecRepTkP_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRepTkP_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}

	currentPage = currentPage.length == 0 ? 1 : currentPage;

	var data = {
		"accion": "SecRptTkP_listar_registros",
		"tipo_busqueda": SecRepTkP_tipo_busqueda,
		"fecha_inicio": SecRepTkP_fecha_inicio,
		"fecha_fin": SecRepTkP_fecha_fin,
		"cliente_tipo": SecRepTkP_cliente_tipo,
		"cliente_texto": SecRepTkP_cliente_texto,
		"proveedor": SecRepTkP_proveedor,
		"zona": SecRepTkP_zona,
		"num_transaccion": SecRepTkP_num_transaccion,
		"page": currentPage
	}
	return data;
}

function sec_reportes_tickets_pagados_cambiar_de_pagina(pagina) {
	$('#currentPage').val(pagina);
	$('#SecRepTkP_btn_buscar').click();
}