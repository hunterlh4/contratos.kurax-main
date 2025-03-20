function sec_reportes_televentas_billetera_det_juegos_virtuales() {
	if (sec_id == 'reportes' && sub_sec_id=='billetera_det_juegos_virtuales') {
		$('#SecRepBilJVdet_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepBilJVdet_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepBilJVdet_fecha_inicio').val($('#SecRepBilJVdet_fecha_actual').val());
		$('#SecRepBilJVdet_fecha_fin').val($('#SecRepBilJVdet_fecha_actual').val());

		$('#SecRepBilJVdet_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepBilJVdet_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepBilJVdet_fecha_inicio").val($("#SecRepBilJVdet_fecha_actual").val());
			}
		});
		$('#SecRepBilJVdet_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepBilJVdet_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepBilJVdet_fecha_fin").val($("#SecRepBilJVdet_fecha_actual").val());
			}
		});

		$('#SecRepBilJVdet_estado').val('').trigger('change').select2();
		$('#SecRepBilJVdet_tipo_saldo').val(1).trigger('change').select2();
	}
}

$('#SecRepBilJVdet_btn_buscar').click(function(){
	$('#SecRepBilJVdet_result').show();
	var ftable = $('#SecRepBilJVdet_tabla_resultados').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepBilJVdet_tabla_resultados').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_billetera_det_juegos_virtuales.php",
			"data": SecRepBilJVdet_get_data()
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
			{ "data": "cliente" },
			{ "data": "num_doc" },
			{ "data": "nomb_juego" },
			{ "data": "fecha_juego" },
			{ "data": "ticket" },
			{ "data": "estado" },
			{ "data": "tipo" },
			{ "data": "monto" },
			{ "data": "tipo_saldo" }
            /*{ "data": "speedway" },
            { "data": "liga_inglaterra" },
			{ "data": "liga_espana" },
			{ "data": "liga_italia" },
			{ "data": "torneo" }*/
		]
	});
	SecRepBilJVdet_listar_totales();
});

