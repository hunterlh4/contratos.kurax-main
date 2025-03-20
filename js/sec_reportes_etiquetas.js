
var SecRepEtiq_resultado = 2;
function sec_reportes_etiquetas() {
	if (sec_id == 'reportes' && sub_sec_id=='etiquetas') {

		$('#SecRepEtiq_fecha_inicio').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepEtiq_fecha_fin').datetimepicker({
			format: 'YYYY-MM-DD'
		});
		$('#SecRepEtiq_fecha_inicio').val($('#g_fecha_actual').val());
		$('#SecRepEtiq_fecha_fin').val($('#g_fecha_actual').val());

		$('#SecRepEtiq_fecha_inicio').change(function () {
			var var_fecha_change = $('#SecRepEtiq_fecha_inicio').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepEtiq_fecha_inicio").val($("#g_fecha_actual").val());
			}
		});
		$('#SecRepEtiq_fecha_fin').change(function () {
			var var_fecha_change = $('#SecRepEtiq_fecha_fin').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepEtiq_fecha_fin").val($("#g_fecha_actual").val());
			}
		});

		$('#SecRepEtiq_btn_buscar').click(function() {
			SecRepEtiq_buscar();
		});
	}
}

function SecRepEtiq_buscar(){
	SecRepTel_listar_registros();
}

// LISTAS TRANSACCIONES
function SecRepTel_listar_registros() {
	var ftable = $('#SecRepEtiq_tabla_registros').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepEtiq_tabla_registros').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_etiquetas.php",
			"data": SecRepTel_listar_registros_data()
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
			{ "data": "num_doc" },
			{ "data": "web_id" },
			{ "data": "cliente" },
			{ "data": "label" }
		]
	});
	$('#SecRepEtiq_RES').show();
}

function SecRepTel_listar_registros_data() {
	var tipo = $("#SecRepEtiq_cliente_tipo").val();
	var texto = $("#SecRepEtiq_cliente_texto").val();

	var data = {
		"accion": "listar_registros",
		"cliente_tipo": tipo,
		"cliente_texto": texto
	}
	return data;
}

$('#SecRepEtiq_btn_exportar').click(function(){
	var tipo = $("#SecRepEtiq_cliente_tipo").val();
	var texto = $("#SecRepEtiq_cliente_texto").val();

	var data = {
		"accion": "listar_export_xls",
		"cliente_tipo": tipo,
		"cliente_texto": texto
	}
	
	$.ajax({
		url: "/sys/get_reportes_etiquetas.php",
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