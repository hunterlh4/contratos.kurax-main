function sec_reportes_balance() {
	if (sec_id == 'reportes' && sub_sec_id=='balance') {
		$('#SecRepBal_fecha').datetimepicker({ format: 'YYYY-MM-DD' });
		$('#SecRepBal_fecha').val($('#SecRepBal_g_fecha_actual').val());

		$('#SecRepBal_hora').datetimepicker({ format: 'HH:mm:ss' });
		$('#SecRepBal_hora').val($('#SecRepBal_g_hora_actual').val());

		$('#SecRepBal_btn_buscar').click(function(){
			SecRptBal_buscar_dt();
			SecRptBal_resumen();
		});
		$('#SecRepBal_btn_exportar').click(function(){
			SecRptBal_exportar_xls();
		});

		$('#SecRepBal_fecha').change(function () {
			var var_fecha_change = $('#SecRepBal_fecha').val();
			if (!(parseInt(var_fecha_change.length) > 0)) {
				$("#SecRepBal_fecha").val($("#SecRepBal_g_fecha_actual").val());
			}
		});

		$('#SecRepBal_hora').change(function () {
			var var_hora_change = $('#SecRepBal_hora').val();
			if (!(parseInt(var_hora_change.length) > 0)) {
				$("#SecRepBal_hora").val($("#SecRepBal_g_hora_actual").val());
			}
		});

		$('#SecRepBal_cant_clientes').val('0');
		$('#SecRepBal_total_balance').val('0.00');
		$('#SecRepBal_total_retiro').val('0.00');
	}
}

function SecRptBal_exportar_xls(){
	var SecRepBal_fecha = $.trim($("#SecRepBal_fecha").val());
	var SecRepBal_hora = $.trim($("#SecRepBal_hora").val());
	var SecRepBal_cliente_tipo = $("#SecRepBal_cliente_tipo").val();
	var SecRepBal_cliente_texto = $("#SecRepBal_cliente_texto").val();
	
	var data = {
		"accion": "SecRptBal_exportar_xls",
		"fecha": SecRepBal_fecha,
		"hora": SecRepBal_hora,
		"cliente_tipo": SecRepBal_cliente_tipo,
		"cliente_texto": SecRepBal_cliente_texto
	}

	$.ajax({
        url: "/sys/get_reportes_balance.php",
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
function SecRptBal_buscar_dt() {
	var ftable = $('#SecRepBal_tabla_registros').DataTable();
	ftable.clear();
	ftable.destroy();
	var ftable = $('#SecRepBal_tabla_registros').DataTable({
		'destroy': true,
		'scrollX': true,
		"processing": true,
		"serverSide": true,
		"order" : [],
		"ajax": {
			type: "POST",
			async : true,
			"url": "/sys/get_reportes_balance.php",
			"data": SecRptBal_buscar_dt_data()
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
		"columnDefs": [
			{
			  "targets": [-2, -1], // Índices de las dos últimas columnas
			  "createdCell": function (td, cellData, rowData, row, col) {
				// Aplicar estilos específicos a las dos últimas columnas
				$(td).css('text-align', 'right');
			  }
			}
		  ],
		"columns": [
			{
				"data": null,
				"sortable": false,
				render: function (data, type, row, meta) {
					data.balance = data.balance.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					data.balance_retirable = data.balance_retirable.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{ "data": "num_doc" },
			{ "data": "cliente" },
			{ "data": "balance" },
			{ "data": "balance_retirable" }
		]
	});
}

function SecRptBal_buscar_dt_data(){
	var SecRepBal_fecha = $.trim($("#SecRepBal_fecha").val());
	var SecRepBal_hora = $.trim($("#SecRepBal_hora").val());
	var SecRepBal_cliente_tipo = $("#SecRepBal_cliente_tipo").val();
	var SecRepBal_cliente_texto = $("#SecRepBal_cliente_texto").val();

	if(parseInt(SecRepBal_cliente_tipo) != 0){
		if(SecRepBal_cliente_texto.length == 0){
			swal('Aviso', 'Debe ingresar un cliente a buscar', 'warning');
			return false;
		}
		if(SecRepBal_cliente_texto.length < 3){
			swal('Aviso', 'El dato debe contener al menos 3 carácteres', 'warning');
			return false;
		}
	}
	var data = {
		"accion": "SecRptBal_listar_registros",
		"fecha": SecRepBal_fecha,
		"hora": SecRepBal_hora,
		"cliente_tipo": SecRepBal_cliente_tipo,
		"cliente_texto": SecRepBal_cliente_texto
	}
	return data;
}

function SecRptBal_resumen(){
	var SecRepBal_fecha = $.trim($("#SecRepBal_fecha").val());
	var SecRepBal_hora = $.trim($("#SecRepBal_hora").val());
	var SecRepBal_cliente_tipo = $("#SecRepBal_cliente_tipo").val();
	var SecRepBal_cliente_texto = $("#SecRepBal_cliente_texto").val();

	var data = {
		"accion": "SecRptBal_listar_resumen",
		"fecha": SecRepBal_fecha,
		"hora": SecRepBal_hora,
		"cliente_tipo": SecRepBal_cliente_tipo,
		"cliente_texto": SecRepBal_cliente_texto
	}

	$.ajax({
		url: "/sys/get_reportes_balance.php",
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
				if (respuesta.resumen.length > 0) {
					$.each(respuesta.resumen, function(index, item) {
						$('#SecRepBal_cant_clientes').val(item.cant_clientes);

						item.total_balance = item.total_balance.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
						item.total_retiro = item.total_retiro.replace(/\D/g, "")
												.replace(/([0-9])([0-9]{2})$/, '$1.$2')
												.replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

						$('#SecRepBal_total_balance').val(item.total_balance);
						$('#SecRepBal_total_retiro').val(item.total_retiro);
					});
				}
				return false;
			}
		},
		error: function() {}
	});
}