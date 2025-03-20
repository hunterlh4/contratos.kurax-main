var SecRepTel_resultado = 2;
function sec_reportes_televentas_billetera_juegos_virtuales() {
	if (sec_id == 'reportes' && sub_sec_id=='billetera_juegos_virtuales') {
		$('#SecRepBilJV_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepBilJV_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepBilJV_fecha_inicio').val($('#SecRepBilJV_fecha_actual').val());
		$('#SecRepBilJV_fecha_fin').val($('#SecRepBilJV_fecha_actual').val());

		$('#SecRepBilJV_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepBilJV_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepBilJV_fecha_inicio").val($("#SecRepBilJV_fecha_actual").val());
			}
		});
		$('#SecRepBilJV_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepBilJV_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepBilJV_fecha_fin").val($("#SecRepBilJV_fecha_actual").val());
			}
		});

		$('#SecRepBilJV_estado').val('0').trigger('change').select2();
	}
}

$('#SecRepBilJV_btn_buscar').click(function(){
	$('#SecRepBilJV_result').show();
	var ftable = $('#SecRepBilJV_tabla_resultados').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepBilJV_tabla_resultados').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_billetera_juegos_virtuales.php",
			"data": SecRepBilJV_get_data()
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
			{ "data": "spin2win" },
			{ "data": "spin2winroyale" },
			{ "data": "keno" },
			{ "data": "keno_deluxe" },
			{ "data": "dog" },
			{ "data": "horse" }
            /*{ "data": "speedway" },
            { "data": "liga_inglaterra" },
			{ "data": "liga_espana" },
			{ "data": "liga_italia" },
			{ "data": "torneo" }*/
		]
	});
	SecRepBilJV_listar_totales();
});

