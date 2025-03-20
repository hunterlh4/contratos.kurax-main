function sec_reportes_sorteo_nuevos_registros() {
	if (sec_id == 'reportes' && sub_sec_id=='sorteo_nuevos_registros') {
		$('#SecRNR_fecha_inicio').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRNR_fecha_inicio').val($('#SecRNR_g_fecha_actual').val());

		$('#SecRNR_fecha_fin').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRNR_fecha_fin').val($('#SecRNR_g_fecha_actual').val());
        $('#SecRNR_zona').select2();

		$('#SecRNR_btn_exportar').click(function(){
			SecRNR_exportar_xls();
		});

		$('#SecRNR_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRNR_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRNR_fecha_inicio").val($("#SecRNR_g_fecha_actual").val());
			}
		});

		$('#SecRNR_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRNR_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRNR_fecha_fin").val($("#SecRNR_g_fecha_actual").val());
			}
		});
	}
}

$('#SecRNR_btn_buscar').click(function(){
	SecRNR_listar_registros();
});

// LISTAS TRANSACCIONES
function SecRNR_listar_registros() {
	var ftable = $('#SecRNR_result').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRNR_result').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"searching": false,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_sorteo_nuevos_registros.php",
			"data": SecRNR_buscar_dt_data()
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
            { "data": "fecha_registro" },
            { "data": "cliente" },
			{ "data": "tipo_doc" },
			{ "data": "num_doc" },
			{ "data": "premio_pagado" },
			{ "data": "monto_premio_pagado" },
			{ "data": "fecha_premio_pagado" },
			{ "data": "cc_tienda_pago" },
			{ "data": "local_pago" },
			{ "data": "zona_pago" }
		]
	});
}

function SecRNR_exportar_xls(){
	var SecRNR_fecha_inicio = $.trim($("#SecRNR_fecha_inicio").val());
	var SecRNR_fecha_fin = $.trim($("#SecRNR_fecha_fin").val());
	var SecRNR_cliente_tipo = $("#SecRNR_cliente_tipo").val();
	var SecRNR_cliente_texto = $("#SecRNR_cliente_texto").val();
	var SecRNR_estado = $("#SecRNR_estado").val();
    var SecRNR_tienda_pago = $("#SecRNR_tienda_pago").val();
    var SecRNR_premio = $('#SecRNR_premio').val();
	
	if(parseInt(SecRNR_cliente_tipo) != 0){
		if(SecRNR_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRNR_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}

	var data = {
		"accion": "SecRNR_exportar_xls",
		"fecha_inicio": SecRNR_fecha_inicio,
		"fecha_fin": SecRNR_fecha_fin,
		"cliente_tipo": SecRNR_cliente_tipo,
		"cliente_texto": SecRNR_cliente_texto,
		"estado": SecRNR_estado,
		"tienda_pago": SecRNR_tienda_pago,
		"premio": SecRNR_premio
	}

	$.ajax({
        url: "/sys/get_reportes_sorteo_nuevos_registros.php",
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

function SecRNR_buscar_dt_data(){
	var SecRNR_fecha_inicio = $.trim($("#SecRNR_fecha_inicio").val());
	var SecRNR_fecha_fin = $.trim($("#SecRNR_fecha_fin").val());
	var SecRNR_cliente_tipo = $("#SecRNR_cliente_tipo").val();
	var SecRNR_cliente_texto = $("#SecRNR_cliente_texto").val();
	var SecRNR_estado = $("#SecRNR_estado").val();
    var SecRNR_tienda_pago = $("#SecRNR_tienda_pago").val();
    var SecRNR_premio = $('#SecRNR_premio').val();
	
	if(parseInt(SecRNR_cliente_tipo) != 0){
		if(SecRNR_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRNR_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}

	var data = {
		"accion": "SecRNR_listar_registros",
		"fecha_inicio": SecRNR_fecha_inicio,
		"fecha_fin": SecRNR_fecha_fin,
		"cliente_tipo": SecRNR_cliente_tipo,
		"cliente_texto": SecRNR_cliente_texto,
		"estado": SecRNR_estado,
		"tienda_pago": SecRNR_tienda_pago,
		"premio": SecRNR_premio
	}
	return data;
}