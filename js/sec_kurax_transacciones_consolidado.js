function sec_kurax_transacciones_consolidado(){

	sec_kurax_tc_validar_fechas();
	

	sec_kurax_kr_obtener_opciones("list_locals", $("[name='sec_kurax_tc_local_id']"));
	sec_kurax_kr_obtener_opciones("list_channels", $("[name='sec_kurax_tc_canal_venta_id']"));

	sec_kurax_kr_obtener_opciones("list_locals", $("[name='sec_modal_tc_reprocesar_local_id']"));
	sec_kurax_kr_obtener_opciones("list_channels", $("[name='sec_modal_tc_reprocesar_canal_venta_id']"));

	setTimeout(() => {
		sec_kurax_tc_reporte();
	}, 1000);
	

	$("#frm_report_kx_transacciones_consolidado").submit(function (e) {
		e.preventDefault();
		sec_kurax_tc_reporte();
	})

	$('#button_modal_reprocesar_transacciones_consolidado').click(function () {
		$('#modal_kx_reprocesar_transacciones_consolidado').modal('show');
	})

	$('#sec_modal_tc_btn_reprocesar').click(function () {
		sec_kurax_tc_reprocesar_transacciones_consolidado();
	})

	
}

function sec_kurax_tc_validar_fechas() {
	var fromInput = $('#sec_kurax_tc_desde');
	var toInput = $('#sec_kurax_tc_hasta');

	fromInput.datetimepicker({
		format: 'YYYY-MM-DD',
		locale: 'es',
		maxDate: toInput.val(),
		useCurrent: false
	});

	toInput.datetimepicker({
		format: 'YYYY-MM-DD',
		locale: 'es',
		minDate: fromInput.val(),
		useCurrent: false //Important! See issue #1075
	});

	fromInput.on("dp.change", function (e) {
		toInput.data("DateTimePicker").minDate(e.date);
	});

	toInput.on("dp.change", function (e) {
		fromInput.data("DateTimePicker").maxDate(e.date);
	});

}

