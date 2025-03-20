function sec_reportes_vales_gdt_detallado() {

	$(".select2").select2({ width: "100%", placeholder: "- Todos -", });
	$(".sec_report_vale_gdt_detallado_datepicker").datepicker({
		dateFormat: "dd-mm-yy",
		changeMonth: true,
		changeYear: true,
	}).on("change", function (ev) {
		$(this).datepicker("hide");
		var newDate = $(this).datepicker("getDate");
		$("input[data-real-date=" + $(this).attr("id") + "]").val($.format.date(newDate, "yyyy-MM-dd"));
	});

	sec_report_vale_gdt_detallado_obtener_opciones("listar_empresas_activas", $("#sec_report_vale_gdt_detallado_empresa"));
	sec_report_vale_gdt_detallado_obtener_estado_vale();
	sec_report_vale_gdt_detallado_obtener_estado_personal();

	setTimeout(() => {
		sec_report_vale_gdt_detallado_buscar_vale_gdt();
	}, 2000);

	$("#sec_report_vale_gdt_detallado_empresa").change(function () {
		sec_report_vale_gdt_detallado_obtener_zonas();
	});

	$("#frm-vale-descuento-btn-reset").click(function () {
		sec_report_vale_gdt_detallado_resetear_form_vale_descuento();
	});


	$('#frm_reporte_gdt_detallado').submit(function (evt) {
		evt.preventDefault();
		var empresa = $('#sec_report_vale_gdt_detallado_empresa').val();
		var zona = $('#sec_report_vale_gdt_detallado_zona').val();
		var empleado = $('#sec_report_vale_gdt_detallado_empleado').val();
		var dni = $('#sec_report_vale_gdt_detallado_dni').val();
		var fecha_desde_vale = $('#sec_report_vale_gdt_detallado_fecha_desde_vale').val();
		var fecha_hasta_vale = $('#sec_report_vale_gdt_detallado_fecha_hasta_vale').val();
	
		if (empresa == null) {
			alertify.error("Seleccione una empresa", 5);
			$("#sec_report_vale_gdt_detallado_empresa").select2('open');
			return false;
		}

		if (fecha_desde_vale.length == 0) {
			alertify.error("Seleccione una fecha", 5);
			$("#sec_report_vale_gdt_detallado_fecha_desde_vale").focus();
			return false;
		}
		if (fecha_hasta_vale.length == 0) {
			alertify.error("Seleccione una fecha", 5);
			$("#sec_report_vale_gdt_detallado_fecha_hasta_vale").focus();
			return false;
		}
		
		sec_report_vale_gdt_detallado_buscar_vale_gdt();
	});

	
}

function sec_report_vale_gdt_detallado_obtener_opciones(accion, select) {
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

function sec_report_vale_gdt_detallado_obtener_estado_vale() {
	var select = $("#sec_report_vale_gdt_detallado_estado_vale");
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: 'listar_estados_gdt' }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			if (respuesta.status == 200) {
				var result = respuesta.result.data;
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});

				select.val(respuesta.result.value).trigger('change.select2');
			}
		},
		error: function () {},
	});
}

function sec_report_vale_gdt_detallado_obtener_estado_personal() {
	var select = $("#sec_report_vale_gdt_detallado_estado_personal");
	$.ajax({
		url: "/vales/controllers/DataController.php",
		type: "POST",
		data: { accion: 'listar_estado_personal' }, //+data,
		beforeSend: function () {},
		complete: function () {},
		success: function (datos) {
			var respuesta = JSON.parse(datos);
			$(select).find("option").remove().end();
			if (respuesta.status == 200) {
				var result = respuesta.result.data;
				$(result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$(select).append(opcion);
				});

				select.val(respuesta.result.value).trigger('change.select2');
			}
		},
		error: function () {},
	});
}


function sec_report_vale_gdt_detallado_obtener_zonas() {

	var empresa_id = $('#sec_report_vale_gdt_detallado_empresa').val();
	if (empresa_id == null) {
		console.log("null")
		$("#sec_report_vale_gdt_detallado_zona").find("option").remove().end();
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
				$("#sec_report_vale_gdt_detallado_zona").find("option").remove().end();
				$(respuesta.result).each(function (i, e) {
					opcion = $("<option value='" + e.id + "'>" + e.nombre + "</option>");
					$("#sec_report_vale_gdt_detallado_zona").append(opcion);
				});
				$("#sec_report_vale_gdt_detallado_zona").val(respuesta.value).trigger('change.select2');

			}
		},
		error: function (error) {
			console.log(error);
		},
	});
}