function SecRepBilJVdet_get_data(){
	var SecRepBilJVdet_fecha_inicio = $.trim($("#SecRepBilJVdet_fecha_inicio").val());
	var SecRepBilJVdet_fecha_fin = $.trim($("#SecRepBilJVdet_fecha_fin").val());
	var SecRepBilJVdet_cliente_tipo = $("#SecRepBilJVdet_cliente_tipo").val();
	var SecRepBilJVdet_cliente_texto = $("#SecRepBilJVdet_cliente_texto").val();
	var SecRepBilJVdet_estado = $("#SecRepBilJVdet_estado").val();
	var SecRepBilJVdet_tipo_saldo = $("#SecRepBilJVdet_tipo_saldo").val();

	if (SecRepBilJVdet_fecha_inicio.length !== 10) {
		$("#SecRepBilJVdet_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJVdet_fecha_fin.length !== 10) {
		$("#SecRepBilJVdet_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_det_resultados",
		"fecha_inicio": SecRepBilJVdet_fecha_inicio,
		"fecha_fin": SecRepBilJVdet_fecha_fin,
		"cliente_tipo": SecRepBilJVdet_cliente_tipo,
		"cliente_texto": SecRepBilJVdet_cliente_texto,
		"estado" : SecRepBilJVdet_estado,
		"tipo_saldo": SecRepBilJVdet_tipo_saldo
	}
	return data;
}

$('#SecRepBilJVdet_btn_exportar').click(function(){
	var SecRepBilJVdet_fecha_inicio = $.trim($("#SecRepBilJVdet_fecha_inicio").val());
	var SecRepBilJVdet_fecha_fin = $.trim($("#SecRepBilJVdet_fecha_fin").val());
	var SecRepBilJVdet_cliente_tipo = $("#SecRepBilJVdet_cliente_tipo").val();
	var SecRepBilJVdet_cliente_texto = $("#SecRepBilJVdet_cliente_texto").val();
	var SecRepBilJVdet_estado = $("#SecRepBilJVdet_estado").val();
	var SecRepBilJVdet_tipo_saldo = $("#SecRepBilJVdet_tipo_saldo").val();

	if (SecRepBilJVdet_fecha_inicio.length !== 10) {
		$("#SecRepBilJVdet_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJVdet_fecha_fin.length !== 10) {
		$("#SecRepBilJVdet_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "exportar_det_resultados_xls",
		"fecha_inicio": SecRepBilJVdet_fecha_inicio,
		"fecha_fin": SecRepBilJVdet_fecha_fin,
		"cliente_tipo": SecRepBilJVdet_cliente_tipo,
		"cliente_texto": SecRepBilJVdet_cliente_texto,
		"estado": SecRepBilJVdet_estado,
		"tipo_saldo": SecRepBilJVdet_tipo_saldo
	}

	$.ajax({
		url: "/sys/get_reportes_billetera_det_juegos_virtuales.php",
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
});

function SecRepBilJVdet_listar_totales(){
	var SecRepBilJVdet_fecha_inicio = $.trim($("#SecRepBilJVdet_fecha_inicio").val());
	var SecRepBilJVdet_fecha_fin = $.trim($("#SecRepBilJVdet_fecha_fin").val());
	var SecRepBilJVdet_cliente_tipo = $("#SecRepBilJVdet_cliente_tipo").val();
	var SecRepBilJVdet_cliente_texto = $("#SecRepBilJVdet_cliente_texto").val();
	var SecRepBilJVdet_estado = $("#SecRepBilJVdet_estado").val();
	var SecRepBilJVdet_tipo_saldo = $("#SecRepBilJVdet_tipo_saldo").val();

	if (SecRepBilJVdet_fecha_inicio.length !== 10) {
		$("#SecRepBilJVdet_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJVdet_fecha_fin.length !== 10) {
		$("#SecRepBilJVdet_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_det_resultados_totales",
		"fecha_inicio": SecRepBilJVdet_fecha_inicio,
		"fecha_fin": SecRepBilJVdet_fecha_fin,
		"cliente_tipo": SecRepBilJVdet_cliente_tipo,
		"cliente_texto": SecRepBilJVdet_cliente_texto,
		"estado": SecRepBilJVdet_estado,
		"tipo_saldo": SecRepBilJVdet_tipo_saldo
	}

	$('#SecRepBilJVdet_monto_total').val('0');
	/*$('#SecRepBilJVdet_cant_spin2win_royale').val('0');
	$('#SecRepBilJVdet_cant_keno').val('0');
	$('#SecRepBilJVdet_cant_keno_deluxe').val('0');
	$('#SecRepBilJVdet_cant_dog').val('0');
	$('#SecRepBilJVdet_cant_horse').val('0');
	/*$('#SecRepBilJVdet_cant_speedway').val('0');
	$('#SecRepBilJVdet_cant_liga_ingl').val('0');
	$('#SecRepBilJVdet_cant_liga_espain').val('0');
	$('#SecRepBilJVdet_cant_liga_itali').val('0');
	$('#SecRepBilJVdet_cant_torneo').val('0');*/

	$.ajax({
		url: "/sys/get_reportes_billetera_det_juegos_virtuales.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			if(respuesta.http_code == 200){
				var monto_total = 0;
				/*var cant_spin2win_royale = 0;
				var cant_keno = 0;
				var cant_keno_deluxe = 0;
				var cant_dog = 0;
				var cant_horse = 0;
				var cant_speedway = 0;
				var cant_liga_ingl = 0;
				var cant_liga_espain = 0;
				var cant_liga_itali = 0;
				var cant_torneo = 0;*/
				$.each(respuesta.result, function(index, item){
					monto_total = item.monto_total;
					/*cant_spin2win_royale = item.cant_spin2win_royale;
					cant_keno = item.cant_keno;
					cant_keno_deluxe = item.cant_keno_deluxe;
					cant_dog = item.cant_dog;
					cant_horse = item.cant_horse;
					cant_speedway = item.cant_speedway;
					cant_liga_ingl = item.cant_liga_ingl;
					cant_liga_espain = item.cant_liga_espain;
					cant_liga_itali = item.cant_liga_itali;
					cant_torneo = item.cant_torneo;*/
				});

				$('#SecRepBilJVdet_monto_total').val(monto_total);
				/*$('#SecRepBilJVdet_cant_spin2win_royale').val(cant_spin2win_royale);
				$('#SecRepBilJVdet_cant_keno').val(cant_keno);
				$('#SecRepBilJVdet_cant_keno_deluxe').val(cant_keno_deluxe);
				$('#SecRepBilJVdet_cant_dog').val(cant_dog);
				$('#SecRepBilJVdet_cant_horse').val(cant_horse);
				$('#SecRepBilJVdet_cant_speedway').val(cant_speedway);
				$('#SecRepBilJVdet_cant_liga_ingl').val(cant_liga_ingl);
				$('#SecRepBilJVdet_cant_liga_espain').val(cant_liga_espain);
				$('#SecRepBilJVdet_cant_liga_itali').val(cant_liga_itali);
				$('#SecRepBilJVdet_cant_torneo').val(cant_torneo);*/
			}
		},
		error: function() {}
	});
}