function sec_reportes_listado_vales() {

	$(".select2").select2({ width: "100%", placeholder: "- Todos -", });
	$(".sec_report_list_vale_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_report_list_vale_obtener_opciones("listar_empresas_activas", $("#sec_report_list_vale_empresa"));
	//sec_report_list_vale_obtener_opciones("listar_motivos_activos", $("#sec_report_list_vale_motivo"));
	sec_report_list_vale_obtener_opciones("listar_vales_estado_activos", $("#sec_report_list_vale_estado"));

	
	setTimeout(() => {
		sec_report_list_vale_buscar_vale_descuento();
	}, 2000);

	
	$("#sec_report_list_vale_empresa").change(function () {
		sec_report_list_vale_obtener_zonas();
		sec_report_list_vale_obtener_motivos();
	});

	$("#frm-vale-descuento-btn-reset").click(function () {
		sec_report_list_vale_resetear_form_vale_descuento();
	});


	$('#frm_reporte_listado_vale_descuento').submit(function (evt) {
		evt.preventDefault();
		var empresa = $('#sec_report_list_vale_empresa').val();
		var zona = $('#sec_report_list_vale_zona').val();
		var fecha_desde_vale = $('#sec_report_list_vale_fecha_desde_vale').val();
		var fecha_hasta_vale = $('#sec_report_list_vale_fecha_hasta_vale').val();
		var motivo = $('#sec_report_list_vale_motivo').val();
		var estado = $('#sec_report_list_vale_estado').val();
		
		if (empresa == null) {
			alertify.error("Seleccione al menos una empresa", 5);
			$("#sec_report_list_vale_empresa").focus();
			$("#sec_report_list_vale_empresa").select2("open");
			return false;
		}
		/*
		if (zona == null) {
			alertify.error("Seleccione al menos una zona", 5);
			$("#sec_report_list_vale_zona").focus();
			$("#sec_report_list_vale_zona").select2("open");
			return false;
		}
		if (estado == null) {
			alertify.error("Seleccione al menos un estado", 5);
			$("#sec_report_list_vale_estado").focus();
			$("#sec_report_list_vale_estado").select2("open");
			return false;
		}*/
		if (fecha_desde_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_report_list_vale_fecha_desde_vale").focus();
			$("#sec_report_list_vale_fecha_desde_vale").select2("open");
			return false;
		}
		if (fecha_hasta_vale.length == 0) {
			alertify.error("Seleccione uan fecha", 5);
			$("#sec_report_list_vale_fecha_hasta_vale").focus();
			$("#sec_report_list_vale_fecha_hasta_vale").select2("open");
			return false;
		}

		sec_report_list_vale_buscar_vale_descuento();
	});

	
}

function sec_report_list_vale_obtener_motivos() {

	var empresa_id = $('#sec_report_list_vale_empresa').val();
	if (empresa_id == "0") {
		$("[name='sec_report_list_vale_motivo']").find("option").remove().end();
		$("[name='sec_report_list_vale_motivo']").append('<option value="0">- Seleccione -</option>');
		return false;
	}

	var data = {
		empresa_id: empresa_id,
		accion : 'listar_motivos_activos_v2'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("#sec_report_list_vale_motivo").find("option").remove().end();
				$("#sec_report_list_vale_motivo").append('<option value="0">- Todos -</option>');
				$(respuesta.result).each(function (i, e) {
					console.log(e)
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_report_list_vale_motivo").append(opcion);
				});
				
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_report_list_vale_obtener_opciones(accion, select) {
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: accion }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			if (respuesta.status == 200) {
				var result = respuesta.result;
				var values = [];
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
					values.push(e.id);
				});
				$(select).val(values).trigger('change.select2');
				$(select).trigger('change');
			}
		},
		error: function () {},
	});
}

