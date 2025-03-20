function sec_reportes_clientes_online_jv(){
	if (sec_id == 'reportes' && sub_sec_id=='clientes_online_jv') {
		$('#RptCliOn_estado').select2();
		$('#RptCliOn_estado').val('1').trigger('change');
		SecRptCliOn_obtener_data_cant();
	}
}

$('#RptCliOn_btn_buscar').click(function(){
	SecRptCliOn_obtener_data_cant();
	$('#RptCliOn_resultado').show();
	var ftable = $('#RptCliOn_tabla_resultado').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#RptCliOn_tabla_resultado').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_clientes_online_jv.php",
			"data": SecRptCliOn_obtener_data()
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
            { 
            	"data": "estado",
            	createdCell: function (td, cellData, rowData, row, col) {
					var color = (rowData.estado == 'ONLINE' ) ? '#38b578' : '';
					$(td).css('color', color + ' solid 3px');
					$(td).css('font-weight', 'bold');
				}
            },
            { "data": "cliente" },
			{ "data": "num_doc" },
			{ "data": "ultimo_registro" }
		]
	});
});

function SecRptCliOn_obtener_data(){
	var estado = $('#RptCliOn_estado').val();

	var data = {
		"accion": "SecRepCliOn_listar_clientes_online_jv",
		"estado": estado
	}
	return data;
}

$('#RptCliOn_btn_exportar').click(function(){
	var estado = $('#RptCliOn_estado').val();
	var data = {
		"accion": "SecRepCliOn_export_clientes_online_jv_xls",
		"estado": estado
	}

	$.ajax({
		url: "/sys/get_reportes_clientes_online_jv.php",
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

function SecRptCliOn_obtener_data_cant(){
	var data = {
		"accion": "SecRepCliOn_cantidades"
	}
	$.ajax({
		url: "/sys/get_reportes_clientes_online_jv.php",
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
				$('#RptCliOn_cant_online').val(respuesta.cant_online);
				$('#RptCliOn_cant_jugando').val(respuesta.cant_jugando);
				return false;
			}
		},
		error: function() {}
	});
}
