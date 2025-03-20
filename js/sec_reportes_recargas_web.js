function sec_reportes_recargas_web() {
	if (sec_id == 'reportes' && sub_sec_id=='recargas_web') {
		$('#SecRepRW_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepRW_fecha_inicio').val($('#SecRepRW_g_fecha_actual').val());

		$('#SecRepRW_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepRW_fecha_fin').val($('#SecRepRW_g_fecha_actual').val());

		$('#SecRepRW_btn_buscar').click(function(){
			SecRptRW_buscar_dt();
			SecRepRW_listar_totales();
		});
		$('#SecRepRW_btn_exportar').click(function(){
			SecRptRW_exportar_xls();
		});

		$('#SecRepRW_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepRW_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepRW_fecha_inicio").val($("#SecRepRW_g_fecha_actual").val());
			}
		});

		$('#SecRepRW_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepRW_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepRW_fecha_fin").val($("#SecRepRW_g_fecha_actual").val());
			}
		});
	}
}

function SecRptRW_exportar_xls(){
	var SecRepRW_tipo_busqueda = $("#SecRepRW_tipo_busqueda").val();
	var SecRepRW_fecha_inicio = $.trim($("#SecRepRW_fecha_inicio").val());
	var SecRepRW_fecha_fin = $.trim($("#SecRepRW_fecha_fin").val());
	var SecRepRW_cliente_tipo = $("#SecRepRW_cliente_tipo").val();
	var SecRepRW_cliente_texto = $("#SecRepRW_cliente_texto").val();
	
	var data = {
		"accion": "SecRptRW_exportar_xls",
		"tipo_busqueda": SecRepRW_tipo_busqueda,
		"fecha_inicio": SecRepRW_fecha_inicio,
		"fecha_fin": SecRepRW_fecha_fin,
		"cliente_tipo": SecRepRW_cliente_tipo,
		"cliente_texto": SecRepRW_cliente_texto
	}

	$.ajax({
        url: "/sys/get_reportes_recargas_web.php",
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
function SecRptRW_buscar_dt() {
	var ftable = $('#SecRepRW_tabla_registros').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepRW_tabla_registros').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_recargas_web.php",
			"data": SecRptRW_buscar_dt_data()
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
			{ "data": "operation_id" },
			{ "data": "local_cierre" },
			{ "data": "fecha_registro" },
			{ "data": "tipo" },
			{ "data": "proveedor" },
			{ "data": "bono" },
			{ "data": "telefono" },
			{ "data": "tipo_doc" },
			{ "data": "num_doc" },
			{ "data": "web_id" },
			{ "data": "cliente" },
			{ "data": "monto" },
			{ "data": "bono_monto" },
			{ "data": "promotor" }
		]
	});
}

function SecRptRW_buscar_dt_data(){
	var SecRepRW_tipo_busqueda = $("#SecRepRW_tipo_busqueda").val();
	var SecRepRW_fecha_inicio = $.trim($("#SecRepRW_fecha_inicio").val());
	var SecRepRW_fecha_fin = $.trim($("#SecRepRW_fecha_fin").val());
	var SecRepRW_cliente_tipo = $("#SecRepRW_cliente_tipo").val();
	var SecRepRW_cliente_texto = $("#SecRepRW_cliente_texto").val();
	
	if(parseInt(SecRepRW_cliente_tipo) != 0){
		if(SecRepRW_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRepRW_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}
	var data = {
		"accion": "SecRptRW_listar_registros",
		"tipo_busqueda": SecRepRW_tipo_busqueda,
		"fecha_inicio": SecRepRW_fecha_inicio,
		"fecha_fin": SecRepRW_fecha_fin,
		"cliente_tipo": SecRepRW_cliente_tipo,
		"cliente_texto": SecRepRW_cliente_texto
	}
	return data;
}

function SecRepRW_listar_totales() {
	$('#SecRepRW_recargas_cant').val('0');
	$('#SecRepRW_recargas_total').val('0.00');
	$('#SecRepRW_bapuestasdeportivas_cant').val('0');
	$('#SecRepRW_bcasino_cant').val('0');
	$('#SecRepRW_bapuestasdeportivas_total').val('0.00');
	$('#SecRepRW_bcasino_total').val('0.00');

	var SecRepRW_tipo_busqueda = $("#SecRepRW_tipo_busqueda").val();
	var SecRepRW_fecha_inicio = $.trim($("#SecRepRW_fecha_inicio").val());
	var SecRepRW_fecha_fin = $.trim($("#SecRepRW_fecha_fin").val());
	var SecRepRW_cliente_tipo = $("#SecRepRW_cliente_tipo").val();
	var SecRepRW_cliente_texto = $("#SecRepRW_cliente_texto").val();

	if (SecRepRW_fecha_inicio.length !== 10) {
		$("#SecRepTel_fecha_inicio").focus();
		return false;
	}
	if (SecRepRW_fecha_fin.length !== 10) {
		$("#buscador_texto").focus();
		return false;
	}

	var data = {
		"accion": "SecRptRW_listar_resumen",
		"tipo_busqueda": SecRepRW_tipo_busqueda,
		"fecha_inicio": SecRepRW_fecha_inicio,
		"fecha_fin": SecRepRW_fecha_fin,
		"cliente_tipo": SecRepRW_cliente_tipo,
		"cliente_texto": SecRepRW_cliente_texto
	}

	$.ajax({
		url: "/sys/get_reportes_recargas_web.php",
		type: 'POST',
		async : true,
		data: data,
		beforeSend: function() {
				loading("true");
		},
		complete: function() {
				loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if (parseInt(respuesta.http_code) === 200) {
				var recargas_cant = 0;
				var total_recargas = 0;
				var apuestas_dep_cat = 0;
				var casino_cant = 0;
				var apuestas_dep_total = 0;
				var casino_total = 0;
						  
				if (respuesta.resumen.length > 0) {
					$.each(respuesta.resumen, function(index, item) {
						$('#SecRepRW_recargas_cant').val(item.recargas_cant);
						$('#SecRepRW_bapuestasdeportivas_cant').val(item.bono_apuesta_deportiva_cant);
						$('#SecRepRW_bcasino_cant').val(item.bono_casino_cant);

						item.total_recarga = item.total_recarga.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bono_apuesta_deportiva = item.total_bono_apuesta_deportiva.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_bono_casino = item.total_bono_casino.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepRW_recargas_total').val(item.total_recarga);
						$('#SecRepRW_bapuestasdeportivas_total').val(item.total_bono_apuesta_deportiva);
						$('#SecRepRW_bcasino_total').val(item.total_bono_casino);
					});
				}
				return false;
			}
		},
		error: function() {}
	});
}