function SecRepBilJV_get_data(){
	var SecRepBilJV_fecha_inicio = $.trim($("#SecRepBilJV_fecha_inicio").val());
	var SecRepBilJV_fecha_fin = $.trim($("#SecRepBilJV_fecha_fin").val());
	var SecRepBilJV_cliente_tipo = $("#SecRepBilJV_cliente_tipo").val();
	var SecRepBilJV_cliente_texto = $("#SecRepBilJV_cliente_texto").val();
	var SecRepBilJV_estado = $("#SecRepBilJV_estado").val();

	if (SecRepBilJV_fecha_inicio.length !== 10) {
		$("#SecRepBilJV_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJV_fecha_fin.length !== 10) {
		$("#SecRepBilJV_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_resultados",
		"fecha_inicio": SecRepBilJV_fecha_inicio,
		"fecha_fin": SecRepBilJV_fecha_fin,
		"cliente_tipo": SecRepBilJV_cliente_tipo,
		"cliente_texto": SecRepBilJV_cliente_texto,
		"estado" : SecRepBilJV_estado
	}
	return data;
}

$('#SecRepBilJV_btn_exportar').click(function(){
	var SecRepBilJV_fecha_inicio = $.trim($("#SecRepBilJV_fecha_inicio").val());
	var SecRepBilJV_fecha_fin = $.trim($("#SecRepBilJV_fecha_fin").val());
	var SecRepBilJV_cliente_tipo = $("#SecRepBilJV_cliente_tipo").val();
	var SecRepBilJV_cliente_texto = $("#SecRepBilJV_cliente_texto").val();
	var SecRepBilJV_estado = $("#SecRepBilJV_estado").val();

	if (SecRepBilJV_fecha_inicio.length !== 10) {
		$("#SecRepBilJV_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJV_fecha_fin.length !== 10) {
		$("#SecRepBilJV_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "exportar_resultados_xls",
		"fecha_inicio": SecRepBilJV_fecha_inicio,
		"fecha_fin": SecRepBilJV_fecha_fin,
		"cliente_tipo": SecRepBilJV_cliente_tipo,
		"cliente_texto": SecRepBilJV_cliente_texto,
		"estado": SecRepBilJV_estado
	}

	$.ajax({
		url: "/sys/get_reportes_billetera_juegos_virtuales.php",
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

function SecRepBilJV_listar_totales(){
	var SecRepBilJV_fecha_inicio = $.trim($("#SecRepBilJV_fecha_inicio").val());
	var SecRepBilJV_fecha_fin = $.trim($("#SecRepBilJV_fecha_fin").val());
	var SecRepBilJV_cliente_tipo = $("#SecRepBilJV_cliente_tipo").val();
	var SecRepBilJV_cliente_texto = $("#SecRepBilJV_cliente_texto").val();
	var SecRepBilJV_estado = $("#SecRepBilJV_estado").val();

	if (SecRepBilJV_fecha_inicio.length !== 10) {
		$("#SecRepBilJV_fecha_inicio").focus();
		return false;
	}
	if (SecRepBilJV_fecha_fin.length !== 10) {
		$("#SecRepBilJV_fecha_fin").focus();
		return false;
	}

	var data = {
		"accion": "listar_resultados_totales",
		"fecha_inicio": SecRepBilJV_fecha_inicio,
		"fecha_fin": SecRepBilJV_fecha_fin,
		"cliente_tipo": SecRepBilJV_cliente_tipo,
		"cliente_texto": SecRepBilJV_cliente_texto,
		"estado": SecRepBilJV_estado
	}

	$('#SecRepBilJV_cant_spin2win').val('0');
	$('#SecRepBilJV_cant_spin2win_royale').val('0');
	$('#SecRepBilJV_cant_keno').val('0');
	$('#SecRepBilJV_cant_keno_deluxe').val('0');
	$('#SecRepBilJV_cant_dog').val('0');
	$('#SecRepBilJV_cant_horse').val('0');
	/*$('#SecRepBilJV_cant_speedway').val('0');
	$('#SecRepBilJV_cant_liga_ingl').val('0');
	$('#SecRepBilJV_cant_liga_espain').val('0');
	$('#SecRepBilJV_cant_liga_itali').val('0');
	$('#SecRepBilJV_cant_torneo').val('0');*/

	$.ajax({
		url: "/sys/get_reportes_billetera_juegos_virtuales.php",
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
				var cant_spin2win = 0;
				var cant_spin2win_royale = 0;
				var cant_keno = 0;
				var cant_keno_deluxe = 0;
				var cant_dog = 0;
				var cant_horse = 0;
				/*var cant_speedway = 0;
				var cant_liga_ingl = 0;
				var cant_liga_espain = 0;
				var cant_liga_itali = 0;
				var cant_torneo = 0;*/
				$.each(respuesta.result, function(index, item){
					cant_spin2win = item.cant_spin2win;
					cant_spin2win_royale = item.cant_spin2win_royale;
					cant_keno = item.cant_keno;
					cant_keno_deluxe = item.cant_keno_deluxe;
					cant_dog = item.cant_dog;
					cant_horse = item.cant_horse;
					/*cant_speedway = item.cant_speedway;
					cant_liga_ingl = item.cant_liga_ingl;
					cant_liga_espain = item.cant_liga_espain;
					cant_liga_itali = item.cant_liga_itali;
					cant_torneo = item.cant_torneo;*/
				});

				$('#SecRepBilJV_cant_spin2win').val(cant_spin2win);
				$('#SecRepBilJV_cant_spin2win_royale').val(cant_spin2win_royale);
				$('#SecRepBilJV_cant_keno').val(cant_keno);
				$('#SecRepBilJV_cant_keno_deluxe').val(cant_keno_deluxe);
				$('#SecRepBilJV_cant_dog').val(cant_dog);
				$('#SecRepBilJV_cant_horse').val(cant_horse);
				/*$('#SecRepBilJV_cant_speedway').val(cant_speedway);
				$('#SecRepBilJV_cant_liga_ingl').val(cant_liga_ingl);
				$('#SecRepBilJV_cant_liga_espain').val(cant_liga_espain);
				$('#SecRepBilJV_cant_liga_itali').val(cant_liga_itali);
				$('#SecRepBilJV_cant_torneo').val(cant_torneo);*/
			}
		},
		error: function() {}
	});
}