function sec_report_list_vale_obtener_zonas() {

	var empresa_id = $('#sec_report_list_vale_empresa').val();
	if (empresa_id == null) {
		console.log("null")
		$("#sec_report_list_vale_zona").find("option").remove().end();
		return false;
	}
	var data = {
		empresa_id : empresa_id,
		accion : 'listar_zonas_por_empresa_multiple'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("#sec_report_list_vale_zona").find("option").remove().end();
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_report_list_vale_zona").append(opcion);
				});
				$("#sec_report_list_vale_zona").val(respuesta.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_report_list_vale_obtener_solicitantes_por_local() {

	var local_id = $('#sec_report_list_vale_local').val();
	if (local_id == "0") {
		$("[name='sec_report_list_vale_solicitante']").find("option").remove().end();
		$("[name='sec_report_list_vale_solicitante']").append('<option value="0">- Todos -</option>');
		alertify.error("Seleccione un local", 5);
		return false;
	}
	var data = {
		local_id : local_id,
		accion : 'listar_solicitantes_por_local'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_report_list_vale_solicitante']").find("option").remove().end();
				$("[name='sec_report_list_vale_solicitante']").append('<option value="0">- Todos -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_report_list_vale_solicitante']").append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_report_list_vale_obtener_empleados_por_local() {

	var local_id = $('#sec_report_list_vale_local').val();
	if (local_id == "0") {
		$("[name='sec_report_list_vale_empleado']").find("option").remove().end();
		$("[name='sec_report_list_vale_empleado']").append('<option value="0">- Todos -</option>');
		alertify.error("Seleccione un local", 5);
		return false;
	}
	var data = {
		local_id : local_id,
		accion : 'listar_empleados_por_local'
	};

	$.ajax({
		url: "vales/controllers/DataController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				$("[name='sec_report_list_vale_empleado']").find("option").remove().end();
				$("[name='sec_report_list_vale_empleado']").append('<option value="0">- Todos -</option>');
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("[name='sec_report_list_vale_empleado']").append(opcion);
				});
			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}

function sec_report_list_vale_buscar_vale_descuento() {

	var empresa = $('#sec_report_list_vale_empresa').val();
	var zona = $('#sec_report_list_vale_zona').val();
	var local = $('#sec_report_list_vale_local').val();
	var solicitante = $('#sec_report_list_vale_solicitante').val();
	var empleado = $('#sec_report_list_vale_empleado').val();
	var fecha_desde_vale = $('#sec_report_list_vale_fecha_desde_vale').val();
	var fecha_hasta_vale = $('#sec_report_list_vale_fecha_hasta_vale').val();
	var motivo = $('#sec_report_list_vale_motivo').val();
	var estado = $('#sec_report_list_vale_estado').val();

	var data = {
		empresa : empresa,
		zona : zona,
		estado : estado,
		fecha_desde_vale : fecha_desde_vale,
		fecha_hasta_vale : fecha_hasta_vale,
		motivo : motivo,
		accion : 'reporte_listado_vales'
	};

	$.ajax({
		url: "vales/controllers/ValeController.php",
		type: "POST",
		data: data, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
				fnc_render_table_reporte_listado_vales(respuesta.result.data);
				fnc_render_table_reporte_resumen_listado_vales(respuesta.result.resumen);
				
			}else{
				alertify.error(respuesta.message, 10);
			}
			return false;
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_report_list_vale_resetear_form_vale_descuento() {

	$('#sec_report_list_vale_empresa').val('0').trigger('change.select2');
	$('#sec_report_list_vale_zona').val('0').trigger('change.select2');
	$('#sec_report_list_vale_local').val('0').trigger('change.select2');
	$('#sec_report_list_vale_solicitante').val('0').trigger('change.select2');
	$('#sec_report_list_vale_empleado').val('0').trigger('change.select2');
	$('#sec_report_list_vale_motivo').val('0').trigger('change.select2');
	//$('#sec_report_list_vale_fecha_incidencia').val();
	$('#sec_report_list_vale_monto').val('');
	$('#sec_report_list_vale_observacion').val('');

}

function fnc_render_table_reporte_listado_vales(data = []) {
	$("#tbl_vale_listado_vales")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		width:'100%',
		order: [[0, 'desc']],
		pageLength: 25,
		lengthMenu: [ [25, 50, 100, 500, -1], [25, 50, 100, 500, "Todos"] ],
		columns: [
		  { data: "nro_vale", className: "text-center" },
		  { data: "nro_vale_totalizado", className: "text-center" },
		  { data: "created_at", className: "text-center" }, 
		  { data: "fecha_incidencia", className: "text-center" },
		  { data: "monto", className: "text-right" },
		  { data: "tipo_vale", className: "text-left" },
		  { data: "vale_estado", className: "text-center" },
		  { data: "dni_empleado", className: "text-center" },
		  { data: "nombre_empleado", className: "text-left" },
		  { data: "motivo", className: "text-left" },
		  { data: "empresa", className: "text-left" },
		  { data: "zona", className: "text-center" },
		  { data: "centro_costo", className: "text-center" },
		  { data: "nombre_centro_costo", className: "text-left" },
		  { data: "fecha_sincronizacion", className: "text-center" },
		  { data: "observacion", className: "text-left" },
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
		scrollY: true,
		scrollX: true,
		dom: 'Bfrtip',
        buttons: [
            'pageLength', 
			{
                extend: 'colvis',
                text: 'Columnas Visibles',
            },
			{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]
                },
				title: 'Reporte Listado de Vales de Descuento'
            },
        ],
		columnDefs: [
            {
                targets: [13,15],
                visible: false
            }
        ]
	  })
	  .DataTable();
  
}


function fnc_render_table_reporte_resumen_listado_vales(data = []) {

	$("#tbl_vale_resumen_listado_vales")
	  .dataTable({
		bDestroy: true,
		data: data,
		width:'100%',
		order: [[0, 'desc']],
		columns: [
		  { data: "nombre_tipo_vale", className: "text-left" },
		  { data: "estado_vale", className: "text-center" }, 
		  { data: "cantidad", className: "text-center" },
		  { data: "monto", className: "text-right" },
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
		}
	  })
	  .DataTable();
  
}