function sec_report_vale_gdt_detallado_buscar_vale_gdt() {

	var empresa = $('#sec_report_vale_gdt_detallado_empresa').val();
	var zona = $('#sec_report_vale_gdt_detallado_zona').val();
	var empleado = $('#sec_report_vale_gdt_detallado_empleado').val();
	var dni = $('#sec_report_vale_gdt_detallado_dni').val();
	var estado_vale = $('#sec_report_vale_gdt_detallado_estado_vale').val();
	var estado_personal = $('#sec_report_vale_gdt_detallado_estado_personal').val();
	var fecha_desde_vale = $('#sec_report_vale_gdt_detallado_fecha_desde_vale').val();
	var fecha_hasta_vale = $('#sec_report_vale_gdt_detallado_fecha_hasta_vale').val();
	var cuota = $('#sec_report_vale_cuota').val();
	
	var data = {
		empresa : empresa,
		zona : zona,
		empleado : empleado,
		dni : dni,
		estado_vale : estado_vale,
		estado_personal : estado_personal,
		fecha_desde_vale : fecha_desde_vale,
		fecha_hasta_vale : fecha_hasta_vale,
		cuota : cuota,
		accion : 'reporte_vales_gdt_detallado'
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
				fnc_render_table_reporte_list_gdt_detallado_vales_gdt(respuesta.result.data,respuesta.result.total.total,respuesta.result.total.total_cuota );		
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


function sec_report_vale_gdt_detallado_resetear_form_vale_descuento() {

	$('#sec_report_vale_gdt_detallado_empresa').val('0').trigger('change.select2');
	$('#sec_report_vale_gdt_detallado_zona').val('0').trigger('change.select2');
	$('#sec_report_vale_gdt_detallado_local').val('0').trigger('change.select2');
	$('#sec_report_vale_gdt_detallado_solicitante').val('0').trigger('change.select2');
	$('#sec_report_vale_gdt_detallado_empleado').val('0').trigger('change.select2');
	$('#sec_report_vale_gdt_detallado_motivo').val('0').trigger('change.select2');
	//$('#sec_report_vale_gdt_detallado_fecha_incidencia').val();
	$('#sec_report_vale_gdt_detallado_monto').val('');
	$('#sec_report_vale_gdt_detallado_observacion').val('');

}

function fnc_render_table_reporte_list_gdt_detallado_vales_gdt(data = [],total = '0.00',total_cuota = '0.00') {
	$("#tbl_reporte_vale_gdt_detalle")
	  .dataTable({
		bDestroy: true,
		data: data,
		responsive: true,
		scrollX:        true,
        scrollCollapse: true,
        paging:         true,
        fixedColumns:   {
            left: 1,
            right: 1
        },
		pageLength: 25,
		lengthMenu: [ [25, 50, 100, 500, -1], [25, 50, 100, 500, "Todos"] ],
		columns: [
		  { data: "empresa", className: "text-left" },
		  { data: "zona", className: "text-center" },
		  { data: "centro_costo", className: "text-center" },
		  { data: "nombre_centro_costo", className: "text-left" },
		  { data: "nombre_empleado", className: "text-left" },
		  { data: "dni_empleado", className: "text-center" },
		  { data: "nro_vale", className: "text-center" },
		  { data: "tipo_vale", className: "text-left" },
		  { data: "motivo", className: "text-left" },
		  { data: "fecha_incidencia", className: "text-center" }, 
		  { data: "fecha_sincronizacion", className: "text-center" }, 
		  { data: "vale_estado", className: "text-center" }, 
		  { data: "nro_cuota", className: "text-center" },
		  { data: "monto_cuota", className: "text-right" },
		  { data: "fecha_cuota", className: "text-center" },
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
                    columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
                },
				title: 'Reporte Vales de Descuento - GDT Detalle'
            },
        ],
		columnDefs: [
            {
                targets: [3],
                visible: false
            }
        ]
	  })
	  .DataTable();

	  var table = $("#tbl_reporte_vale_gdt_detalle").DataTable();
	 //$(table.column(12).footer()).html(total);
	  //$(table.column(12).footer()).addClass('text-right');

	  $(table.column(18).footer()).html(total_cuota);
	  $(table.column(18).footer()).addClass('text-right');

}