function sec_kurax_tc_reporte() {
	var local_id = $('#sec_kurax_tc_local_id').val();
	var canal_venta_id = $('#sec_kurax_tc_canal_venta_id').val();
	var estado = $('#sec_kurax_tc_estado').val();
	var desde = $('#sec_kurax_tc_desde').val();
	var hasta = $('#sec_kurax_tc_hasta').val();

	if (local_id.length == 0) {
		return false;
	}

	if (canal_venta_id.length == 0) {
		return false;
	}
	if (estado.length == 0) {
		return false;
	}
	if (desde.length == 0) {
		alertify.error("Seleccione una fecha", 5);
		$('#sec_kurax_tc_desde').focus();
		return false;
	}
	if (hasta.length == 0) {
		alertify.error("Seleccione una fecha", 5);
		$('#sec_kurax_tc_hasta').focus();
		return false;
	}

	var data = {
		action : 'list',
		local_id : local_id,
		channel : canal_venta_id,
		state: estado,
		from_date : desde,
		to_date : hasta,
	};

	$.ajax({
		url: "/app/routes/KxConsolidado/index.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == "success") {
				sec_kurax_kr_render_table_transacciones_consolidado(respuesta.data);
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}



function sec_kurax_kr_render_table_transacciones_consolidado(data = []) {
	var table = $('#table_kr_transacciones_consolidado').DataTable();
        table.clear();
        table.destroy();
    var table = $("#table_kr_transacciones_consolidado").dataTable({
		bDestroy: true,
		data: data,
		order: [[0, 'desc']],
		pageLength: 25,
		columns: [
		  { data: "id", className: "text-center" },
		  { data: "nombre", className: "text-left" }, 
		  { data: "nombre_servicio", className: "text-left" },
		  { data: "nombre_canal_de_venta", className: "text-left" },
		  { data: "tipo_transaccion", className: "text-left" },
		  { data: "fecha_consolidado", className: "text-center" },
		  { data: "tickets_apostados", className: "text-right" },
		  { data: "tickets_cancelados", className: "text-right" },
		  { data: "tickets_ganados", className: "text-right" },
		  { data: "tickets_pagados", className: "text-right" },
		  { data: "apostado", className: "text-right" },
		  { data: "cancelado", className: "text-right" },
		  { data: "ganado", className: "text-right" },
		  { data: "pagado", className: "text-right" },
		  { data: "caja_deposito_terminal", className: "text-right" },
		  { data: "vouchers_caja_deposito_terminal", className: "text-right" },
		  { data: "caja_retiro_terminal", className: "text-right" },
		  { data: "vouchers_caja_retiro_terminal", className: "text-right" },
		  { data: "deposito_terminal", className: "text-right" },
		  { data: "total_pagado_en_otra_tienda", className: "text-right" },
		  { data: "tickets_pagados_en_otra_tienda", className: "text-right" },
		  { data: "total_pagado_de_otra_tienda", className: "text-right" },
		  { data: "tickets_pagados_de_otra_tienda", className: "text-right" },
		  { data: "estado", className: "text-center" },
		  { data: "created_at", className: "text-center" },
		  { data: "usuario", className: "text-left" },
		],
		language: {
		  decimal: "",
		  emptyTable: "Tabla vacia",
		  info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
		  infoEmpty: "Mostrando 0 a 0 de 0 entradas",
		  infoFiltered: "(filtered from _MAX_ total entradas)",
		  infoPostFix: "",
		  thousands: ",",
		  lengthMenu: "Mostrar _MENU_ entradas",
		  loadingRecords: "Cargando...",
		  processing: "Procesando...",
		  search: "Filtrar:",
		  zeroRecords: "Sin resultados",
		  paginate: {
			first: "Primero",
			last: "Ultimo",
			next: "Siguiente",
			previous: "Anterior",
		  },
		  aria: {
			sortAscending: ": activate to sort column ascending",
			sortDescending: ": activate to sort column descending",
		  },
		  buttons: {
			pageLength: {
				_: "Mostrar %d Resultados",
				'-1': "Tout afficher"
			}
		  }
		  
		},
		scrollX:true,
		bRetrieve: true,
		dom: 'Bfrtip',
		buttons: [
            'pageLength',
			{
                extend: 'excelHtml5',
				title: 'Transacciones Consolidado'
            },
        ]
	  })
	  .DataTable();



	 
}

function sec_kurax_tc_reprocesar_transacciones_consolidado() {
	var local_id = $('#sec_modal_tc_reprocesar_local_id').val();
	var canal_venta_id = $('#sec_modal_tc_reprocesar_canal_venta_id').val();
	var fecha = $('#sec_modal_tc_reprocesar_fecha').val();

	if (fecha.length == 0) {
		alertify.error("Seleccione una fecha", 5);
		$('#sec_modal_tc_reprocesar_fecha').focus();
		return false;
	}

	var data = {
		action : 'reprocess',
		local_id : local_id,
		channel : canal_venta_id,
		date : fecha,
	};

	$.ajax({
		url: "/app/routes/KxConsolidado/index.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == "success") {
				alertify.success("Se ha reprocesado las transacciones consolidado correctamente.", 5);
				$('#modal_kx_reprocesar_transacciones_consolidado').modal('hide');
				sec_kurax_tc_reporte();
			}
		},
		error: function (error) {
			alertify.error("A ocurrido un error en el reproceso.", 5);
			console.log(error);
			loading();
		},
	});
}


function sec_kurax_kr_obtener_opciones(accion, select) {
	$.ajax({
		url: "/app/routes/KxConsolidado/index.php",
		type: "POST",
		data: { action: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
		//  alert(datat)
		var respuesta = JSON.parse(datos);
		$(select).find("option").remove().end();
		$(select).append('<option value="0" selected>- Todos -</option>');
		$(respuesta.data).each(function (i, e) {
			opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
			$(select).append(opcion);
		});
		},
		error: function () {},
	});
}
